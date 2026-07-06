#!/usr/bin/env python3
"""Fix mismatched ITTF IDs in the database.

Reads ranking JSON files (source of truth from ITTF portal) and corrects
ITTF IDs in the database that don't match the portal's mapping.
Falls back to searching the ITTF portal for players not found in local files.

Usage:
    python fix_ittf_ids.py                        # Dry-run (show what would change)
    python fix_ittf_ids.py --apply                # Apply changes to database
    python fix_ittf_ids.py --report               # Print detailed report
    python fix_ittf_ids.py --search-portal        # Enable portal search for not-found players
"""

import argparse
import json
import os
import re
import sys
import time
from pathlib import Path

sys.path.insert(0, str(Path(__file__).resolve().parent))

from auth import IttfSession
from bs4 import BeautifulSoup
from config import IMPORT_DIR, PLAYER_PROFILE_URL, SESSION_PATH

try:
    import mysql.connector
except ImportError:
    print("ERROR: mysql-connector-python required. pip install mysql-connector-python")
    sys.exit(1)


def load_portal_mapping() -> dict[str, dict]:
    """Load ranking JSON files and build portal_name -> {ittf_id, gender} mapping."""
    portal = {}

    for filename in ["rankings_men_2026-07-06_all.json", "rankings_women_2026-07-06_all.json", "rankings_men_2026-07-05_full.json", "rankings_women_2026-07-05_top100.json"]:
        filepath = IMPORT_DIR / filename
        if not filepath.exists():
            continue

        data = json.loads(filepath.read_text())
        rows = data.get("rows", [])
        gender = data.get("gender", "unknown")

        for row in rows:
            name = row.get("name", "").strip()
            ittf_id = str(row.get("ittf_id", "")).strip()
            if name and ittf_id:
                portal[name] = {"ittf_id": ittf_id, "gender": gender}

        print(f"Loaded {len(rows)} entries from {filename}")

    return portal


def load_analysis() -> dict:
    """Load the mismatch analysis file."""
    analysis_path = Path(__file__).resolve().parent.parent.parent / "temp_ittf_id_final_analysis.json"
    if not analysis_path.exists():
        print(f"WARNING: {analysis_path} not found")
        return {}
    return json.loads(analysis_path.read_text())


def get_db_players_with_ittf_id(conn) -> list[dict]:
    """Get all players with ITTF IDs from the database."""
    cur = conn.cursor(dictionary=True)
    cur.execute("""
        SELECT id, ittf_id, first_name, last_name, country_code, date_of_birth, world_ranking
        FROM players
        WHERE ittf_id IS NOT NULL AND ittf_id != ''
        ORDER BY id
    """)
    rows = cur.fetchall()
    cur.close()
    return rows


def normalize_name(first_name: str, last_name: str) -> str:
    """Normalize player name to ITTF portal format: SURNAME Given(s).

    DB has two formats:
      Format A: first_name="Chuqin", last_name="Wang" -> "WANG Chuqin"
      Format B: first_name="Tomokazu HARIMOTO", last_name="" -> "HARIMOTO Tomokazu"
    Portal uses: "SURNAME Given"
    """
    surname = (last_name or "").strip()
    given = (first_name or "").strip()

    if surname:
        # Format A: separate first/last names
        return f"{surname.upper()} {given}".strip()

    if not given:
        return ""

    # Format B: full name in first_name
    # Two sub-formats:
    #   "SURNAME Given" (ITTF portal format) -> "HARIMOTO Tomokazu"
    #   "Given SURNAME" (WTT import format) -> "Tomokazu HARIMOTO"
    import re
    parts = given.split()
    if len(parts) < 2:
        return given

    # Check if last word is all uppercase (likely surname at end = "Given SURNAME")
    if parts[-1] == parts[-1].upper() and not re.search(r'\d', parts[-1]):
        # "Given SURNAME" format -> reverse to "SURNAME Given"
        surname = parts[-1]
        given_name = " ".join(parts[:-1])
        return f"{surname} {given_name}".strip()

    # Check if first word is all uppercase (likely surname at start = "SURNAME Given")
    if parts[0] == parts[0].upper() and not re.search(r'\d', parts[0]):
        surname = parts[0]
        given_name = " ".join(parts[1:])
        return f"{surname} {given_name}".strip()

    return given


