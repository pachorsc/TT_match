#!/usr/bin/env python3
"""StatsTT CLI — Import table tennis data from StatsTT.

Usage:
    python statstt.py login              # Login via browser
    python statstt.py status             # Check remaining queries
    python statstt.py schema             # Show database schema
    python statstt.py query "SQL..."     # Run custom SQL query
    python statstt.py import --type rankings --gender male --limit 20
    python statstt.py import --type player --id 12345
    python statstt.py import --type matches --player-id 12345 --limit 20
    python statstt.py import --type all --limit 10
"""

import json
import sys

import click

from api import StatsTTAPI, QuotaExceededError, RateLimitError
from auth import login_interactive, save_token, load_token, token_valid
from importer import (
    load_import_file,
    list_import_files,
    transform_player,
    transform_match,
    transform_tournament,
)


@click.group()
def cli():
    """StatsTT — Table Tennis Data Import Tool."""
    pass


@cli.command()
def login():
    """Login to StatsTT via browser and save token."""
    try:
        token = login_interactive()
        save_token(token)
        click.echo(f"Token saved successfully!")
    except RuntimeError as e:
        click.echo(f"Login failed: {e}", err=True)
        sys.exit(1)


@cli.command()
def status():
    """Check remaining queries and token status."""
    token = load_token()
    if not token:
        click.echo("Not logged in. Run: python statstt.py login")
        return

    if not token_valid(token):
        click.echo("Token invalid. Run: python statstt.py login")
        return

    api = StatsTTAPI(token)
    try:
        user_info = api.get_user_info()
        if user_info:
            click.echo(f"Plan: {user_info.get('plan_type', 'unknown')}")
            click.echo(f"Queries today: {user_info.get('queries_today', '?')}")
            click.echo(f"Remaining: {user_info.get('queries_remaining', '?')}")
            click.echo(f"Data access level: {user_info.get('data_access_level', '?')}")
        else:
            click.echo("Could not fetch user info.")
    except Exception as e:
        click.echo(f"Error: {e}")

    data_status = api.get_data_status()
    if data_status:
        click.echo(f"Last updated: {data_status.get('last_updated', 'unknown')}")


@cli.command()
def schema():
    """Show StatsTT database schema."""
    api = StatsTTAPI()
    try:
        result = api.get_schema()
        tables = result.get("tables", [])
        columns = result.get("columns", {})

        click.echo(f"Tables ({len(tables)}):\n")
        for table in sorted(tables):
            cols = columns.get(table, [])
            click.echo(f"  {table}")
            for col in cols:
                click.echo(f"    - {col}")
            click.echo()
    except Exception as e:
        click.echo(f"Error fetching schema: {e}", err=True)


@cli.command()
@click.argument("sql")
def query(sql):
    """Run a custom SQL query."""
    token = load_token()
    api = StatsTTAPI(token)
    try:
        result = api.query_formatted(sql)
        click.echo(f"Rows: {len(result)}\n")
        if result:
            # Print as table
            headers = list(result[0].keys())
            click.echo("  ".join(headers))
            click.echo("-" * 80)
            for row in result:
                click.echo("  ".join(str(row.get(h, "")) for h in headers))
        else:
            click.echo("No results.")
    except QuotaExceededError as e:
        click.echo(f"Quota exceeded: {e}", err=True)
        sys.exit(1)
    except RateLimitError as e:
        click.echo(f"Rate limited: {e}", err=True)
        sys.exit(1)
    except Exception as e:
        click.echo(f"Error: {e}", err=True)
        sys.exit(1)


@cli.command("import")
@click.option("--type", "import_type", required=True, type=click.Choice(
    ["rankings", "player", "matches", "tournaments", "all"]
))
@click.option("--gender", default="male", type=click.Choice(["male", "female"]))
@click.option("--limit", default=20, type=int)
@click.option("--id", "player_id", type=int, help="Player StatsTT ID")
@click.option("--player-id", "player_id_from_opt", type=int, help="Player StatsTT ID for matches")
@click.option("--event-id", type=int, help="Event StatsTT ID for matches")
def import_cmd(import_type, gender, limit, player_id, player_id_from_opt, event_id):
    """Import data from StatsTT."""
    token = load_token()
    if not token:
        click.echo("Not logged in. Run: python statstt.py login")
        sys.exit(1)

    api = StatsTTAPI(token)

    try:
        if import_type == "rankings":
            _import_rankings(api, gender, limit)
        elif import_type == "player":
            if not player_id:
                click.echo("--id is required for player import")
                sys.exit(1)
            _import_player(api, player_id)
        elif import_type == "matches":
            pid = player_id or player_id_from_opt
            if not pid and not event_id:
                click.echo("--player-id or --event-id is required")
                sys.exit(1)
            _import_matches(api, pid, event_id, limit)
        elif import_type == "tournaments":
            _import_tournaments(api, limit)
        elif import_type == "all":
            _import_all(api, limit)

    except QuotaExceededError as e:
        click.echo(f"Quota exceeded: {e}", err=True)
        sys.exit(1)
    except RateLimitError as e:
        click.echo(f"Rate limited: {e}", err=True)
        sys.exit(1)
    except Exception as e:
        click.echo(f"Error: {e}", err=True)
        sys.exit(1)


