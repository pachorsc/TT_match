#!/usr/bin/env python3
"""WTT Singles Match Fetcher — Fetch all singles matches from a WTT Grand Smash event.

Extracts scores directly from bracket API (no need for individual match data endpoint).

Usage:
    python fetch_singles.py                        # Default: US Smash 2026 (eventId=3242)
    python fetch_singles.py --event-id 3242
    python fetch_singles.py --event-id 3242 --players-file players_wtt_ids.json
"""

import json
import re
import time
from pathlib import Path

import brotli
import requests

PROJECT_ROOT = Path(__file__).resolve().parent.parent.parent
OUTPUT_DIR = PROJECT_ROOT / "storage" / "app" / "import" / "wtt"
OUTPUT_DIR.mkdir(parents=True, exist_ok=True)

BRACKET_API = "https://liveeventsapi.worldtabletennis.com/api/cms/GetBrackets/{event_id}/{sub_event}"

HEADERS = {
    "Origin": "https://www.worldtabletennis.com",
    "Referer": "https://www.worldtabletennis.com/",
    "Accept-Encoding": "gzip, deflate, br",
    "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36",
}

SUB_EVENTS = {
    "TTEMSINGLES": "Men's Singles",
    "TTEWSINGLES": "Women's Singles",
}


def fetch_bracket(event_id: int, sub_event_code: str) -> dict:
    """Fetch bracket data for a subevent."""
    url = BRACKET_API.format(event_id=event_id, sub_event=sub_event_code)
    resp = requests.get(url, headers=HEADERS, timeout=30)
    resp.raise_for_status()
    raw = resp.content
    try:
        data = brotli.decompress(raw)
    except Exception:
        data = raw
    return json.loads(data)


def parse_score_string(result_str: str) -> tuple[str, str]:
    """Parse result string like '3-0 (11:6,15:13,11:3,0:0,0:0)' into overall_scores and game_scores."""
    if not result_str:
        return ("", "")

    match = re.match(r"(\d+-\d+)\s*\(([^)]+)\)", result_str)
    if match:
        overall = match.group(1)
        games_raw = match.group(2)
        games = [g.strip() for g in games_raw.split(",") if g.strip() and g.strip() != "0:0"]
        game_scores = ",".join(games)
        return (overall, game_scores)

    match_simple = re.match(r"(\d+-\d+)", result_str)
    if match_simple:
        return (match_simple.group(1), "")

    return ("", "")


def extract_matches_from_bracket(bracket_data: dict, sub_event_name: str) -> list[dict]:
    """Extract all match document codes, player ITTF IDs, and scores from MAIN bracket only."""
    matches = []
    bracket = bracket_data.get("Competition", {}).get("Bracket", [])

    for b in bracket:
        bracket_code = b.get("Code", "")
        if bracket_code != "MAIN":
            continue

        items = b.get("BracketItems", [])
        for item in items:
            ci = item.get("BracketItem", [])
            for match in ci:
                code = match.get("Code", "").strip()
                if not code:
                    continue

                competitors = match.get("CompetitorPlace", [])
                player_a_id = None
                player_b_id = None
                winner_ittf_id = None
                player_a_sets = 0
                player_b_sets = 0

                if len(competitors) >= 2:
                    c1 = competitors[0]
                    c2 = competitors[1]
                    comp1 = c1.get("Competitor", {})
                    comp2 = c2.get("Competitor", {})
                    if comp1:
                        player_a_id = str(comp1.get("Code", ""))
                    if comp2:
                        player_b_id = str(comp2.get("Code", ""))

                    player_a_sets = int(c1.get("Result", 0) or 0)
                    player_b_sets = int(c2.get("Result", 0) or 0)

                    if c1.get("Wlt") == "W" and player_a_id:
                        winner_ittf_id = player_a_id
                    elif c2.get("Wlt") == "W" and player_b_id:
                        winner_ittf_id = player_b_id

                result_str = match.get("Result", "")
                overall_scores, game_scores = parse_score_string(result_str)

                if not overall_scores and player_a_sets and player_b_sets:
                    overall_scores = f"{player_a_sets}-{player_b_sets}"

                matches.append({
                    "document_code": code,
                    "sub_event": sub_event_name,
                    "player_a_ittf_id": player_a_id,
                    "player_b_ittf_id": player_b_id,
                    "overall_scores": overall_scores,
                    "game_scores": game_scores,
                    "winner_ittf_id": winner_ittf_id,
                    "date": match.get("Date", ""),
                    "completed": bool(result_str),
                })

    return matches


