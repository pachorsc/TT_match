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
    return {
        "player_a": row.get("player_a", ""),
        "player_b": row.get("player_b", ""),
        "tournament": row.get("tournament", ""),
        "event_type": row.get("event_type", ""),
        "stage": row.get("stage", ""),
        "round": row.get("round", ""),
        "score": row.get("score", ""),
        "result": row.get("result", ""),
    }


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
