# ROADMAP.md — Development Roadmap

## Phase 1: Project Setup

- [x] Initialize Laravel 12 project
- [x] Configure environment variables
- [x] Set up MySQL database connection
- [x] Install and configure Tailwind CSS v4
- [x] Install Heroicons
- [x] Configure Vite for asset compilation
- [x] Set up Laravel Pint for code style enforcement
- [x] Create base layout with dark theme
- [x] Verify development environment is running

## Phase 2: Database Foundation

- [x] Create players migration and model
- [x] Create tournaments migration and model
- [x] Create matches migration and model
- [x] Create match_sets migration and model
- [x] Create rankings migration and model
- [x] ~~Create news migration and model~~ (removed — replaced by YouTube videos)
- [x] Define all relationships in models
- [x] Create factories for all models
- [x] Create seeders with sample data
- [x] Add wtt_id, ittf_id, gender fields to players
- [x] Add ittf_id to matches and tournaments
- [x] Add performance indexes
- [x] Verify database structure with test data

## Phase 3: Core Services

- [x] Create PlayerService for player data retrieval
- [x] Create MatchService for match data and head-to-head logic
- [x] Create RankingService for ranking data
- [x] ~~Create NewsService for news retrieval~~ (removed)
- [x] Create MatchPreviewService to aggregate all preview data
- [x] Create YouTubeService for fetching WTT YouTube videos
- [x] Create IttfImportService for ITTF data import
- [x] Create WttApiClient / WttMatchImportService / WttMatchSyncService for WTT data sync
- [x] Write unit tests for all services

## Phase 4: Blade Components

- [x] Create PlayerHeader component (flag, name, ranking, rating, hand)
- [x] Create PlayerCard component (full player details)
- [x] Create MatchTable component (last 7 matches table)
- [x] Create HeadToHead component (h2h stats and history)
- [x] Create VideoCard component (YouTube video embed card)
- [x] Create MatchHeader component (tournament, date, time)
- [x] Create Badge component (win/loss indicators)
- [x] Create EmptyState component (fallback messages)
- [x] Create MatchRow component (single match row)
- [x] Create SetBreakdown component (set-by-set score table)
- [x] Create TournamentSection component (tournament block with matches)
- [x] Create ThemeToggle component (dark/light mode)
- [x] Verify component reusability

## Phase 5: Pages

- [x] Create Home page with stats bar and CTA
- [x] Create MatchPreviewController and preview view
- [x] Create MatchDetailController and detail view with set breakdown
- [x] Create CompareController and comparison view
- [x] Create VideoController and videos view
- [x] Assemble all components in the preview view
- [x] Implement header section with player matchup
- [x] Implement player information cards
- [x] Implement last 7 matches tables
- [x] Implement head to head section
- [x] Implement YouTube videos section
- [x] Add routing for all pages
- [x] Custom 404 error page

## Phase 6: Data Acquisition (Python + Artisan Commands)

- [x] Set up Python scraping infrastructure under tools/
- [x] Create ITTF scraper (tools/ittf/)
- [x] Create WTT ranking scraper (tools/wtt_ranking/)
- [x] Create WTT match scraper (tools/wtt_matches/)
- [x] Create Artisan command: import:ittf
- [x] Create Artisan command: wtt:import-matches
- [x] Create Artisan command: wtt:import-ranking
- [x] Create Artisan command: wtt:sync-matches (scheduled hourly)
- [x] Create Artisan command: matches:clean-invalid
- [x] Create Artisan command: players:fix-genders
- [x] Create Artisan command: players:update-birth-years
- [x] Create Artisan command: matches:truncate
- [x] Set up scheduled job configuration (console.php)

## Phase 7: Polish and Optimization

- [x] Verify responsive behavior across screen sizes
- [x] Optimize database queries (N+1 prevention, indexing)
- [x] Add loading states and error handling
- [x] Add player search with autocomplete (vanilla JS)
- [x] Add video loader with spinner/error/empty states
- [x] Verify accessibility (keyboard navigation, screen readers)
- [x] Performance testing and optimization
- [x] Cross-browser testing

## Phase 8: MVP Completion

- [x] Final code review
- [x] Verify all tests pass (42 tests, 114 assertions)
- [x] Verify code style compliance (Pint — 94 files)
- [x] Documentation review and updates
- [x] MVP launch ready
