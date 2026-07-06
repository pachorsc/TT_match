# Table Tennis Match Preview

A desktop-first web application that displays all relevant factual information about two table tennis players before a match. Built for coaches, analysts, commentators, and table tennis enthusiasts.

## Features

- **Home Page** — Stats overview with match, player, and tournament counts
- **Match Preview** — Full pre-match view with player matchup, stats, last 7 matches, head-to-head, and YouTube videos
- **Match Detail** — Final result with set-by-set breakdown
- **Player Comparison** — Side-by-side comparison with gender filter, search, H2H, and ranking history
- **YouTube Videos** — Search and browse WTT official channel videos per player
- Data imported from ITTF and WTT APIs via automated pipelines

## Screenshots

> *Coming soon*

## Prerequisites

| Tool | Version |
|---|---|
| PHP | 8.4+ |
| Composer | 2.x |
| MySQL | 8.x+ |
| Node.js | 18+ |
| npm | 9+ |
| Python | 3.9+ (for scraping tools only) |

## Installation

### 1. Clone the repository

```bash
git clone https://github.com/pachorsc/TT_match.git
cd TT_match
```

### 2. Install PHP dependencies

```bash
composer install
```

### 3. Install Node.js dependencies

```bash
npm install
```

### 4. Create environment file

```bash
cp .env.example .env
```

### 5. Generate application key

```bash
php artisan key:generate
```

### 6. Configure database in `.env`

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tt_match
DB_USERNAME=root
DB_PASSWORD=
```

### 7. Create the database

```bash
mysql -u root -p -e "CREATE DATABASE tt_match CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### 8. Configure API keys in `.env`

```
YOUTUBE_API_KEY=your_youtube_api_key_here
```

> **YouTube Data API v3**: Create a project at https://console.cloud.google.com/apis/credentials, enable the YouTube Data API v3, and create an API key. Without this, the video search feature will return empty results (the rest of the app will work).

```
WTT_API_KEY=2bf8b222-532c-4c60-8ebe-eb6fdfebe84a
WTT_SEC_API_KEY=S_WTT_882jjh7basdj91834783mds8j2jsd81
```

> **WTT API Gateway**: These keys are found in the World Table Tennis website source code and are publicly accessible. They are required for importing rankings. You can obtain them by inspecting network requests on https://www.worldtabletennis.com.

### 9. Run migrations and seeders

```bash
php artisan migrate --seed
```

### 10. Build frontend assets

```bash
npm run build
```

### 11. Start the development server

```bash
php artisan serve
```

Visit `http://localhost:8000` in your browser.

## Importing Real Data

The seeders provide sample data. To populate the database with real rankings:

### WTT Rankings (World Table Tennis)

```bash
# Top 100 men's singles
php artisan wtt:import-ranking --gender men --limit 100

# Top 100 women's singles
php artisan wtt:import-ranking --gender women --limit 100
```

### ITTF Portal Data (matches, profiles)

Requires an ITTF results portal account (for match history scraping):

```bash
cd tools/ittf
pip install -r requirements.txt
python ittf.py login --username YOUR_USERNAME --password YOUR_PASSWORD
python ittf.py fetch rankings --gender men
python ittf.py fetch rankings --gender women
```

### WTT Matches

```bash
cd tools/wtt_matches
# See individual scripts for usage
```

## Running Tests

```bash
php artisan test
```

## Code Style

```bash
./vendor/bin/pint --test
```

## Frontend Compilation

```bash
npm run build
```

## Documentation

| File | Description |
|---|---|
| [PROJECT.md](PROJECT.md) | Project overview and features |
| [TECH_STACK.md](TECH_STACK.md) | Technology stack and constraints |
| [DOMAIN.md](DOMAIN.md) | Domain model and relationships |
| [CODING_STANDARDS.md](CODING_STANDARDS.md) | Coding standards and conventions |
| [UI_GUIDELINES.md](UI_GUIDELINES.md) | UI design guidelines |
| [ROADMAP.md](ROADMAP.md) | Development roadmap |
| [SCHEDULE.md](SCHEDULE.md) | Cron job configuration |

## Technologies

| Layer | Technology |
|---|---|
| Backend | PHP 8.4+, Laravel 12 |
| Frontend | Laravel Blade, Tailwind CSS v4 |
| Database | MySQL |
| Icons | Heroicons |
| Scripts | Python (scraping, ETL, automation) |

## Project Structure

```
TT_match/
├── app/
│   ├── Console/              # Artisan commands
│   ├── Http/                 # Controllers, Middleware, Form Requests
│   ├── Models/               # Eloquent models
│   └── Services/             # Business logic
├── config/                   # Laravel configuration files
├── database/
│   ├── factories/            # Model factories
│   ├── migrations/           # Database migrations
│   └── seeders/              # Database seeders
├── resources/
│   ├── css/                  # Stylesheets
│   ├── js/                   # JavaScript
│   └── views/                # Blade templates
├── routes/                   # Route definitions
├── tests/                    # PHPUnit tests
├── tools/                    # Python scripts (scraping, ETL)
│   ├── ittf/                 # ITTF data scraping and import
│   ├── wtt_ranking/          # WTT ranking scraper
│   └── wtt_matches/          # WTT match scraper
├── .env.example              # Environment variables template
├── AGENTS.md                 # AI agent instructions
├── CODING_STANDARDS.md       # Coding standards
├── DOMAIN.md                 # Domain model
├── LICENSE                   # MIT license
├── PROJECT.md                # Project overview
├── README.md                 # This file
├── ROADMAP.md                # Development roadmap
├── SCHEDULE.md               # Cron job configuration
├── TECH_STACK.md             # Technology stack
└── UI_GUIDELINES.md          # UI design guidelines
```

## Contributing

1. Read all project documentation before making changes
2. Create a feature branch from main
3. Follow PSR-12 coding standards
4. Keep controllers thin, use services for business logic
5. Use Blade Components for reusable UI elements
6. Write tests for service classes
7. Run `php artisan test` before submitting
8. Run `./vendor/bin/pint --test` to verify code style
9. Run `npm run build` to verify frontend compiles
10. Submit a pull request

## License

MIT License — see [LICENSE](LICENSE) for details.