def find_correct_ittf_id(portal: dict, db_player: dict) -> str | None:
    """Find the correct ITTF ID for a DB player by matching name against portal."""
    # Try direct name match
    portal_name = normalize_name(db_player["first_name"], db_player["last_name"])

    if portal_name in portal:
        return portal[portal_name]["ittf_id"]

    # Try case-insensitive match
    portal_name_lower = portal_name.lower()
    for pname, pdata in portal.items():
        if pname.lower() == portal_name_lower:
            return pdata["ittf_id"]

    # Build all possible name variants
    first = (db_player["first_name"] or "").strip()
    last = (db_player["last_name"] or "").strip()
    full = f"{first} {last}".strip()

    # Variant: if full name is in first_name (Format B)
    if not last and " " in first:
        # Already tried normalize_name, try more variants
        parts = first.split()
        for i in range(1, len(parts)):
            variant_a = " ".join(parts[i:] + parts[:i])  # rotate
            variant_a_lower = variant_a.lower()
            for pname, pdata in portal.items():
                if pname.lower() == variant_a_lower:
                    return pdata["ittf_id"]

    # Try surname-only match
    surname = last.upper() if last else ""
    if not surname and first:
        # Extract surname from full name in first_name
        parts = first.split()
        for p in parts:
            if p == p.upper() and len(p) > 1:
                surname = p
                break

    if surname:
        candidates = []
        for pname, pdata in portal.items():
            if pname.upper().startswith(surname + " "):
                candidates.append((pname, pdata["ittf_id"]))
        if len(candidates) == 1:
            return candidates[0][1]

    # Try GivenName surname match (WTT format in portal)
    if first and last:
        wtt_variant = f"{first} {last}"
        for pname, pdata in portal.items():
            # Check if portal name contains both first and last
            pname_parts = pname.lower().split()
            if first.lower() in [p.lower() for p in pname_parts] and last.lower() in [p.lower() for p in pname_parts]:
                return pdata["ittf_id"]

    return None


def init_session() -> IttfSession | None:
    """Load saved session or try auto-login from credentials.json."""
    session = IttfSession()
    if session.load():
        print("Session restored from disk.")
        return session

    creds_path = Path(__file__).resolve().parent / "credentials.json"
    if creds_path.exists():
        creds = json.loads(creds_path.read_text())
        username = creds.get("username", "")
        password = creds.get("password", "")
        if username and password:
            print("Auto-login from credentials.json...")
            if session.login(username, password):
                print("Auto-login successful!")
                return session

    print("No ITTF session available. Portal search disabled.")
    return None


def search_portal_by_name(session: IttfSession, surname: str, given: str) -> list[dict]:
    """Search the ITTF portal by surname and given name."""
    url = (
        f"{PLAYER_PROFILE_URL}"
        f"?resetfilters=1"
        f"&vw_profiles___surname_raw={surname}"
        f"&vw_profiles___given_name_raw={given}"
    )
    try:
        resp = session.get(url, timeout=30)
        resp.raise_for_status()
    except Exception as e:
        print(f"    Portal search error: {e}")
        return []

    soup = BeautifulSoup(resp.text, "html.parser")
    rows = soup.select("table[id^='list_'] tr.fabrik_row, table[id^='list_'] tr.fabrik_row___")
    results = []
    for row in rows:
        cells = row.find_all("td")
        entry = {}
        for cell in cells:
            cls = " ".join(cell.get("class", []))
            if "player_id_raw" in cls:
                entry["ittf_id"] = cell.get_text(strip=True)
            elif "name_raw" in cls or "surname_raw" in cls:
                link = cell.find("a")
                if link:
                    entry["name"] = link.get_text(strip=True)
                else:
                    entry["name"] = cell.get_text(strip=True)
            elif "country_raw" in cls:
                entry["country"] = cell.get_text(strip=True)
        if "ittf_id" in entry and entry["ittf_id"]:
            results.append(entry)
    return results


