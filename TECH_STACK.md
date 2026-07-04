# TECH_STACK.md — Technology Stack

## Backend

| Technology | Version | Purpose |
|---|---|---|
| PHP | 8.4+ | Server-side language |
| Laravel | 12 | PHP framework |
| MySQL | 8.x+ | Primary database |

## Frontend

| Technology | Purpose |
|---|---|
| Laravel Blade | Templating engine |
| Tailwind CSS v4 | Utility-first CSS framework |
| Alpine.js | Lightweight interactivity (only if absolutely necessary) |
| Vanilla JavaScript | Preferred for any scripting needs |

## Icons

| Library | Usage |
|---|---|
| Heroicons | All icons throughout the application |

## Python (External Scripts)

| Purpose | Notes |
|---|---|
| Web scraping | Collecting match data, player data, news |
| ETL pipelines | Transforming raw data into application format |
| Ranking imports | Importing official ITTF rankings |
| Scheduled jobs | Cron-based data collection and updates |
| Data cleaning | Normalizing and validating imported data |

**Important:** Python is used ONLY for data acquisition and automation. All business logic must remain inside Laravel.

## Prohibited Technologies

The following must never be introduced into this project:

- React
- Vue.js
- Angular
- Livewire (unless explicitly requested by the user)
- jQuery
- Bootstrap
- Any JavaScript framework not listed above
- Any CSS framework not listed above

## Development Tools

| Tool | Purpose |
|---|---|
| Composer | PHP dependency management |
| npm | Node.js dependency management |
| Laravel Pint | Code style enforcement |
| PHPUnit | PHP testing |
| Vite | Frontend asset bundling |

## Environment Requirements

- PHP 8.4+
- MySQL 8.x+
- Composer 2.x
- Node.js 18+ (for Vite)
- npm 9+
