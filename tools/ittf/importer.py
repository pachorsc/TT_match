"""Transform ITTF scraped data into Laravel JSON import format."""

import json
from pathlib import Path
from typing import Any

from config import IMPORT_DIR


def transform_ranking(row: dict[str, Any]) -> dict[str, Any]:
    """Transform an ITTF ranking row to Laravel ranking import format.

    The Laravel Ranking model expects:
        player_id (internal), ranking, rating_points, ranking_date

    Since the import uses ittf_id to resolve player_id, we preserve
    the ittf_id here for resolution in the PHP import service.
    """
    return {
        "ittf_id": row.get("ittf_id", ""),
        "rank_position": row.get("position", 0),
        "rating_points": row.get("points", 0),
        "name": row.get("name", ""),
        "country": row.get("country", ""),
        "continent": row.get("continent", ""),
        "gender": row.get("gender", "men"),
    }


def transform_player(row: dict[str, Any]) -> dict[str, Any]:
    """Transform an ITTF player profile row to Laravel player import format."""
    return {
        "ittf_id": row.get("ittf_id", ""),
        "name": row.get("name", ""),
        "details": row.get("details", ""),
        "career_stats": row.get("career_stats", ""),
        "ytd_stats": row.get("ytd_stats", ""),
    }


def transform_match(row: dict[str, Any]) -> dict[str, Any]:
    """Transform an ITTF match row to Laravel match import format."""
    import re

    def split_player(text: str) -> tuple[str, str]:
        m = re.match(r'^(.+?)\s*\(([^)]+)\)\s*$', text)
        if m:
            return m.group(1).strip(), m.group(2).strip()
        return text, ""

    player_a_raw = row.get("player_a", "")
    player_b_raw = row.get("player_b", "")
    player_a_name, player_a_country = split_player(player_a_raw)
    player_b_name, player_b_country = split_player(player_b_raw)

    score = row.get("score", "")
    player_a_sets = 0
    player_b_sets = 0
    if " - " in score:
        parts = score.split(" - ", 1)
        player_a_sets = int(parts[0]) if parts[0].isdigit() else 0
        player_b_sets = int(parts[1]) if parts[1].isdigit() else 0

    result = row.get("result", "")
    winner_name = row.get("winner", "")
    if not winner_name:
        if result.upper() == "WON":
            winner_name = player_a_name
        elif result.upper() == "LOST":
            winner_name = player_b_name

    result_dict = {
        "tournament": row.get("tournament", ""),
        "year": row.get("year", ""),
        "player_a": player_a_raw,
        "player_a_name": player_a_name,
        "player_a_country": player_a_country,
        "player_b": player_b_raw,
        "player_b_name": player_b_name,
        "player_b_country": player_b_country,
        "event_type": row.get("event_type", ""),
        "stage": row.get("stage", ""),
        "round": row.get("round", ""),
        "score": score,
        "player_a_sets": player_a_sets,
        "player_b_sets": player_b_sets,
        "detailed_sets": row.get("detailed_sets", ""),
        "result": result,
        "winner": winner_name,
    }

    # Preserve extra fields from the parser (e.g. player_ittf_id, player_rank)
    for key in ("player_ittf_id", "player_rank"):
        if key in row:
            result_dict[key] = row[key]

    return result_dict


def save_import_file(data: dict[str, Any], name: str) -> Path:
    """Save data as JSON in the import directory.

    Args:
        data: Dictionary with 'rows' and metadata.
        name: Base filename (without extension).

    Returns:
        Path to the saved file.
    """
    filepath = IMPORT_DIR / f"{name}.json"
    with open(filepath, "w", encoding="utf-8") as f:
        json.dump(data, f, indent=2, ensure_ascii=False)
    return filepath


def load_import_file(name: str) -> dict[str, Any]:
    """Load a JSON file from the import directory."""
    filepath = IMPORT_DIR / f"{name}.json"
    if not filepath.exists():
        # Try with .json extension
        filepath = IMPORT_DIR / name
    if not filepath.exists():
        raise FileNotFoundError(f"Import file not found: {filepath}")
    with open(filepath, encoding="utf-8") as f:
        return json.load(f)


def list_import_files() -> list[Path]:
    """List all JSON files in the import directory."""
    return sorted(IMPORT_DIR.glob("*.json"))
