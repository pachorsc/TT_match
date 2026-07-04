"""StatsTT configuration."""

import os
from pathlib import Path

# API
API_BASE_URL = os.environ.get(
    "STATSTT_API_URL", "https://tts-production-78d0.up.railway.app"
)
DAILY_LIMIT = int(os.environ.get("STATSTT_DAILY_LIMIT", "10"))
MAX_ROWS = int(os.environ.get("STATSTT_MAX_ROWS", "20"))

# Clerk
CLERK_PUBLISHABLE_KEY = "pk_live_Y2xlcmsuc3RhdHN0dC5jb20k"
STATSTT_URL = "https://www.statstt.com"

# Paths
PROJECT_ROOT = Path(__file__).resolve().parent.parent.parent
IMPORT_DIR = PROJECT_ROOT / "storage" / "app" / "import" / "statstt"
TOKEN_PATH = PROJECT_ROOT / "storage" / "app" / "statstt_token.json"

# Queries — based on actual StatsTT schema
QUERIES = {
    "rankings_male": "SELECT rank_position, player_id, name_code, rating, last_match_date, last_rating_change FROM player_rankings_male_active ORDER BY rank_position LIMIT {limit}",
    "rankings_female": "SELECT rank_position, player_id, name_code, rating, last_match_date, last_rating_change FROM player_rankings_female_active ORDER BY rank_position LIMIT {limit}",
    "player_by_id": "SELECT * FROM players_basic WHERE player_id = {id}",
    "players_by_ids": "SELECT * FROM players_basic WHERE player_id IN ({ids})",
    "matches_by_player": "SELECT * FROM matches_basic WHERE player_a_id = {player_id} OR player_b_id = {player_id} ORDER BY date DESC LIMIT {limit}",
    "matches_by_tournament": "SELECT * FROM matches_basic WHERE event_id = {event_id} ORDER BY date DESC LIMIT {limit}",
    "events_by_ids": "SELECT * FROM events_basic WHERE event_id IN ({ids})",
    "games_by_match": "SELECT * FROM games_basic WHERE match_id = {match_id} ORDER BY game_number LIMIT {limit}",
}

# Ensure import directory exists
IMPORT_DIR.mkdir(parents=True, exist_ok=True)
