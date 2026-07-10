# IMPROVEMENTS.md — Upcoming Improvements

## Completed Features

### 1. Player Profile Page ✅

Individual player page with complete stats and history.

**Route:** `/players/{player}`

**Sections:**
- Player header: full name, country, flag, ranking, rating, hand, age, height, playing style
- Career stats: total matches, wins, losses, win percentage
- Ranking history: table + trend indicator (up/down/stable)
- Match history: all completed matches (paginated, filterable by year/tournament)
- Current streak: consecutive wins or losses
- Performance by round: win rates in different tournament stages (qualifying, R32, R16, QF, SF, F)

**Implementation:**
- New controller: `PlayerController`
- New service method in `PlayerService`: `getPlayerProfile()`, `getMatchHistory()`, `getRankingHistory()`
- New Blade components: `player-profile-header`, `ranking-chart`, `match-history-table`
- New view: `pages/player-profile.blade.php`
- Route: `Route::get('/players/{player}', PlayerController::class)->name('players.show')`

---

### 2. Rankings Page ✅

World rankings table with sorting and filtering.

**Route:** `/rankings`

**Sections:**
- Gender toggle (Men / Women)
- Current rankings table: position, player name, country, rating points, movement indicator
- Top 50 players per gender
- Click on player → profile page

**Implementation:**
- New controller: `RankingController`
- New service method in `RankingService`: `getCurrentRankings()`, `getRankingMovement()`
- New Blade components: `rankings-table`, `ranking-row`
- New view: `pages/rankings.blade.php`
- Route: `Route::get('/rankings', RankingController::class)->name('rankings')`

---

### 3. Advanced Stats Comparison ✅

Side-by-side statistical comparison with detailed metrics.

**Route:** `/compare/stats?player_a={id}&player_b={id}`

**Sections:**
- Head-to-head record (wins, total matches)
- Win percentage comparison
- Performance by match format (best of 5, best of 7)
- Performance by opponent ranking range
- Recent form comparison (last 10 matches)
- Set differential analysis

**Implementation:**
- Extend `CompareController` or create `StatsController`
- New service: `PlayerStatsService` for advanced metrics
- New Blade components: `stat-comparison-row`, `stat-bar`, `performance-breakdown`
- New view: `pages/compare-stats.blade.php`
- Route: `Route::get('/compare/stats', StatsController::class)->name('compare.stats')`

---

## Database Considerations

- Rankings table already supports historical data via `ranking_date`
- Match history can be derived from existing `matches` table
- No new migrations needed for these features

## Dependencies

- No new packages required
- All features use existing Laravel + Blade + Tailwind stack
- No JavaScript frameworks (vanilla JS or Alpine.js only if needed)

## Estimated Effort

| Feature | Complexity | Files to Create |
|---------|------------|-----------------|
| Player Profile | Medium | 1 controller, 1 service extension, 3 components, 1 view |
| Rankings | Low | 1 controller, 1 service extension, 2 components, 1 view |
| Advanced Stats | Medium | 1 controller, 1 new service, 3 components, 1 view |

---

## Future Improvements

### 1. Player Photos/Avatars

- [ ] Add player photos to profiles
- [ ] Consider using WTT or ITTF official photos

### 2. Real-time Match Tracking

- [ ] Live score updates during matches
- [ ] Push notifications for match starts

### 3. User Accounts

- [ ] User registration and login
- [ ] Favorite players tracking
- [ ] Custom match alerts

### 4. Mobile Optimization

- [ ] PWA support
- [ ] Touch-optimized navigation
- [ ] Offline data caching

---

*Last updated: 2026-07-09*
