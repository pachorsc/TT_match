# CODING_STANDARDS.md — Coding Standards

## General Principles

- Follow PSR-12 coding standards for all PHP code
- Prefer readable code over clever code
- Avoid duplicated code. Extract shared logic into reusable components
- Write code that is easy to understand and maintain
- Every piece of code should have a clear purpose

## PHP / Laravel Standards

### Controllers

- Controllers must remain thin
- Controllers handle HTTP requests and delegate to services
- Never put business logic inside controllers
- Use dependency injection to resolve services
- Use Form Requests for all validation
- One controller action should do one thing

### Services

- Business logic belongs in Service classes
- Services are located in `app/Services/`
- Services are injected via constructor dependency injection
- Services should be focused and follow the Single Responsibility Principle
- Use interfaces when multiple implementations are needed

### Models

- Define relationships clearly with return types
- Use accessors and mutators when appropriate
- Keep models focused on data representation and relationships
- Avoid putting business logic in models

### Form Requests

- Use Form Requests for all validation logic
- Create dedicated Form Request classes for each validation scenario
- Never validate inside controllers

### Migrations

- Always use migrations for database changes
- Never modify tables manually
- Use foreign keys to enforce referential integrity
- Add proper indexes for query performance
- Use seeders and factories for test data

## Frontend Standards

### Blade Templates

- Use Blade Components for reusable UI elements
- Reuse existing components before creating new ones
- Keep templates clean and focused
- Use semantic HTML

### CSS (Tailwind)

- Use Tailwind CSS utility classes
- Follow the design system defined in UI_GUIDELINES.md
- Extract repeated patterns into Blade components
- Maintain consistency across all pages

### JavaScript

- Prefer vanilla JavaScript over frameworks
- Use Alpine.js only when vanilla JS would be significantly more complex
- Keep JavaScript minimal and focused
- Never introduce jQuery or other libraries outside the approved stack

## File Organization

```
app/
├── Console/          # Artisan commands
├── Exceptions/       # Custom exceptions
├── Http/
│   ├── Controllers/  # Thin controllers
│   ├── Middleware/    # HTTP middleware
│   └── Requests/     # Form Requests
├── Models/           # Eloquent models
├── Services/         # Business logic
└── View/             # Blade components and view composers
```

## Naming Conventions

| Element | Convention | Example |
|---|---|---|
| Classes | PascalCase | `PlayerService` |
| Methods | camelCase | `getPlayerStats()` |
| Variables | camelCase | `$playerRanking` |
| Files | PascalCase (classes), snake_case (config) | `PlayerController.php` |
| Database tables | snake_case, plural | `players`, `match_results` |
| Database columns | snake_case | `world_ranking`, `rating_points` |
| Blade components | kebab-case | `player-card`, `match-table` |

## Testing

- Write tests for all service classes
- Write feature tests for critical user flows
- Run `php artisan test` before completing any task
- Tests should be readable and well-organized
