# Table Tennis Match Preview

A desktop-first web application that displays all relevant factual information about two table tennis players before a match. Built for coaches, analysts, commentators, and table tennis enthusiasts.

## Features

- **Home Page** — Stats overview with match, player, and tournament counts
- **Match Preview** — Full pre-match view with player matchup, stats, last 7 matches, head-to-head, and YouTube videos
- **Match Detail** — Final result with set-by-set breakdown
- **Player Comparison** — Side-by-side comparison with gender filter, search, H2H, and ranking history
- **YouTube Videos** — Search and browse WTT official channel videos per player
- Data imported from ITTF and WTT APIs via automated pipelines

## Purpose

This application presents match preview data in a clean, modern, sports-analytics aesthetic. It displays player profiles, recent match history, head-to-head records, and YouTube videos from the WTT official channel — all factual information with no predictions or betting content.

## Technologies

| Layer | Technology |
|---|---|
| Backend | PHP 8.4+, Laravel 12 |
| Frontend | Laravel Blade, Tailwind CSS v4 |
| Database | MySQL |
| Icons | Heroicons |
| Scripts | Python (scraping, ETL, automation) |

## Folder Structure

```
TT_match/
├── .agents/                 # AI agent skills
├── app/
│   ├── Console/              # Artisan commands
│   ├── Exceptions/           # Custom exceptions
│   ├── Http/
│   │   ├── Controllers/      # Thin controllers
│   │   ├── Middleware/        # HTTP middleware
│   │   └── Requests/         # Form Requests
│   ├── Models/               # Eloquent models
│   └── Services/             # Business logic
├── database/
│   ├── factories/            # Model factories
│   ├── migrations/           # Database migrations
│   └── seeders/              # Database seeders
├── linkedin-posts/           # LinkedIn post scripts
├── resources/
│   ├── css/                  # Stylesheets
│   ├── js/                   # JavaScript
│   └── views/                # Blade templates
│       ├── components/       # Reusable Blade components
│       └── pages/            # Page templates
├── routes/                   # Route definitions
├── tests/                    # PHPUnit tests
├── tools/                    # Python scripts (scraping, ETL)
│   ├── ittf/                 # ITTF data scraping and import
│   ├── wtt_ranking/          # WTT ranking scraper
│   └── wtt_matches/          # WTT match scraper
├── AGENTS.md                 # AI agent instructions
├── CODING_STANDARDS.md       # Coding standards
├── DOMAIN.md                 # Domain model
├── PROJECT.md                # Project overview
├── README.md                 # This file
├── ROADMAP.md                # Development roadmap
├── SCHEDULE.md               # Cron job configuration
├── TECH_STACK.md             # Technology stack
└── UI_GUIDELINES.md          # UI design guidelines
```

## Installation

### Prerequisites

- PHP 8.4+
- Composer 2.x
- MySQL 8.x+
- Node.js 18+
- npm 9+

### Setup

1. Clone the repository:
   ```bash
   git clone <repository-url>
   cd tt-match-preview
   ```

2. Install PHP dependencies:
   ```bash
   composer install
   ```

3. Install Node.js dependencies:
   ```bash
   npm install
   ```

4. Create environment file:
   ```bash
   cp .env.example .env
   ```

5. Generate application key:
   ```bash
   php artisan key:generate
   ```

6. Configure database and YouTube API key in `.env`:
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=tt_match_preview
   DB_USERNAME=root
   DB_PASSWORD=

   YOUTUBE_API_KEY=your_youtube_api_key_here
   ```

7. Run migrations and seeders:
   ```bash
   php artisan migrate --seed
   ```

8. Build frontend assets:
   ```bash
   npm run build
   ```

9. Start the development server:
   ```bash
   php artisan serve
   ```

## Development Workflow

1. Read all project documentation before making changes
2. Create a feature branch from main
3. Follow PSR-12 coding standards
4. Keep controllers thin, use services for business logic
5. Use Blade Components for reusable UI elements
6. Write tests for service classes
7. Run `php artisan test` to verify tests pass
8. Run `./vendor/bin/pint --test` to verify code style
9. Run `npm run build` to verify frontend compiles
10. Submit a pull request for review

## License

This project is proprietary software.
