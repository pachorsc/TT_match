#!/usr/bin/env python3
"""WTT Ranking Scraper — Fetch top 100 rankings from World Table Tennis.

Usage:
    python scraper.py                    # Default: top 100 men's singles
    python scraper.py --gender women     # Top 100 women's singles
    python scraper.py --limit 50         # Top 50 men's singles
    python scraper.py --output ranking.json  # Custom output file
"""

import json
import sys
import time
from pathlib import Path

import requests

# Output directory (Laravel import directory)
PROJECT_ROOT = Path(__file__).resolve().parent.parent.parent
OUTPUT_DIR = PROJECT_ROOT / "storage" / "app" / "import" / "wtt"
OUTPUT_DIR.mkdir(parents=True, exist_ok=True)

# WTT API Gateway endpoint (no brotli compression needed)
API_BASE = "https://wttcmsapigateway-new.azure-api.net/internalttu/RankingsCurrentWeek/CurrentWeek/GetRankingIndividuals"

# API keys (public, found in WTT website source code)
API_KEYS = {
    "apikey": "REPLACED_WITH_ENV_VAR",
    "secapimkey": "REPLACED_WITH_ENV_VAR",
}

# Country code mapping (3-letter → 2-letter ISO)
COUNTRY_MAP = {
    "CHN": ("CN", "China"), "JPN": ("JP", "Japan"), "KOR": ("KR", "South Korea"),
    "GER": ("DE", "Germany"), "SWE": ("SE", "Sweden"), "FRA": ("FR", "France"),
    "GBR": ("GB", "Great Britain"), "ITA": ("IT", "Italy"), "BRA": ("BR", "Brazil"),
    "USA": ("US", "United States"), "TPE": ("TW", "Chinese Taipei"),
    "HKG": ("HK", "Hong Kong"), "SIN": ("SG", "Singapore"), "IND": ("IN", "India"),
    "RUS": ("RU", "Russia"), "POL": ("PL", "Poland"), "CZE": ("CZ", "Czech Republic"),
    "AUT": ("AT", "Austria"), "NED": ("NL", "Netherlands"), "BEL": ("BE", "Belgium"),
    "ESP": ("ES", "Spain"), "POR": ("PT", "Portugal"), "CRO": ("HR", "Croatia"),
    "SRB": ("RS", "Serbia"), "ROU": ("RO", "Romania"), "BUL": ("BG", "Bulgaria"),
    "HUN": ("HU", "Hungary"), "GRE": ("GR", "Greece"), "TUR": ("TR", "Turkey"),
    "EGY": ("EG", "Egypt"), "NGR": ("NG", "Nigeria"), "ARG": ("AR", "Argentina"),
    "CHI": ("CL", "Chile"), "COL": ("CO", "Colombia"), "MEX": ("MX", "Mexico"),
    "AUS": ("AU", "Australia"), "NZL": ("NZ", "New Zealand"), "CAN": ("CA", "Canada"),
    "UKR": ("UA", "Ukraine"), "BLR": ("BY", "Belarus"), "KAZ": ("KZ", "Kazakhstan"),
    "UZB": ("UZ", "Uzbekistan"), "IRI": ("IR", "Iran"), "IRQ": ("IQ", "Iraq"),
    "LBN": ("LB", "Lebanon"), "THA": ("TH", "Thailand"), "VIE": ("VN", "Vietnam"),
    "MAS": ("MY", "Malaysia"), "INA": ("ID", "Indonesia"), "PHI": ("PH", "Philippines"),
    "PUR": ("PR", "Puerto Rico"), "DOM": ("DO", "Dominican Republic"),
    "CUB": ("CU", "Cuba"), "PRK": ("KP", "North Korea"), "LUX": ("LU", "Luxembourg"),
    "SUI": ("CH", "Switzerland"), "MRI": ("MU", "Mauritius"), "SEN": ("SN", "Senegal"),
    "ALG": ("DZ", "Algeria"), "TUN": ("TN", "Tunisia"), "MAR": ("MA", "Morocco"),
    "CMR": ("CM", "Cameroon"), "GHA": ("GH", "Ghana"), "RSA": ("ZA", "South Africa"),
    "IRL": ("IE", "Ireland"), "NOR": ("NO", "Norway"), "DEN": ("DK", "Denmark"),
    "FIN": ("FI", "Finland"), "SVK": ("SK", "Slovakia"), "SLO": ("SI", "Slovenia"),
    "LTU": ("LT", "Lithuania"), "LAT": ("LV", "Latvia"), "EST": ("EE", "Estonia"),
}

