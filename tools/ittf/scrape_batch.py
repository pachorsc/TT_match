"""Scrape matches for a batch of players from the full ranking file."""
import sys
import time
import json
import argparse
from pathlib import Path
from datetime import date

sys.path.insert(0, str(Path(__file__).parent))

from ittf import _get_session
from config import PLAYER_MATCHES_URL, PLAYER_MATCHES_QS, LIST_PLAYER_MATCHES, ROWS_PER_PAGE
from parser import parse_player_matches_table, parse_pagination_info
from importer import transform_match, save_import_file

DELAY = 3
MIN_YEAR = date.today().year - 5


def scrape_batch(ranking_file: str, start: int, end: int):
    """Scrape matches for players from rank start to end (1-indexed)."""
    ranking_path = Path(ranking_file)
    if not ranking_path.exists():
        # Try in import dir
        ranking_path = Path("storage/app/import/ittf") / ranking_file
    if not ranking_path.exists():
        print(f"Ranking file not found: {ranking_file}")
        return

    data = json.loads(ranking_path.read_text())
    players = data.get("rows", [])

    # Extract the batch (0-indexed)
    batch = players[start-1:end]
    if not batch:
        print(f"No players in range {start}-{end}")
        return

    session = _get_session()
    today = date.today().isoformat()
    all_matches = []
    errors = []

    for i, player in enumerate(batch):
        rank = start + i
        ittf_id = player.get("ittf_id", "")
        name = player.get("name", f"Player {ittf_id}")

        if not ittf_id:
            errors.append(f"Rank {rank}: missing ittf_id")
            continue

        print(f"  [{rank}/100] {name} (ID: {ittf_id})... ", end="", flush=True)
        player_matches_list = []
        page = 0

        while True:
            offset = page * ROWS_PER_PAGE
            qs = PLAYER_MATCHES_QS.format(player_id=ittf_id)
            url = f"{PLAYER_MATCHES_URL}?{qs}&limitstart{LIST_PLAYER_MATCHES}={offset}"
            try:
                resp = session.get(url)
                resp.raise_for_status()
            except Exception as e:
                print(f"ERROR: {e}")
                errors.append(f"Player {ittf_id} ({name}): {e}")
                break

            matches = parse_player_matches_table(resp.text)
            if not matches:
                break

            player_matches_list.extend(matches)
            pagination = parse_pagination_info(resp.text)
            total_pages = pagination.get("total_pages", 0)
            if total_pages and page + 1 >= total_pages:
                break
            page += 1

        filtered = []
        for m in player_matches_list:
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

        all_matches.extend(transform_match(m) for m in filtered)
        print(f"{len(filtered)} singles matches")

        if i < len(batch) - 1:
            time.sleep(DELAY)

    print(f"\nTotal: {len(all_matches)} matches from {len(batch)} players")
    if errors:
        print(f"Errors ({len(errors)}): {errors[:5]}")

    if all_matches:
        output = {
            "source": f"ITTF top 100 men matches (batch {start}-{end})",
            "fetched_at": today,
            "gender": "men",
            "min_year": MIN_YEAR,
            "players_processed": len(batch),
            "count": len(all_matches),
            "rows": all_matches,
        }
        filepath = save_import_file(output, f"matches_men_{today}_batch{start}_{end}")
        print(f"Saved: {filepath}")


if __name__ == "__main__":
    parser = argparse.ArgumentParser()
    parser.add_argument("--ranking", default="rankings_men_2026-07-05_full.json")
    parser.add_argument("--start", type=int, required=True)
    parser.add_argument("--end", type=int, required=True)
    args = parser.parse_args()
    scrape_batch(args.ranking, args.start, args.end)
