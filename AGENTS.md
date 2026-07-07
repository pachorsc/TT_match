# AGENTS.md — AI Agent Instructions

## Purpose

This file contains mandatory instructions for any AI agent working on this codebase.

## Before Writing Any Code

1. Read all project documentation files in the repository root:
   - `README.md`
   - `PROJECT.md`
   - `TECH_STACK.md`
   - `CODING_STANDARDS.md`
   - `UI_GUIDELINES.md`
   - `DOMAIN.md`
   - `ROADMAP.md`
   - `IMPROVEMENTS.md` (contains pending features to implement)

2. Understand the domain model and relationships before modifying any model or migration.

3. Verify which technologies are permitted before introducing any new package or dependency.

## Architecture Rules

- Controllers must remain thin. They handle HTTP requests and delegate to services.
- Business logic belongs in Service classes under `app/Services/`.
- Use dependency injection. Never resolve services manually inside controllers.
- Use Form Requests for validation. Never validate inside controllers.
- Use Blade Components for reusable UI elements. Never duplicate markup.
- Follow PSR-12 coding standards.
- Prefer readable code over clever code.
- Avoid duplicated code. Extract shared logic into services, helpers, or components.

## Technology Constraints

- **Backend:** PHP 8.4+, Laravel 12
- **Frontend:** Laravel Blade, Tailwind CSS v4, vanilla JavaScript, Alpine.js only if absolutely necessary
- **Database:** MySQL
- **Python:** Only for scraping, ETL, data import, scheduled jobs, automation, and data cleaning. Business logic must remain in Laravel.
- **Icons:** Heroicons

### Never Introduce

- React, Vue, Angular, or Livewire (unless explicitly requested by the user)
- Player photos, avatars, or decorative graphics
- Betting odds, AI predictions, win probabilities, confidence bars, heat maps
- Unnecessary charts or visualizations
- Any technology outside the defined stack

## UI Rules

- Desktop-first design
- Premium dark theme
- Large spacing, rounded cards (16–20px), soft shadows, thin borders
- Subtle glassmorphism effects
- Excellent typography
- Modern sports analytics aesthetic
- Responsive layout
- Inspiration: Apple Sports, SofaScore, ATP Tour, Flashscore, TradingView

## Code Quality

- Run `php artisan test` before completing any task.
- Run `./vendor/bin/pint --test` to verify code style.
- Run `npm run build` to verify frontend compilation.
- Never commit secrets, API keys, or credentials.
- Never modify database tables manually. Use migrations, seeders, and factories.
- Use foreign keys and proper indexes in all migrations.

## Communication

- When completing a task, report what was done and any decisions made.
- If you encounter ambiguity, ask the user before proceeding.
- Do not make architectural changes without explicit justification.
- Keep code clean, modular, and maintainable for long-term development.
