#!/usr/bin/env python3
"""Scrape matches for all top 100 players without matches."""

import sys
import time
import json
from pathlib import Path
from datetime import date

sys.path.insert(0, str(Path(__file__).parent))

from auth import IttfSession
from config import (
    IMPORT_DIR,
    LIST_PLAYER_MATCHES,
    PLAYER_MATCHES_QS,
    PLAYER_MATCHES_URL,
    ROWS_PER_PAGE,
)
from parser import parse_player_matches_table, parse_pagination_info
from importer import transform_match, save_import_file

DELAY = 3
MIN_YEAR = date.today().year - 2

# All top 100 players without matches (using ITTF IDs where known, WTT IDs otherwise)
PLAYERS = [
    # Women - have ITTF IDs
    {"name": "Xingtong CHEN", "ittf_id": "121403", "rank": 5, "gender": "F"},
    {"name": "Yi CHEN", "ittf_id": "132132", "rank": 6, "gender": "F"},
    {"name": "Yidi WANG", "ittf_id": "124110", "rank": 8, "gender": "F"},
    # Women - using WTT IDs as ITTF IDs
    {"name": "Amy WANG", "ittf_id": "119022", "rank": 46, "gender": "F"},
    {"name": "Seongjin KIM", "ittf_id": "135370", "rank": 71, "gender": "F"},
    {"name": "Xiaoxin YANG", "ittf_id": "112868", "rank": 72, "gender": "F"},
    {"name": "Yiyun YANG", "ittf_id": "135922", "rank": 78, "gender": "F"},
    {"name": "Xiaotong WANG", "ittf_id": "136539", "rank": 97, "gender": "F"},
    {"name": "Eugene WANG", "ittf_id": "114247", "rank": 100, "gender": "F"},
    # Men - using WTT IDs as ITTF IDs
    {"name": "Yun-Ju LIN", "ittf_id": "121582", "rank": 7, "gender": "M"},
    {"name": "Gyuhyeon PARK", "ittf_id": "133813", "rank": 58, "gender": "M"},
]


def scrape_player(session, ittf_id, rank):
    """Scrape all matches for a player."""
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
            print(f"    ERROR on page {page + 1}: {e}")
            break

        matches = parse_player_matches_table(resp.text)
        if not matches:
            if page == 0:
                return []
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
            if int(year_str) >= MIN_YEAR:
                m["player_rank"] = rank
                m["player_ittf_id"] = str(ittf_id)
                filtered.append(m)
        else:
            m["player_rank"] = rank
            m["player_ittf_id"] = str(ittf_id)
            filtered.append(m)

    return filtered


def main():
    session = IttfSession()
    if not session.load():
        print("No saved session. Run: python ittf.py login --username ... --password ...")
        sys.exit(1)

    print("Session loaded.\n")

    all_matches = []
    summary = []

    for i, player in enumerate(PLAYERS):
        name = player["name"]
        ittf_id = player["ittf_id"]
        rank = player["rank"]

        print(f"[{i+1}/{len(PLAYERS)}] #{rank} {name} (ID: {ittf_id})... ", end="", flush=True)

        try:
            matches = scrape_player(session, ittf_id, rank)
            print(f"{len(matches)} matches")
            all_matches.extend(transform_match(m) for m in matches)
            summary.append({"name": name, "rank": rank, "ittf_id": ittf_id, "matches": len(matches)})
        except Exception as e:
            print(f"ERROR: {e}")
            summary.append({"name": name, "rank": rank, "ittf_id": ittf_id, "matches": 0, "error": str(e)})

        if i < len(PLAYERS) - 1:
            time.sleep(DELAY)

    print(f"\n{'='*60}")
    print(f"Total matches scraped: {len(all_matches)}")

    # Save matches
    if all_matches:
        today = date.today().isoformat()
        output = {
            "source": "ITTF top 100 missing players matches (scraped individually)",
            "fetched_at": today,
            "min_year": MIN_YEAR,
            "players_processed": len(PLAYERS),
            "count": len(all_matches),
            "rows": all_matches,
        }
        filepath = save_import_file(output, f"matches_top100_missing_{today}")
        print(f"Saved: {filepath}")

    # Save summary
    summary_path = IMPORT_DIR / "scrape_summary_top100_missing.json"
    summary_path.write_text(json.dumps(summary, indent=2))
    print(f"Summary: {summary_path}")


if __name__ == "__main__":
    main()
