#!/usr/bin/env python3
"""Re-scrape and re-import match history for corrected ITTF IDs.

After fixing ITTF IDs (fix_ittf_ids.py --apply), the 83 corrected players
have new ITTF IDs but their match history still reflects the old (wrong) IDs.
This script re-scrapes match data using the correct IDs and imports it.

Usage:
    python reimport_matches.py                     # Fetch and save match data
    python reimport_matches.py --import            # Also import into Laravel
    python reimport_matches.py --limit 10          # Only process first 10 players
    python reimport_matches.py --delay 2.0         # Set custom delay between requests
"""

import argparse
import json
import subprocess
import sys
import time
from datetime import date
from pathlib import Path

sys.path.insert(0, str(Path(__file__).resolve().parent))

from ittf import _get_session
from config import IMPORT_DIR, PLAYER_MATCHES_URL, PLAYER_MATCHES_QS, LIST_PLAYER_MATCHES, ROWS_PER_PAGE
from parser import parse_player_matches_table, parse_pagination_info
from importer import transform_match, save_import_file

# Default delay between player requests (respect portal rate limits)
DEFAULT_DELAY = 3.0


def load_corrected_players() -> list[dict]:
    """Load the list of corrected players from birth_years.json.

    Returns list of {ittf_id, name} for the 83 corrected players.
    """
    birth_path = IMPORT_DIR / "birth_years.json"
    if not birth_path.exists():
        print(f"ERROR: {birth_path} not found. Run fetch_birth_years.py first.")
        sys.exit(1)

    birth_data = json.loads(birth_path.read_text())
    corrected_ids = list(birth_data.keys())

    # Build a name lookup from the comprehensive ranking files
    name_map = {}
    for fname in ["rankings_men_2026-07-06_all.json", "rankings_women_2026-07-06_all.json"]:
        fpath = IMPORT_DIR / fname
        if fpath.exists():
            data = json.loads(fpath.read_text())
            for row in data.get("rows", []):
                ittf_id = str(row.get("ittf_id", ""))
                name = row.get("name", "")
                if ittf_id and name:
                    name_map[ittf_id] = name

    players = []
    for pid in corrected_ids:
        name = name_map.get(pid, f"Player {pid}")
        players.append({"ittf_id": pid, "name": name})

    return players


def fetch_player_matches(session, ittf_id: str, min_year: int) -> list[dict]:
    """Fetch complete match history for a single player from the ITTF portal.

    Args:
        session: Authenticated ITTF session.
        ittf_id: Player's ITTF ID.
        min_year: Minimum year to include matches from.

    Returns:
        List of transformed match dicts.
    """
    all_matches = []
    page = 0

    while True:
        offset = page * ROWS_PER_PAGE
        qs = PLAYER_MATCHES_QS.format(player_id=ittf_id)
        url = f"{PLAYER_MATCHES_URL}?{qs}&limitstart{LIST_PLAYER_MATCHES}={offset}"

        try:
            resp = session.get(url, timeout=30)
            resp.raise_for_status()
        except Exception as e:
            print(f"ERROR: {e}")
            break

        matches = parse_player_matches_table(resp.text)
        if not matches:
            break

        all_matches.extend(matches)

        pagination = parse_pagination_info(resp.text)
        total_pages = pagination.get("total_pages", 0)
        if total_pages and page + 1 >= total_pages:
            break
        page += 1

    # Filter by year and annotate
    filtered = []
    for m in all_matches:
        year_str = m.get("year", "")
        if year_str and year_str.isdigit():
            if int(year_str) >= min_year:
                m["player_ittf_id"] = ittf_id
                filtered.append(m)
        else:
            m["player_ittf_id"] = ittf_id
            filtered.append(m)

    return [transform_match(m) for m in filtered]


def import_to_laravel(filename: str) -> bool:
    """Import a match JSON file into Laravel using the import:ittf command."""
    cmd = ["php", "artisan", "import:ittf", "matches", "--file", filename]
    project_root = Path(__file__).resolve().parent.parent.parent

    try:
        result = subprocess.run(
            cmd,
            capture_output=True,
            text=True,
            cwd=str(project_root),
            timeout=120,
        )
        print(f"  Import result: {result.stdout.strip()}")
        if result.returncode != 0 and result.stderr:
            print(f"  Import stderr: {result.stderr.strip()}")
        return result.returncode == 0
    except subprocess.TimeoutExpired:
        print(f"  Import timed out!")
        return False
    except FileNotFoundError:
        print(f"  PHP not found. Import manually: {' '.join(cmd)}")
        return False


def main():
    parser = argparse.ArgumentParser(description="Re-import matches for corrected ITTF IDs")
    parser.add_argument("--import", action="store_true", dest="do_import",
                        help="Import match data into Laravel after scraping")
    parser.add_argument("--limit", type=int, default=0,
                        help="Max players to process (default: all 83)")
    parser.add_argument("--delay", type=float, default=DEFAULT_DELAY,
                        help=f"Delay between requests in seconds (default: {DEFAULT_DELAY})")
    parser.add_argument("--min-year", type=int, default=None,
                        help="Minimum year for matches (default: 2 years ago)")
    args = parser.parse_args()

    today = date.today().isoformat()
    current_year = date.today().year
    min_year = args.min_year if args.min_year else current_year - 2

    # Load corrected players
    players = load_corrected_players()
    print(f"Loaded {len(players)} corrected players\n")

    if args.limit > 0:
        players = players[:args.limit]

    # Get authenticated session
    session = _get_session()

    # Fetch matches for each player
    all_matches = []
    errors = []
    processed = 0

    for i, player in enumerate(players):
        ittf_id = player["ittf_id"]
        name = player["name"]

        print(f"[{i+1}/{len(players)}] {name} (ID: {ittf_id})... ", end="", flush=True)

        try:
            matches = fetch_player_matches(session, ittf_id, min_year)
            all_matches.extend(matches)
            print(f"{len(matches)} singles matches")
            processed += 1
        except Exception as e:
            print(f"ERROR: {e}")
            errors.append(f"Player {ittf_id} ({name}): {e}")

        # Rate limiting
        if i < len(players) - 1:
            time.sleep(args.delay)

    # Summary
    print(f"\n{'='*60}")
    print(f"Processed: {processed}/{len(players)} players")
    print(f"Total matches: {len(all_matches)}")
    if errors:
        print(f"Errors: {len(errors)}")
        for e in errors[:5]:
            print(f"  - {e}")

    # Save matches
    if not all_matches:
        print("\nNo matches to save.")
        return

    output = {
        "source": f"ITTF reimport for corrected players",
        "fetched_at": today,
        "min_year": min_year,
        "players_processed": processed,
        "count": len(all_matches),
        "rows": all_matches,
    }

    filename = f"matches_reimport_{today}"
    filepath = save_import_file(output, filename)
    print(f"\nSaved: {filepath}")

    # Optionally import into Laravel
    if args.do_import:
        print(f"\nImporting into Laravel...")
        import_to_laravel(f"{filename}.json")

        print(f"\nDone! Verify the import with:")
        print(f"  php artisan import:ittf matches --file={filename}.json")

    print(f"\nNext step:")
    print(f"  php artisan import:ittf matches --file={filename}.json")


if __name__ == "__main__":
    main()
