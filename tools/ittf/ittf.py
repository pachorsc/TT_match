#!/usr/bin/env python3
"""ITTF Portal CLI — Scrape rankings and player data from results.ittf.link.

Usage:
    python ittf.py login --username USERNAME --password PASSWORD
    python ittf.py fetch rankings --gender men
    python ittf.py fetch rankings --gender women
    python ittf.py fetch player --id 121558
    python ittf.py files
    python ittf.py preview <filename>
"""

import re
import sys
from datetime import date

import click

from auth import IttfSession
from config import (
    BASE_URL,
    IMPORT_DIR,
    LIST_RANKING_MS,
    LIST_RANKING_WS,
    PLAYER_PROFILE_URL,
    RANKING_URLS,
    ROWS_PER_PAGE,
)
from importer import (
    list_import_files,
    load_import_file,
    save_import_file,
    transform_ranking,
)
from parser import (
    parse_pagination_info,
    parse_player_profile,
    parse_ranking_table,
)


@click.group()
def cli():
    """ITTF Portal — Table Tennis Data Scraper."""
    pass


def _get_session(require_login: bool = True) -> IttfSession:
    """Get an authenticated session."""
    session = IttfSession()
    if session.load():
        click.echo("Session restored from disk.")
        return session

    if require_login:
        click.echo("No saved session found.", err=True)
        click.echo("Run: python ittf.py login --username ... --password ...", err=True)
        sys.exit(1)

    return session


@cli.command()
@click.option("--username", prompt=True, help="ITTF portal username")
@click.option("--password", prompt=True, hide_input=True, help="ITTF portal password")
def login(username: str, password: str):
    """Login to the ITTF results portal and save session."""
    session = IttfSession()
    click.echo(f"Logging in as '{username}'...")
    success = session.login(username, password)

    if success:
        click.echo("Login successful! Session saved.")
    else:
        click.echo("Login failed. Check your credentials.", err=True)
        sys.exit(1)


@cli.group()
def fetch():
    """Fetch data from the ITTF portal."""
    pass


@fetch.command()
@click.option("--gender", default="men", type=click.Choice(["men", "women"]))
@click.option("--max-pages", default=None, type=int, help="Max pages to fetch (default: all)")
def rankings(gender: str, max_pages: int | None):
    """Fetch ITTF rankings for a given gender."""
    session = _get_session()

    url_template = RANKING_URLS[gender]
    today = date.today().isoformat()
    all_rows: list[dict] = []
    page = 0
    list_id = LIST_RANKING_MS if gender == "men" else LIST_RANKING_WS

    click.echo(f"Fetching {gender} rankings...")

    while True:
        offset = page * ROWS_PER_PAGE
        url = f"{url_template}?limitstart{list_id}={offset}"
        click.echo(f"  Page {page + 1} (offset {offset})... ", nl=False)

        try:
            resp = session.get(url)
            resp.raise_for_status()
        except Exception as e:
            click.echo(f"ERROR: {e}")
            break

        rows = parse_ranking_table(resp.text, gender=gender)
        if not rows:
            click.echo("No rows found.")
            break

        all_rows.extend(rows)
        click.echo(f"{len(rows)} players")

        # Check pagination
        pagination = parse_pagination_info(resp.text)
        total_pages = pagination.get("total_pages", 0)

        if max_pages and page + 1 >= max_pages:
            break

        # If we got fewer rows than a full page, we're done
        if len(rows) < ROWS_PER_PAGE:
            break

        # If pagination info tells us total pages, use that
        if total_pages and page + 1 >= total_pages:
            break

        page += 1

    click.echo(f"\nTotal: {len(all_rows)} players")

    # Save
    transformed = [transform_ranking(r) for r in all_rows]
    output = {
        "source": f"ITTF {gender} rankings",
        "fetched_at": today,
        "gender": gender,
        "count": len(transformed),
        "rows": transformed,
    }

    filename = f"rankings_{gender}_{today}"
    filepath = save_import_file(output, filename)
    click.echo(f"Saved: {filepath}")


@fetch.command()
@click.option("--id", "player_id", required=True, type=int, help="ITTF player ID")
def player(player_id: int):
    """Fetch a single player's profile and recent matches."""
    session = _get_session()

    url = f"{PLAYER_PROFILE_URL}?vw_profiles___player_id_raw={player_id}"
    click.echo(f"Fetching player {player_id}...")

    try:
        resp = session.get(url)
        resp.raise_for_status()
    except Exception as e:
        click.echo(f"ERROR fetching player {player_id}: {e}", err=True)
        sys.exit(1)

    profile = parse_player_profile(resp.text)
    click.echo(f"  Name: {profile.get('profile', {}).get('name', 'N/A')}")
    click.echo(f"  Recent matches: {len(profile.get('recent_matches', []))}")

    today = date.today().isoformat()

    # Save profile
    profile_output = {
        "source": f"ITTF player profile {player_id}",
        "fetched_at": today,
        "player_id": player_id,
        "count": 1,
        "rows": [profile.get("profile", {})],
    }
    filename = f"player_{player_id}_{today}"
    save_import_file(profile_output, filename)
    click.echo(f"Profile saved: {IMPORT_DIR / filename}.json")

    # Save recent matches
    matches = profile.get("recent_matches", [])
    if matches:
        matches_output = {
            "source": f"ITTF player matches {player_id}",
            "fetched_at": today,
            "player_id": player_id,
            "count": len(matches),
            "rows": matches,
        }
        match_filename = f"player_matches_{player_id}_{today}"
        save_import_file(matches_output, match_filename)
        click.echo(f"Matches saved: {IMPORT_DIR / match_filename}.json")


@cli.command()
def files():
    """List all import files."""
    files = list_import_files()
    if not files:
        click.echo("No import files found.")
        return

    click.echo("Import files:\n")
    for f in files:
        size = f.stat().st_size
        click.echo(f"  {f.name:50} {size:>8} bytes")


@cli.command()
@click.argument("filename")
def preview(filename: str):
    """Preview an import file."""
    try:
        data = load_import_file(filename)
    except FileNotFoundError as e:
        click.echo(str(e), err=True)
        sys.exit(1)

    click.echo(f"Source: {data.get('source', 'N/A')}")
    click.echo(f"Rows: {data.get('count', 0)}\n")

    rows = data.get("rows", [])
    if rows:
        headers = list(rows[0].keys())
        click.echo("  ".join(h for h in headers[:6]))
        click.echo("-" * 80)
        for row in rows[:10]:
            click.echo("  ".join(str(row.get(h, ""))[:25] for h in headers[:6]))
        if len(rows) > 10:
            click.echo(f"  ... and {len(rows) - 10} more rows")


if __name__ == "__main__":
    cli()
