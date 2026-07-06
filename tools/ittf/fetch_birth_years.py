#!/usr/bin/env python3
"""Fetch birth years from ITTF portal for players without DOB.

Usage:
    python fetch_birth_years.py                    # Fetch for all players without DOB
    python fetch_birth_years.py --limit 50         # Fetch for top 50 only
    python fetch_birth_years.py --ittf-id 113679   # Fetch for specific player
"""

import json
import re
import sys
import time
from pathlib import Path

# Add parent to path for imports
sys.path.insert(0, str(Path(__file__).resolve().parent))

from auth import IttfSession
from config import BASE_URL, PLAYER_PROFILE_URL, SESSION_PATH, IMPORT_DIR

# Laravel DB path
PROJECT_ROOT = Path(__file__).resolve().parent.parent.parent
LARAVEL_PATH = PROJECT_ROOT


def get_laravel_players_without_dob():
    """Query Laravel DB via artisan tinker for players without DOB."""
    import subprocess

    cmd = [
        "php", "artisan", "tinker", "--execute",
        "echo json_encode(App\\Models\\Player::whereNull('date_of_birth')"
        "->whereNotNull('ittf_id')->where('ittf_id','!=','')"
        "->orderBy('world_ranking')"
        "->get(['id','ittf_id','first_name','last_name','world_ranking'])"
        "->toArray());"
    ]

    result = subprocess.run(cmd, capture_output=True, text=True, cwd=str(LARAVEL_PATH), timeout=30)
    if result.returncode != 0:
        print(f"Error: {result.stderr}")
        return []

    try:
        data = json.loads(result.stdout.strip())
        return data
    except json.JSONDecodeError:
        print(f"Failed to parse JSON: {result.stdout[:200]}")
        return []


def fetch_birth_year_from_profile(session: IttfSession, ittf_id: str) -> int | None:
    """Fetch birth year from ITTF player profile page."""
    url = f"{PLAYER_PROFILE_URL}?resetfilters=1&vw_profiles___player_id_raw%5Bvalue%5D%5B%5D={ittf_id}"

    try:
        resp = session.get(url, timeout=30)
        resp.raise_for_status()
    except Exception as e:
        print(f"    HTTP error: {e}")
        return None

    html = resp.text

    # Extract birth year from profile details
    # HTML format: "Birth Year: <span class='notranslate'>2001</span>"
    match = re.search(r"Birth Year:\s*(?:<[^>]+>)?\s*(\d{4})", html)
    if match:
        return int(match.group(1))

    return None


def save_results(results: dict, filename: str = "birth_years.json"):
    """Save birth year mappings to JSON file."""
    filepath = IMPORT_DIR / filename
    with open(filepath, "w") as f:
        json.dump(results, f, indent=2)
    print(f"\nSaved {len(results)} birth years to: {filepath}")
    return filepath


def main():
    import argparse

    parser = argparse.ArgumentParser(description="Fetch birth years from ITTF portal")
    parser.add_argument("--limit", type=int, default=None, help="Max players to fetch")
    parser.add_argument("--ittf-id", type=str, default=None, help="Fetch single player by ITTF ID")
    parser.add_argument("--delay", type=float, default=1.5, help="Delay between requests (seconds)")
    args = parser.parse_args()

    # Load or create session
    session = IttfSession()
    if not session.load():
        print("No valid session found. Logging in...")
        from config import SESSION_PATH
        creds_path = Path(__file__).resolve().parent / "credentials.json"
        if creds_path.exists():
            creds = json.loads(creds_path.read_text())
            if not session.login(creds.get("username", ""), creds.get("password", "")):
                print("Login failed!")
                sys.exit(1)
        else:
            print(f"Credentials not found at {creds_path}")
            sys.exit(1)

    print("Session loaded OK\n")

    if args.ittf_id:
        # Single player mode
        year = fetch_birth_year_from_profile(session, args.ittf_id)
        if year:
            print(f"ITTF ID {args.ittf_id}: Birth Year = {year}")
            save_results({args.ittf_id: year}, "birth_years_single.json")
        else:
            print(f"ITTF ID {args.ittf_id}: Birth Year not found")
        return

    # Bulk mode — get players from Laravel
    print("Fetching players without DOB from database...")
    players = get_laravel_players_without_dob()
    print(f"Found {len(players)} players\n")

    if args.limit:
        players = players[:args.limit]

    results = {}
    errors = 0

    for i, player in enumerate(players):
        ittf_id = player.get("ittf_id", "")
        name = f"{player.get('first_name', '')} {player.get('last_name', '')}".strip()
        ranking = player.get("world_ranking", "?")

        print(f"[{i+1}/{len(players)}] #{ranking} {name} (ID: {ittf_id})... ", end="", flush=True)

        year = fetch_birth_year_from_profile(session, ittf_id)

        if year:
            results[ittf_id] = year
            print(f"{year}")
        else:
            errors += 1
            print("not found")

        # Rate limit — 1.5s between requests
        if i < len(players) - 1:
            time.sleep(args.delay)

        # Save intermediate results every 50 players
        if (i + 1) % 50 == 0:
            save_results(results, f"birth_years_partial_{i+1}.json")

    # Save final results
    save_results(results)

    print(f"\nDone: {len(results)} birth years found, {errors} errors")


if __name__ == "__main__":
    main()
