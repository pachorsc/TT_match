# StatsTT Import Skill

## Overview

StatsTT is a table tennis data platform with a SQL playground interface. This skill covers importing data from StatsTT into the Laravel TT Match application.

## API Details

- **Base URL:** `https://tts-production-78d0.up.railway.app`
- **Auth:** Clerk JWT tokens (`Authorization: Bearer <token>`)
- **Plan Free:** 10 queries/day, 20 rows max per query
- **Plan Pro:** 500 queries/day, 200 rows max per query

## Endpoints

| Method | Endpoint | Auth | Purpose |
|--------|----------|------|---------|
| POST | `/api/query` | Yes | Execute SQL query |
| GET | `/api/schema` | No | Get database schema |
| GET | `/api/data-status` | No | Get data freshness |
| GET | `/api/auth/me` | Yes | Get user quota info |

## Request/Response Format

**Query request:**
```json
POST /api/query
Authorization: Bearer <token>
Content-Type: application/json

{"sql": "SELECT * FROM players_basic LIMIT 10"}
```

**Query response:**
```json
{
  "columns": ["player_id", "name", "association"],
  "rows": [[249, "WANG Chuqin", "CHN"], [253, "LEBRUN Felix", "FRA"]],
  "row_count": 2,
  "execution_time_ms": 12.34
}
```

**Error response (429):**
```json
{
  "detail": {
    "code": "QUOTA_EXCEEDED",
    "daily_limit": 10
  }
}
```

## Available Tables (Free Plan)

### players_basic
| Column | Type | Description |
|--------|------|-------------|
| `player_id` | int | Primary key |
| `player_id_ittf` | int | ITTF ID |
| `name` | string | Full name (SURNAME Given) |
| `name_code` | string | Name with country |
| `name_local` | string | Name in local script |
| `association` | string | Country name |
| `association_code` | string | 3-letter country code |
| `gender` | string | Male/Female |
| `birth_year` | int | Birth year |
| `birth_date` | date | Birth date |
| `height` | int | Height in cm |
| `weight` | int | Weight in kg |
| `hand` | string | Right/Left |
| `style` | string | Attack/Defense/All-round |
| `grip` | string | ShakeHand/PenHold |
| `is_active` | bool | Active status |

### matches_basic
| Column | Type | Description |
|--------|------|-------------|
| `match_id` | int | Primary key |
| `match_year` | int | Year |
| `event_id` | int | Tournament ID |
| `event_name` | string | Tournament name |
| `gender` | string | Male/Female |
| `match_type` | string | Singles/Doubles |
| `stage` | string | Stage |
| `round` | string | Round |
| `date` | date | Match date |
| `location` | string | Location |
| `player_a` | string | Player A name |
| `player_b` | string | Player B name |
| `result` | string | Score (3-1, 3-0, etc.) |
| `player_a_id` | int | Player A ID |
| `player_b_id` | int | Player B ID |
| `winner_s_id` | int | Winner ID (singles) |
| `winner_d_id` | int | Winner ID (doubles) |
| `score_ab` | string | Set scores |
| `score_xy` | string | Set scores |

### events_basic
| Column | Type | Description |
|--------|------|-------------|
| `event_id` | int | Primary key |
| `event_name` | string | Tournament name |
| `event_year` | int | Year |
| `level` | string | Category level |
| `start_date` | date | Start date |
| `end_date` | date | End date |
| `location` | string | Location |
| `status` | string | Status |

### player_rankings_male_active / player_rankings_female_active
| Column | Type | Description |
|--------|------|-------------|
| `rank_position` | int | World ranking |
| `player_id` | int | Player ID |
| `name_code` | string | Name with country |
| `rating` | float | Rating points |
| `last_match_date` | date | Last match |
| `last_rating_change` | float | Rating change |

### games_basic
| Column | Type | Description |
|--------|------|-------------|
| `match_id` | int | Match ID |
| `game_number` | int | Game number |
| `score_ab` | string | Score |
| `score_xy` | string | Score |
| `game_winner_side` | string | Winner side |
| `is_deciding` | bool | Deciding game |

## Field Mapping: StatsTT → Laravel