def search_portal_for_player(session: IttfSession, db_player: dict) -> tuple[str | None, str | None]:
    """Search ITTF portal for a player by name.

    Returns:
        (correct_ittf_id, portal_name) if found, (None, None) otherwise.
    """
    first = (db_player["first_name"] or "").strip()
    last = (db_player["last_name"] or "").strip()
    full_name = f"{first} {last}".strip()

    # Determine surname and given name for portal search
    if last:
        surname = last.upper()
        given = first
    elif " " in first:
        # Full name in first_name field
        parts = first.split()
        # Check if last word is uppercase (SURNAME) = "Given SURNAME" format
        if parts[-1] == parts[-1].upper() and not re.search(r'\d', parts[-1]):
            surname = parts[-1]
            given = " ".join(parts[:-1])
        elif parts[0] == parts[0].upper() and not re.search(r'\d', parts[0]):
            surname = parts[0]
            given = " ".join(parts[1:])
        else:
            surname = parts[-1]
            given = " ".join(parts[:-1])
    else:
        surname = first.upper()
        given = ""

    # Search by surname + given name
    results = search_portal_by_name(session, surname, given)
    if not results and given:
        # Try surname only
        results = search_portal_by_name(session, surname, "")

    if not results:
        return None, None

    # Try to match by name
    for r in results:
        portal_name = r.get("name", "")
        portal_country = r.get("country", "")
        db_country = db_player.get("country_code", "")

        # Check if country matches
        if portal_country and db_country and portal_country.upper() == db_country.upper():
            return r["ittf_id"], portal_name

    # If no country match, return the first result
    return results[0]["ittf_id"], results[0].get("name", "")