HEADERS = {
    "Accept": "application/json, text/plain, */*",
    "Origin": "https://www.worldtabletennis.com",
    "Referer": "https://www.worldtabletennis.com/",
    "Accept-Language": "en-US,en;q=0.9",
}


def parse_name(full_name: str) -> tuple[str, str]:
    """Parse WTT name format: 'SURNAME Given Name' -> (first_name, last_name)."""
    parts = full_name.strip().split()
    if len(parts) <= 1:
        return full_name, ""

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


def resolve_country(association: str) -> tuple[str, str]:
    """Resolve 3-letter country code to (2-letter, name)."""
    if association in COUNTRY_MAP:
        return COUNTRY_MAP[association]
    return association[:2] if association else "", association


def fetch_rankings(gender: str = "men", limit: int = 100) -> list[dict]:
    """Fetch rankings from WTT API Gateway."""
    sub_event = "MS" if gender == "men" else "WS"
    all_results = []
    batch_size = 100
    start_rank = 1

    headers = {**HEADERS, **API_KEYS}

    while start_rank <= limit:
        end_rank = min(start_rank + batch_size - 1, limit)

        url = f"{API_BASE}?CategoryCode=SEN&SubEventCode={sub_event}&StartRank={start_rank}&EndRank={end_rank}&q=1"

        print(f"Fetching ranks {start_rank}-{end_rank}...")

        response = requests.get(url, headers=headers, timeout=30)
        response.raise_for_status()

        data = response.json()

        if "Result" not in data:
            raise ValueError("Invalid API response: missing Result key")

        results = data["Result"]

        if not results:
            break

        all_results.extend(results)

        if len(all_results) >= limit:
            break

        start_rank += batch_size

        # Small delay to avoid rate limiting
        time.sleep(0.5)

    return all_results[:limit]


def transform_entry(entry: dict, gender: str = "men") -> dict:
    """Transform a WTT API entry to our import format."""
    wtt_id = str(entry.get("IttfId", ""))
    player_name = entry.get("PlayerName", "")
    association = entry.get("AssociationCountryCode", entry.get("CountryCode", ""))
    rank_position = entry.get("RankingPosition", 0)
    rank_points = entry.get("RankingPointsYTD", 0)

    first_name, last_name = parse_name(player_name)
    country_code, country_name = resolve_country(association)

    return {
        "wtt_id": wtt_id,
        "first_name": first_name,
        "last_name": last_name,
        "gender": "M" if gender == "men" else "F",
        "country": country_name,
        "country_code": country_code,
        "world_ranking": rank_position,
        "rating_points": rank_points,
        "dominant_hand": "Right",
    }


def save_output(players: list[dict], output_file: str) -> Path:
    """Save transformed players to JSON."""
    filepath = OUTPUT_DIR / output_file
    output = {
        "source": "worldtabletennis.com",
        "fetched_at": time.strftime("%Y-%m-%dT%H:%M:%S"),
        "count": len(players),
        "rows": players,
    }
    with open(filepath, "w") as f:
        json.dump(output, f, indent=2)
    return filepath


def main():
    import argparse

    parser = argparse.ArgumentParser(description="WTT Ranking Scraper")
    parser.add_argument("--gender", choices=["men", "women"], default="men")
    parser.add_argument("--limit", type=int, default=100)
    parser.add_argument("--output", type=str, default=None)
    args = parser.parse_args()

    output_file = args.output or f"top{args.limit}_{args.gender}_singles.json"

    print(f"Scraping top {args.limit} {args.gender} singles rankings from WTT...\n")

    try:
        entries = fetch_rankings(args.gender, args.limit)
        print(f"Fetched {len(entries)} entries from API\n")

        players = [transform_entry(e, args.gender) for e in entries]
        filepath = save_output(players, output_file)

        print(f"Saved {len(players)} players to: {filepath}")
        print(f"\nTo import into Laravel, run:")
        print(f"  php artisan wtt:import-ranking")

    except requests.exceptions.RequestException as e:
        print(f"Network error: {e}", file=sys.stderr)
        sys.exit(1)
    except ValueError as e:
        print(f"Data error: {e}", file=sys.stderr)
        sys.exit(1)


if __name__ == "__main__":
    main()
