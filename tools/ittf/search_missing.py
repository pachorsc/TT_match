#!/usr/bin/env python3
"""Search ITTF portal for missing player IDs."""

import json
import re
import sys
import time
from pathlib import Path

sys.path.insert(0, str(Path(__file__).parent))

from auth import IttfSession
from config import BASE_URL, PLAYER_PROFILE_URL

# Players missing ITTF IDs
MISSING_PLAYERS = [
    {"name": "Manyu WANG", "country": "CN", "rank": 2},
    {"name": "Xingtong CHEN", "country": "CN", "rank": 5},
    {"name": "Yi CHEN", "country": "CN", "rank": 6},
    {"name": "Yidi WANG", "country": "CN", "rank": 8},
    {"name": "Amy WANG", "country": "US", "rank": 46},
    {"name": "Yu-Jie HUANG", "country": "TW", "rank": 67},
    {"name": "Seongjin KIM", "country": "KR", "rank": 71},
    {"name": "Xiaoxin YANG", "country": "MO", "rank": 72},
    {"name": "Yiyun YANG", "country": "CN", "rank": 78},
    {"name": "Gahyeon PARK", "country": "KR", "rank": 84},
    {"name": "Xiaotong WANG", "country": "CN", "rank": 97},
]


def search_player(session: IttfSession, surname: str, given: str) -> list[dict]:
    """Search for a player by surname and given name via the player profile page."""
    # The profile list page supports filtering by name
    url = f"{PLAYER_PROFILE_URL}?resetfilters=1&vw_profiles___surname_raw={surname}&vw_profiles___given_name_raw={given}"
    try:
        resp = session.get(url)
        resp.raise_for_status()
    except Exception as e:
        print(f"  ERROR searching {given} {surname}: {e}")
        return []

    # Parse the results table
    from bs4 import BeautifulSoup
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


def search_by_surname(session: IttfSession, surname: str) -> list[dict]:
    """Search by surname only."""
    url = f"{PLAYER_PROFILE_URL}?resetfilters=1&vw_profiles___surname_raw={surname}"
    try:
        resp = session.get(url)
        resp.raise_for_status()
    except Exception as e:
        print(f"  ERROR searching surname {surname}: {e}")
        return []

    from bs4 import BeautifulSoup
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
                entry["name"] = (link.get_text(strip=True) if link else cell.get_text(strip=True))
            elif "country_raw" in cls:
                entry["country"] = cell.get_text(strip=True)

        if "ittf_id" in entry and entry["ittf_id"]:
            results.append(entry)

    return results


def main():
    session = IttfSession()
    if not session.load():
        print("No saved session. Run: python ittf.py login --username ... --password ...")
        sys.exit(1)

    print("Session loaded.\n")

    found = {}
    not_found = []

    for player in MISSING_PLAYERS:
        full_name = player["name"]
        parts = full_name.rsplit(" ", 1)
        if len(parts) == 2:
            given, surname = parts
        else:
            given, surname = "", full_name

        print(f"Searching #{player['rank']} {full_name} ({player['country']})...")

        # Try full name search first
        results = search_player(session, surname, given)

        if not results:
            # Try surname only
            results = search_by_surname(session, surname)

        if results:
            # Filter by country if possible
            country_match = [r for r in results if player["country"].lower() in r.get("country", "").lower()]
            if country_match:
                best = country_match[0]
            else:
                best = results[0]

            print(f"  Found: {best.get('name', 'N/A')} (ID: {best['ittf_id']}, Country: {best.get('country', 'N/A')})")
            found[full_name] = best["ittf_id"]
        else:
            print(f"  NOT FOUND")
            not_found.append(full_name)

        time.sleep(1)

    print(f"\n{'='*60}")
    print(f"Found: {len(found)}/{len(MISSING_PLAYERS)}")
    print(f"\nSQL UPDATE statements:")
    for name, ittf_id in found.items():
        last = name.split(" ")[-1].upper()
        first = " ".join(name.split(" ")[:-1])
        print(f"  UPDATE players SET ittf_id = '{ittf_id}' WHERE last_name LIKE '%{last}%' AND first_name LIKE '%{first}%' AND gender = 'F';")

    if not_found:
        print(f"\nNot found ({len(not_found)}):")
        for name in not_found:
            print(f"  - {name}")


if __name__ == "__main__":
    main()