### players table
| Laravel Field | StatsTT Source | Notes |
|---------------|----------------|-------|
| first_name | `name` | Parse: first word(s) before surname |
| last_name | `name` | Parse: SURNAME portion (all caps in StatsTT) |
| country | `association` | Direct mapping |
| country_code | `association_code` | 3-letter → 2-letter conversion needed |
| dominant_hand | `hand` | Map: "R" → "Right", "L" → "Left" |
| playing_style | `style` | Map: "Attack" → "Offensive", etc. |
| height_cm | `height` | Direct mapping |
| date_of_birth | `birth_date` | Date format |
| statstt_id | `player_id` | StatsTT player ID |

### matches table
| Laravel Field | StatsTT Source | Notes |
|---------------|----------------|-------|
| player_a_id | `player_a_id` | Foreign key to players |
| player_b_id | `player_b_id` | Foreign key to players |
| winner_id | `winner_s_id` | Foreign key to players (singles) |
| player_a_sets | `result` | Parse from result string (3-1) |
| player_b_sets | `result` | Parse from result string (3-1) |
| match_date | `date` | Date format |
| round | `round` | Direct mapping |
| tournament_id | `event_id` | Foreign key to tournaments |
| statstt_id | `match_id` | StatsTT match ID |

### tournaments table
| Laravel Field | StatsTT Source | Notes |
|---------------|----------------|-------|
| name | `event_name` | Direct mapping |
| location | `location` | Direct mapping |
| start_date | `start_date` | Date format |
| end_date | `end_date` | Date format |
| category | `level` | Direct mapping |
| statstt_id | `event_id` | StatsTT event ID |

### rankings table
| Laravel Field | StatsTT Source | Notes |
|---------------|----------------|-------|
| player_id | `player_id` | Foreign key to players |
| ranking | `rank_position` | World ranking |
| rating_points | `rating` | Rating points |
| ranking_date | `last_match_date` | Date of snapshot |

## Predefined Queries

```sql
-- Male rankings (top N)
SELECT rank_position, player_id, name_code, rating, last_match_date, last_rating_change
FROM player_rankings_male_active
ORDER BY rank_position
LIMIT {limit}

-- Female rankings (top N)
SELECT rank_position, player_id, name_code, rating, last_match_date, last_rating_change
FROM player_rankings_female_active
ORDER BY rank_position
LIMIT {limit}

-- Player by ID
SELECT * FROM players_basic WHERE player_id = {id}

-- Players by IDs
SELECT * FROM players_basic WHERE player_id IN ({ids})

-- Matches by player
SELECT * FROM matches_basic
WHERE player_a_id = {player_id} OR player_b_id = {player_id}
ORDER BY date DESC
LIMIT {limit}

-- Matches by tournament
SELECT * FROM matches_basic
WHERE event_id = {event_id}
ORDER BY date DESC
LIMIT {limit}
```

## Import Workflow

### Step 1: Login (opens browser)
```bash
python tools/statstt/statstt.py login
```

### Step 2: Check status
```bash
python tools/statstt/statstt.py status
```

### Step 3: Import data
```bash
# Import everything (uses ~5 queries)
python tools/statstt/statstt.py import --type all --limit 10

# Or import individually
python tools/statstt/statstt.py import --type rankings --gender male --limit 20
python tools/statstt/statstt.py import --type player --id 249
python tools/statstt/statstt.py import --type matches --player-id 249 --limit 20
```

### Step 4: Import to Laravel
```bash
# Import in order: tournaments → players → rankings → matches
php artisan import:statstt tournaments --file=tournaments_10.json
php artisan import:statstt players --file=players_male_top_10.json
php artisan import:statstt rankings --file=rankings_male_10.json
php artisan import:statstt matches --file=matches_player_249_20.json
```

## Country Code Mapping

StatsTT uses 3-letter codes, Laravel uses 2-letter:

| StatsTT | Laravel | Country |
|---------|---------|---------|
| CHN | CN | China |
| JPN | JP | Japan |
| KOR | KR | South Korea |
| GER | DE | Germany |
| SWE | SE | Sweden |
| FRA | FR | France |
| GBR | GB | Great Britain |
| TPE | TW | Taiwan |
| BRA | BR | Brazil |
| USA | US | USA |
| IND | IN | India |
| POL | PL | Poland |
| ITA | IT | Italy |

## Important Notes

- **Free plan:** 10 queries/day, 20 rows max
- **Token expiry:** Tokens expire after ~24 hours, re-login required
- **Name format:** StatsTT uses "SURNAME Given" (all-caps surname)
- **Import order:** tournaments → players → rankings → matches (foreign keys)
- **Deduplication:** Uses `statstt_id` for upsert (update existing, create new)