def _import_rankings(api: StatsTTAPI, gender: str, limit: int):
    """Import rankings data."""
    query_key = f"rankings_{gender}"
    click.echo(f"Importing top {limit} {gender} rankings...")

    result = api.import_query(query_key, f"rankings_{gender}_{limit}", limit=limit)
    data = json.loads(result.read_text())
    click.echo(f"Saved {data['count']} rankings to {result}")

    # Also fetch player data for these rankings
    player_ids = [str(r.get("player_id", r.get("id", ""))) for r in data["rows"]]
    player_ids = [pid for pid in player_ids if pid]

    if player_ids:
        ids_str = ",".join(player_ids)
        click.echo(f"Fetching data for {len(player_ids)} players...")
        result = api.import_query(
            "players_by_ids", f"players_{gender}_top_{limit}", ids=ids_str
        )
        data = json.loads(result.read_text())
        click.echo(f"Saved {data['count']} players to {result}")


def _import_player(api: StatsTTAPI, player_id: int):
    """Import a single player."""
    click.echo(f"Importing player {player_id}...")
    result = api.import_query("player_by_id", f"player_{player_id}", id=player_id)
    data = json.loads(result.read_text())
    click.echo(f"Saved {data['count']} player(s) to {result}")


def _import_matches(api: StatsTTAPI, player_id: int | None, event_id: int | None, limit: int):
    """Import matches."""
    if player_id:
        click.echo(f"Importing matches for player {player_id}...")
        result = api.import_query(
            "matches_by_player",
            f"matches_player_{player_id}_{limit}",
            player_id=player_id,
            limit=limit,
        )
    else:
        click.echo(f"Importing matches for event {event_id}...")
        result = api.import_query(
            "matches_by_tournament",
            f"matches_event_{event_id}_{limit}",
            event_id=event_id,
            limit=limit,
        )

    data = json.loads(result.read_text())
    click.echo(f"Saved {data['count']} matches to {result}")


def _import_tournaments(api: StatsTTAPI, limit: int):
    """Import tournaments."""
    click.echo(f"Importing tournaments (limit {limit})...")
    # Use a generic query since there's no events list endpoint
    sql = f"SELECT * FROM events_basic LIMIT {limit}"
    result = api.query_formatted(sql)
    output = {"query": sql, "count": len(result), "rows": result}
    filepath = api.save_result(output, f"tournaments_{limit}")
    click.echo(f"Saved {len(result)} tournaments to {filepath}")


def _import_all(api: StatsTTAPI, limit: int):
    """Import everything within daily limits."""
    click.echo("Importing all data (respecting daily limits)...\n")

    queries_used = 0

    # 1. Male rankings
    click.echo("[1] Top male rankings...")
    _import_rankings(api, "male", limit)
    queries_used += 2
    click.echo()

    # 2. Female rankings (if queries remain)
    if queries_used < api.queries_remaining - 2:
        click.echo("[2] Top female rankings...")
        _import_rankings(api, "female", limit)
        queries_used += 2
        click.echo()

    # 3. Tournaments
    if queries_used < api.queries_remaining - 1:
        click.echo("[3] Tournaments...")
        _import_tournaments(api, limit)
        queries_used += 1
        click.echo()

    click.echo(f"Done. Used approximately {queries_used} queries.")
    click.echo(f"Remaining today: ~{api.queries_remaining}")


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
        click.echo(f"  {f.name:40} {size:>8} bytes")


@cli.command()
@click.argument("filename")
def preview(filename):
    """Preview an import file."""
    from config import IMPORT_DIR

    filepath = IMPORT_DIR / filename
    if not filepath.exists():
        click.echo(f"File not found: {filepath}")
        return

    with open(filepath) as f:
        data = json.load(f)

    click.echo(f"Query: {data.get('query', 'N/A')}")
    click.echo(f"Rows: {data.get('count', 0)}\n")

    rows = data.get("rows", [])
    if rows:
        headers = list(rows[0].keys())
        click.echo("  ".join(headers[:6]))
        click.echo("-" * 80)
        for row in rows[:10]:
            click.echo("  ".join(str(row.get(h, ""))[:20] for h in headers[:6]))
        if len(rows) > 10:
            click.echo(f"  ... and {len(rows) - 10} more rows")


if __name__ == "__main__":
    cli()
