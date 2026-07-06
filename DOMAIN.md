# DOMAIN.md — Domain Model and Business Rules

## Entities

### Player

Represents a table tennis player.

| Field | Type | Description |
|---|---|---|
| id | bigint | Primary key |
| wtt_id | string | World Table Tennis ID (nullable) |
| ittf_id | string | ITTF ID (nullable) |
| first_name | string | Player first name |
| last_name | string | Player last name |
| gender | string | M or F |
| country | string | Country of origin |
| country_code | string | ISO country code (for flag display) |
| date_of_birth | date | Player date of birth (nullable) |
| height_cm | integer | Height in centimeters (nullable) |
| dominant_hand | string | Left or Right (nullable) |
| playing_style | string | Offensive, Defensive, All-round (nullable) |
| world_ranking | integer | Current ITTF world ranking (nullable) |
| rating_points | integer | Current rating points (nullable) |
| created_at | timestamp | Record creation time |
| updated_at | timestamp | Record last update time |

**Computed attributes:**
- `full_name` — accessor that concatenates first_name and last_name

**Rules:**
- A player must have a first name and last name
- A player must have a country and country code
- Gender must be M or F
- World ranking and rating points may be null if not yet ranked
- Playing style, dominant hand, height, date of birth may be null if not available

---

### Tournament

Represents a table tennis tournament or competition.

| Field | Type | Description |
|---|---|---|
| id | bigint | Primary key |
| ittf_id | string | ITTF tournament ID (nullable) |
| name | string | Tournament name |
| location | string | Host city or venue |
| country | string | Host country |
| country_code | string | ISO country code |
| start_date | date | Tournament start date |
| end_date | date | Tournament end date |
| category | string | Tournament tier/category (nullable) |
| created_at | timestamp | Record creation time |
| updated_at | timestamp | Record last update time |

**Rules:**
- A tournament must have a name, location, and country
- Start date must be on or before end date
- Tournament category is optional (e.g., Grand Slam, World Tour, etc.)

---

### Match

Represents a table tennis match between two players.

| Field | Type | Description |
|---|---|---|
| id | bigint | Primary key |
| ittf_id | string | ITTF match ID (nullable) |
| tournament_id | bigint | Foreign key to tournaments |
| player_a_id | bigint | Foreign key to players (Player A) |
| player_b_id | bigint | Foreign key to players (Player B) |
| winner_id | bigint | Foreign key to players (nullable, null if not yet played) |
| player_a_sets | integer | Sets won by Player A |
| player_b_sets | integer | Sets won by Player B |
| match_date | date | Date the match was played |
| match_time | time | Time the match started (nullable) |
| round | string | Tournament round (e.g., Quarterfinal, Semifinal, Final) |
| status | string | Scheduled, Completed, Walkover, Cancelled |
| created_at | timestamp | Record creation time |
| updated_at | timestamp | Record last update time |

**Rules:**
- A match must have exactly two players
- Player A and Player B must be different players
- winner_id must be one of the two players or null
- player_a_sets and player_b_sets must be non-negative integers
- A match belongs to a tournament
- status tracks whether the match is scheduled, completed, or cancelled

**Relationships:**
- Belongs to a Tournament
- Belongs to Player A (Player)
- Belongs to Player B (Player)
- Belongs to Winner (Player, nullable)

---

### MatchSet

Represents an individual set within a match.

| Field | Type | Description |
|---|---|---|
| id | bigint | Primary key |
| match_id | bigint | Foreign key to matches |
| set_number | integer | Set number (1, 2, 3, etc.) |
| player_a_points | integer | Points scored by Player A |
| player_b_points | integer | Points scored by Player B |

**Rules:**
- Each set belongs to a match
- set_number must be sequential starting from 1
- Points must be non-negative integers
- A set is won by the player with more points (standard table tennis rules)

---

### Ranking

Represents a player's ranking at a specific point in time.

| Field | Type | Description |
|---|---|---|
| id | bigint | Primary key |
| player_id | bigint | Foreign key to players |
| ranking | integer | World ranking position |
| rating_points | integer | Rating points at this ranking |
| ranking_date | date | Date this ranking was recorded |
| created_at | timestamp | Record creation time |

**Rules:**
- A ranking belongs to a player
- ranking_date is used to track ranking history
- Multiple rankings can exist for the same player at different dates
- ranking and rating_points must be positive integers

---

## Entity Relationships

```
Player 1──* Match (as Player A)
Player 1──* Match (as Player B)
Player 1──* Match (as Winner)
Tournament 1──* Match
Match 1──* MatchSet
Player 1──* Ranking
```

## Business Rules Summary

1. **Match Preview Generation:** A match preview requires two players, a tournament, and match details
2. **Head to Head:** Only shows matches from the last two years between the same two players
3. **Last 7 Matches:** Shows the most recent 7 completed matches for each player
4. **Ranking History:** Rankings are tracked over time to show player progression
5. **YouTube Videos:** Videos from the WTT official channel are fetched per player via the YouTube API and displayed as embed cards
6. **Data Completeness:** Some fields are nullable (playing style, height, match time) to accommodate incomplete data

## Terminology

| Term | Definition |
|---|---|---|
| Player A | The first player listed in a match matchup |
| Player B | The second player listed in a match matchup |
| Sets | Best-of format matches (typically best of 5 or 7) |
| Rating Points | ITTF rating system points |
| World Ranking | Official ITTF world ranking position |
| Head to Head | Historical match record between two specific players |
| Match Preview | The pre-match informational display |
| Video | YouTube video from the WTT official channel embedded per player |