def filter_matches_by_players(matches: list[dict], player_ids: set[str]) -> list[dict]:
    """Filter matches where both players are in the player_ids set."""
    filtered = []
    for m in matches:
        a_id = m.get("player_a_ittf_id", "")
        b_id = m.get("player_b_ittf_id", "")
        if a_id and b_id and a_id in player_ids and b_id in player_ids:
            filtered.append(m)
    return filtered


def load_player_ids(filepath: str | None) -> set[str]:
    """Load player ITTF IDs from a JSON file or return empty set."""
    if not filepath:
        return set()

    path = Path(filepath)
    if not path.exists():
        print(f"Player IDs file not found: {filepath}")
        return set()

    with open(path) as f:
        data = json.load(f)

    if isinstance(data, list):
        return set(str(p) for p in data)
    elif isinstance(data, dict) and "wtt_ids" in data:
        return set(str(p) for p in data["wtt_ids"])
    elif isinstance(data, dict) and "players" in data:
        return set(str(p.get("wtt_id", "")) for p in data["players"] if p.get("wtt_id"))

    return set()


def main():
    import argparse

    parser = argparse.ArgumentParser(description="WTT Singles Match Fetcher")
    parser.add_argument("--event-id", type=int, default=3242, help="WTT Event ID")
    parser.add_argument("--players-file", type=str, default=None, help="JSON file with player wtt_ids to filter")
    parser.add_argument("--output", type=str, default=None, help="Output filename")
    args = parser.parse_args()

    event_id = args.event_id
    output_file = args.output or f"{event_id}_singles_matches.json"

    print(f"Fetching singles brackets for event {event_id}...")

    all_matches = []
    for code, name in SUB_EVENTS.items():
        print(f"  Fetching {name} ({code})...")
        try:
            bracket = fetch_bracket(event_id, code)
            matches = extract_matches_from_bracket(bracket, name)
            total = bracket.get("TotalMatchesForSubEvent", len(matches))
            completed = sum(1 for m in matches if m["completed"])
            print(f"    Found {len(matches)} main draw matches ({completed} completed)")
            all_matches.extend(matches)
        except Exception as e:
            print(f"    Error: {e}")

    print(f"\nTotal main draw singles matches: {len(all_matches)}")
    completed_count = sum(1 for m in all_matches if m["completed"])
    print(f"Completed: {completed_count} | Scheduled: {len(all_matches) - completed_count}")

    player_ids = load_player_ids(args.players_file)
    if player_ids:
        print(f"Filtering by {len(player_ids)} player IDs...")
        all_matches = filter_matches_by_players(all_matches, player_ids)
        completed_count = sum(1 for m in all_matches if m["completed"])
        print(f"Matches after filtering: {len(all_matches)} ({completed_count} completed)")

    output = {
        "source": "worldtabletennis.com",
        "event_id": event_id,
        "fetched_at": time.strftime("%Y-%m-%dT%H:%M:%S"),
        "count": len(all_matches),
        "matches": all_matches,
    }

    filepath = OUTPUT_DIR / output_file
    with open(filepath, "w") as f:
        json.dump(output, f, indent=2)

    print(f"\nSaved {len(all_matches)} matches to: {filepath}")
    print(f"\nTo import into Laravel, run:")
    print(f"  php artisan wtt:import-matches --file={output_file}")


if __name__ == "__main__":
    main()
