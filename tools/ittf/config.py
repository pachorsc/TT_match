"""ITTF results portal configuration."""

import os
from pathlib import Path

BASE_URL = "https://results.ittf.link"
LOGIN_URL = f"{BASE_URL}/index.php/login"

RANKING_URLS = {
    "men": f"{BASE_URL}/ittf-rankings/ittf-ranking-men-singles/list/57",
    "women": f"{BASE_URL}/ittf-rankings/ittf-ranking-women-singles/list/58",
}

PLAYER_PROFILE_URL = f"{BASE_URL}/player-profile/list/60"
PLAYER_MATCHES_URL = f"{BASE_URL}/player-matches/list/31"

ROWS_PER_PAGE = 25

PROJECT_ROOT = Path(__file__).resolve().parent.parent.parent
IMPORT_DIR = PROJECT_ROOT / "storage" / "app" / "import" / "ittf"
SESSION_PATH = PROJECT_ROOT / "storage" / "app" / "ittf_session.json"

# Fabrik list IDs
LIST_RANKING_MS = 57
LIST_RANKING_WS = 58
LIST_PLAYER_PROFILE = 60
LIST_PLAYER_MATCHES = 31

IMPORT_DIR.mkdir(parents=True, exist_ok=True)
