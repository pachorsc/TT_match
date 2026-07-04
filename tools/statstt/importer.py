"""Import StatsTT data into Laravel database."""

import json
import re
from pathlib import Path
from typing import Any

from config import IMPORT_DIR


def parse_player_name(full_name: str) -> tuple[str, str]:
    """Parse StatsTT full name into first_name and last_name.

    StatsTT format: 'SURNAME GivenName' (surname in ALL CAPS)
    Example: 'FAN Zhendong' -> first_name='Zhendong', last_name='Fan'
    """
    if not full_name:
        return "", ""

    parts = full_name.strip().split()
    if len(parts) == 1:
        return "", parts[0]

    surname_parts = []
    given_parts = []

    for i, part in enumerate(parts):
        if part.isupper() and not given_parts:
            surname_parts.append(part)
        else:
            given_parts = parts[i:]
            break

    if not given_parts:
        return full_name, ""

    first_name = " ".join(given_parts)
    last_name = " ".join(surname_parts).title()

    return first_name, last_name


def map_country_code(country_code: str) -> str:
    """Convert 3-letter country code to 2-letter (ISO 3166-1 alpha-2)."""
    mapping = {
        "CHN": "CN", "JPN": "JP", "KOR": "KR", "GER": "DE", "SWE": "SE",
        "FRA": "FR", "GBR": "GB", "ITA": "IT", "BRA": "BR", "USA": "US",
        "TPE": "TW", "HKG": "HK", "SIN": "SG", "IND": "IN", "RUS": "RU",
        "POL": "PL", "CZE": "CZ", "AUT": "AT", "NED": "NL", "BEL": "BE",
        "ESP": "ES", "POR": "PT", "CRO": "HR", "SRB": "RS", "ROU": "RO",
        "BUL": "BG", "HUN": "HU", "GRE": "GR", "TUR": "TR", "EGY": "EG",
        "NGR": "NG", "ARG": "AR", "CHI": "CL", "COL": "CO", "MEX": "MX",
        "AUS": "AU", "NZL": "NZ", "CAN": "CA", "UKR": "UA", "BLR": "BY",
        "KAZ": "KZ", "UZB": "UZ", "IRI": "IR", "IRQ": "IQ", "LBN": "LB",
        "THA": "TH", "VIE": "VN", "MAS": "MY", "INA": "ID", "PHI": "PH",
    }
    return mapping.get(country_code, country_code[:2] if country_code else "")


def map_playing_hand(hand: str | None) -> str:
    """Map StatsTT hand notation to Laravel enum."""
    if not hand:
        return "Right"
    hand = hand.strip().upper()
    if hand in ("L", "LEFT"):
        return "Left"
    return "Right"


def map_playing_style(style: str | None) -> str | None:
    """Map StatsTT style to Laravel enum."""
    if not style:
        return None
    style = style.strip().lower()
    if "off" in style:
        return "Offensive"
    if "def" in style:
        return "Defensive"
    if "all" in style:
        return "All-round"
    return None


def parse_score(result_str: str) -> tuple[int, int]:
    """Parse result string like '3-1' or '3:1' into (player_a_sets, player_b_sets)."""
    if not result_str:
        return 0, 0
    match = re.match(r"(\d+)\s*[-:]\s*(\d+)", str(result_str))
    if match:
        return int(match.group(1)), int(match.group(2))
    return 0, 0


def transform_player(row: dict[str, Any]) -> dict[str, Any]:
    """Transform a StatsTT player row to Laravel player fields."""
    full_name = row.get("name", "")
    first_name, last_name = parse_player_name(full_name)

    country_code_raw = row.get("association_code", "")
    country_code = map_country_code(country_code_raw)

    return {
        "statstt_id": str(row.get("player_id", "")),
        "first_name": first_name,
        "last_name": last_name,
        "country": row.get("association", ""),
        "country_code": country_code,
        "dominant_hand": map_playing_hand(row.get("hand")),
        "playing_style": map_playing_style(row.get("style")),
        "height_cm": row.get("height"),
        "date_of_birth": row.get("birth_date"),
    }


def transform_match(row: dict[str, Any]) -> dict[str, Any]:
    """Transform a StatsTT match row to Laravel match fields."""
    result_str = row.get("result", "")
    player_a_sets, player_b_sets = parse_score(result_str)

    winner_id = row.get("winner_s_id") or row.get("winner_d_id")

    return {
        "statstt_id": str(row.get("match_id", "")),
        "statstt_tournament_id": row.get("event_id"),
        "player_a_id": row.get("player_a_id"),
        "player_b_id": row.get("player_b_id"),
        "winner_id": winner_id,
        "player_a_sets": player_a_sets,
        "player_b_sets": player_b_sets,
        "match_date": row.get("date"),
        "round": row.get("round", ""),
        "status": "Completed",
    }


def transform_tournament(row: dict[str, Any]) -> dict[str, Any]:
    """Transform a StatsTT event row to Laravel tournament fields."""
    return {
        "statstt_id": str(row.get("event_id", "")),
        "name": row.get("event_name", ""),
        "location": row.get("location", ""),
        "country": "",
        "country_code": "",
        "start_date": row.get("start_date"),
        "end_date": row.get("end_date"),
        "category": row.get("level"),
    }


def transform_ranking(row: dict[str, Any]) -> dict[str, Any]:
    """Transform a StatsTT ranking row to Laravel ranking fields."""
    return {
        "player_id": row.get("player_id"),
        "ranking": row.get("rank_position"),
        "rating_points": row.get("rating", 0),
        "ranking_date": row.get("last_match_date"),
    }


def load_import_file(name: str) -> dict[str, Any]:
    """Load a JSON file from the import directory."""
    filepath = IMPORT_DIR / f"{name}.json"
    if not filepath.exists():
        raise FileNotFoundError(f"Import file not found: {filepath}")
    with open(filepath) as f:
        return json.load(f)


def list_import_files() -> list[Path]:
    """List all JSON files in the import directory."""
    return sorted(IMPORT_DIR.glob("*.json"))