def verify_current_id(session: IttfSession, db_player: dict) -> bool:
    """Verify that the player's current ITTF ID matches their name on the portal."""
    ittf_id = str(db_player["ittf_id"])

    url = (
        f"{PLAYER_PROFILE_URL}"
        f"?resetfilters=1"
        f"&vw_profiles___player_id_raw%5Bvalue%5D%5B%5D={ittf_id}"
    )
    try:
        resp = session.get(url, timeout=30)
        resp.raise_for_status()
    except Exception:
        return False

    soup = BeautifulSoup(resp.text, "html.parser")
    rows = soup.select("table[id^='list_'] tr.fabrik_row, tr.fabrik_row___")
    if not rows:
        return False

    cells = rows[0].find_all("td")
    for cell in cells:
        cls = " ".join(cell.get("class", []))
        if "vw_profiles___name" in cls:
            portal_name = cell.get_text(strip=True)
            portal_name = re.sub(r'\s*\(#?\d+\)\s*$', '', portal_name).strip()

            # Check name match
            first = (db_player["first_name"] or "").strip()
            last = (db_player["last_name"] or "").strip()
            full_name = f"{first} {last}".strip()

            portal_lower = portal_name.lower()
            full_lower = full_name.lower()

            # Check if all parts of DB name appear in portal name or vice versa
            db_parts = set(full_lower.replace("-", " ").split())
            portal_parts = set(portal_lower.replace("-", " ").split())
            shared = db_parts & portal_parts

            # At least surname should match
            if last and last.lower() in portal_lower:
                return True
            if len(shared) >= max(1, len(db_parts) // 2):
                return True
            return False
    return False


def main():
    parser = argparse.ArgumentParser(description="Fix mismatched ITTF IDs")
    parser.add_argument("--apply", action="store_true", help="Apply changes to database")
    parser.add_argument("--report", action="store_true", help="Print detailed report")
    parser.add_argument("--search-portal", action="store_true", help="Search ITTF portal for players not found in local ranking files")
    args = parser.parse_args()

    # Load portal mapping (source of truth)
    print("Loading portal ranking data...")
    portal = load_portal_mapping()
    print(f"Portal mapping: {len(portal)} players\n")

    # Load analysis for reference
    analysis = load_analysis()
    mismatched_ids = set()
    if analysis:
        for detail in analysis.get("mismatch_details", []):
            mismatched_ids.add(detail["ittf_id"])
        print(f"Mismatched ITTF IDs from analysis: {len(mismatched_ids)}\n")

    # Connect to database
    conn = mysql.connector.connect(
        host=os.getenv("DB_HOST", "127.0.0.1"),
        port=int(os.getenv("DB_PORT", "3306")),
        user=os.getenv("DB_USERNAME", "root"),
        password=os.getenv("DB_PASSWORD", ""),
        database=os.getenv("DB_DATABASE", "tt_match"),
    )

    # Get all players with ITTF IDs
    players = get_db_players_with_ittf_id(conn)
    print(f"DB players with ITTF ID: {len(players)}\n")

    # Initialize portal session if requested
    portal_session = None
    if args.search_portal:
        portal_session = init_session()

    # Analyze each player
    changes = []
    already_correct = 0
    not_found = 0
    not_found_players = []
    resolved_via_portal = 0
    portal_search_errors = 0
    mismatches = []

    for i, player in enumerate(players):
        db_id = str(player["ittf_id"])
        correct_id = find_correct_ittf_id(portal, player)
        full_name = f"{player['first_name']} {player['last_name']}".strip()

        if correct_id is None:
            # Try portal search as fallback
            if portal_session:
                print(f"  Searching portal for: {full_name} (DB ID: {db_id})... ", end="", flush=True)
                portal_id, portal_name = search_portal_for_player(portal_session, player)
                if portal_id:
                    print(f"found: {portal_name} (ID: {portal_id})")
                    if portal_id == db_id:
                        already_correct += 1
                        resolved_via_portal += 1
                    else:
                        mismatches.append({
                            "player_id": player["id"],
                            "full_name": full_name,
                            "db_ittf_id": db_id,
                            "correct_ittf_id": portal_id,
                            "country": player["country_code"],
                            "ranking": player["world_ranking"],
                            "dob": str(player["date_of_birth"]) if player["date_of_birth"] else None,
                        })
                        changes.append({
                            "player_id": player["id"],
                            "old_ittf_id": db_id,
                            "new_ittf_id": portal_id,
                            "full_name": full_name,
                        })
                        resolved_via_portal += 1
                else:
                    # Last resort: verify current ID by fetching profile page
                    print("checking current ID... ", end="", flush=True)
                    if verify_current_id(portal_session, player):
                        print(f"verified OK (ID: {db_id})")
                        already_correct += 1
                    else:
                        print("not found on portal")
                        not_found += 1
                        not_found_players.append({"full_name": full_name, "db_ittf_id": db_id, "player_id": player["id"]})
                # Rate limit portal searches
                if i < len(players) - 1:
                    time.sleep(1.0)
            else:
                not_found += 1
                not_found_players.append({"full_name": full_name, "db_ittf_id": db_id, "player_id": player["id"]})
                if args.report:
                    print(f"  NOT FOUND: {full_name} (DB ID: {db_id})")
            continue

        if correct_id == db_id:
            already_correct += 1
            continue

        # This player has a wrong ITTF ID
        mismatches.append({
            "player_id": player["id"],
            "full_name": full_name,
            "db_ittf_id": db_id,
            "correct_ittf_id": correct_id,
            "country": player["country_code"],
            "ranking": player["world_ranking"],
            "dob": str(player["date_of_birth"]) if player["date_of_birth"] else None,
        })

        changes.append({
            "player_id": player["id"],
            "old_ittf_id": db_id,
            "new_ittf_id": correct_id,
            "full_name": full_name,
        })

    # Print summary
    print("=" * 70)
    print("ANALYSIS SUMMARY")
    print("=" * 70)
    print(f"Total players with ITTF ID:  {len(players)}")
    print(f"Already correct:             {already_correct}")
    if args.search_portal:
        print(f"  (resolved via portal:      {resolved_via_portal})")
    print(f"Need correction:             {len(changes)}")
    print(f"Name not found in portal:    {not_found}")
    print("=" * 70)

    if not_found_players and args.report:
        print("\nSTILL NOT FOUND (check manually):")
        print("-" * 70)
        for p in not_found_players:
            print(f"  {p['full_name']} (DB ID: {p['db_ittf_id']}, Player ID: {p['player_id']})")
        print("-" * 70)

    if args.report and changes:
        print("\nDETAILED CHANGES:")
        print("-" * 70)
        for c in changes:
            print(f"  {c['full_name']} (ID:{c['player_id']}):")
            print(f"    OLD: {c['old_ittf_id']}")
            print(f"    NEW: {c['new_ittf_id']}")
        print("-" * 70)

    if not changes and not_found == 0:
        print("\nNo changes needed!")
        conn.close()
        return

    if not changes:
        print(f"\nNo corrections, but {not_found} players still unresolved.")
        if not args.apply:
            print("Use --apply to still update anything, or fix remaining players manually.")
        conn.close()
        return

    # Apply changes
    if not args.apply:
        print(f"\n[DRY RUN] Would apply {len(changes)} changes. Use --apply to execute.")
        conn.close()
        return

    print(f"\nApplying {len(changes)} ITTF ID corrections...")

    cur = conn.cursor()

    # Step 1: Set all wrong IDs to temporary prefixed values to avoid unique constraint conflicts
    print("  Step 1: Setting temporary IDs...")
    for c in changes:
        cur.execute(
            "UPDATE players SET ittf_id = CONCAT('OLD_', ittf_id) WHERE id = %s",
            (c["player_id"],)
        )
    conn.commit()
    print(f"    {len(changes)} players set to temporary IDs")

    # Step 2: Set correct IDs
    print("  Step 2: Setting correct ITTF IDs...")
    for c in changes:
        cur.execute(
            "UPDATE players SET ittf_id = %s WHERE id = %s",
            (c["new_ittf_id"], c["player_id"])
        )
    conn.commit()
    print(f"    {len(changes)} players updated with correct ITTF IDs")

    # Step 3: Verify - check for any remaining OLD_ prefixed IDs
    cur.execute("SELECT COUNT(*) FROM players WHERE ittf_id LIKE 'OLD_%'")
    old_count = cur.fetchone()[0]
    if old_count > 0:
        print(f"  WARNING: {old_count} players still have OLD_ prefixed IDs!")
    else:
        print("  Verification OK: no OLD_ prefixed IDs remain")

    # Step 4: Reset birth years for corrected players (they need re-scraping)
    print("  Step 3: Resetting birth years for corrected players...")
    player_ids = [c["player_id"] for c in changes]
    placeholders = ",".join(["%s"] * len(player_ids))
    cur.execute(
        f"UPDATE players SET date_of_birth = NULL WHERE id IN ({placeholders})",
        player_ids
    )
    conn.commit()
    print(f"    {cur.rowcount} players' birth years reset to NULL")

    cur.close()
    conn.close()

    print(f"\nDone! {len(changes)} ITTF IDs corrected.")
    print("Next steps:")
    print("  1. Run: python fetch_birth_years.py  (to re-scrape birth years)")
    print("  2. Run: python reimport_matches.py   (to re-import match data)")


if __name__ == "__main__":
    main()
