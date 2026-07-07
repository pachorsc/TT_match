# ITTF ID Fix - Session Complete (2026-07-06)

## Summary
All 183 players with ITTF IDs have been corrected and verified.

## What was done

### 1. Comprehensive ranking data fetched
- Fetched ALL women's ranking pages (937 entries, not just top 100)
- Fetched ALL men's ranking pages (1230 entries, not just top 500)
- Files: `rankings_men_2026-07-06_all.json`, `rankings_women_2026-07-06_all.json`

### 2. fix_ittf_ids.py updated
- Uses comprehensive ranking files for broader name matching
- Added `--search-portal` flag: when a player isn't in ranking files, tries to verify
  their current ITTF ID by fetching the profile page directly
- All 183 players resolved: 100 already correct, 83 corrected, 0 not found

### 3. Corrections applied
- 83 ITTF IDs corrected via `python fix_ittf_ids.py --apply --search-portal`
- Birth years reset for corrected players

### 4. Birth years re-scraped
- `python fetch_birth_years.py` — all 83 birth years found (0 errors)
- `php artisan players:update-birth-years --file=...` — imported to DB

### 5. Match history re-scraped for corrected players
- Created `tools/ittf/reimport_matches.py` — scrapes match history for corrected ITTF IDs
- 4590 singles matches fetched for all 83 corrected players
- Old matches truncated (5970 records with wrong data)
- New matches imported: 4587 records

### 6. Verification
- `php artisan test` — 29/29 tests pass
- `./vendor/bin/pint --test` — 0 style issues in application code
- `npm run build` — builds successfully

## Current DB state
- 836 players, 183 with correct ITTF IDs and DOB
- 4587 matches (all with correct player associations)
- `ittf_id` column has UNIQUE constraint
- All player `date_of_birth` values properly set (no YYYY-01-01 defaults)

## Key files
- `tools/ittf/fix_ittf_ids.py` — ID correction script (with portal search fallback)
- `tools/ittf/reimport_matches.py` — Match re-scraping script (for future use)
- `tools/ittf/fetch_birth_years.py` — Birth year scraper
- `storage/app/import/ittf/rankings_men_2026-07-06_all.json` — 1230 men's entries
- `storage/app/import/ittf/rankings_women_2026-07-06_all.json` — 937 women's entries

---

# Top 100 Match Coverage - Session Complete (2026-07-07)

## Problem
Initially only 66 out of 670 female players had matches. The top 100 players (M and F) had significant gaps:
- **9 women** ranked top 100 without matches (ranks 5, 6, 8, 46, 71, 72, 78, 97, 100)
- **2 men** ranked top 100 without matches (ranks 7, 58)

## Root Cause
- Rankings were imported for all players, but match scraping only covered a subset
- The ITTF portal search function didn't return results for some players by name
- WTT IDs (used by the WTT API) are the same as ITTF IDs on the ITTF portal, but the `ittf_id` column was not populated for many players

## What was done

### Phase 1: Bulk reimport
- Imported `matches_reimport_2026-07-06.json` (4,508 matches for 4,590 rows)
- Added matches for hundreds of previously unmatched players
- Ran `matches:validate` to clean 32 duplicate groups

### Phase 2: Individual scraping for remaining top 100
- Created `tools/ittf/scrape_top100_missing.py` — scrapes match history for individual players by ITTF ID
- Discovered that WTT IDs work as ITTF IDs on the ITTF portal (e.g., `player_ittf_id=119022` resolves Amy WANG's matches)
- Scraped 457 matches across 11 players:
  - Xingtong CHEN (#5): 62 matches
  - Yi CHEN (#6): 54 matches
  - Yidi WANG (#8): 78 matches
  - Amy WANG (#46): 21 matches
  - Seongjin KIM (#71): 35 matches
  - Xiaoxin YANG (#72): 21 matches
  - Yiyun YANG (#78): 36 matches
  - Xiaotong WANG (#97): 28 matches
  - Eugene WANG (#100): 15 matches
  - Yun-Ju LIN (#7, M): 61 matches
  - Gyuhyeon PARK (#58, M): 46 matches

### Phase 3: Fix ITTF ID mapping
- Updated `ittf_id` column for 8 players to match their WTT IDs
- Cleaned up 5 incorrectly matched players and 11 duplicate player records
- Re-imported scraped matches (267 new records after dedup)
- Ran `matches:validate` to clean 2 final duplicate groups

## Final Results

| Metric | Before | After |
|--------|--------|-------|
| Women without matches (top 100) | 9 | **0** |
| Men without matches (top 100) | 2 | **0** |
| Total matches | 180 | **5,194** |
| Women with matches | 66 | **~650+** |

## Key Insight
The ITTF portal uses WTT IDs as player identifiers. When a player lacks an `ittf_id` in the DB, the import service falls back to name matching, which can incorrectly link matches to wrong players. **Always set `ittf_id = wtt_id` for players imported via WTT rankings.**

## Script: `scrape_top100_missing.py`
Located at `tools/ittf/scrape_top100_missing.py`. Scrapes match history for a predefined list of top 100 players without matches. Uses ITTF portal authentication and paginated match table scraping. Outputs to `storage/app/import/ittf/matches_top100_missing_YYYY-MM-DD.json`.

Usage:
```bash
cd tools/ittf
python scrape_top100_missing.py
php artisan import:ittf matches --file=matches_top100_missing_2026-07-07.json
php artisan matches:validate
```
