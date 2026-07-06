# Scheduler Configuration

This project uses Laravel's scheduler for recurring data import tasks.

## Available Commands

| Command | Description |
|---|---|
| `php artisan wtt:sync-matches {tournamentId}` | Sync matches for a specific tournament from WTT |
| `php artisan wtt:import-ranking` | Import rankings from WTT API Gateway |

## Setup

### 1. Verify the command works

```bash
php artisan wtt:sync-matches 3242
php artisan wtt:import-ranking --gender men --limit 100
```

### 2. Configure cron job

Add the following entry to your server's crontab:

```cron
* * * * * cd /path/to/your/project && php artisan schedule:run >> /dev/null 2>&1
```

This runs every minute. Laravel's scheduler then determines which tasks to execute based on the schedule defined in `app/Console/Kernel.php`.

### 3. Verify

Check the logs or run:

```bash
php artisan schedule:list
```

> **Note:** Laravel's `schedule:run` is safe to run every minute — it only executes tasks whose scheduled time has elapsed. Running it more frequently (e.g., every 5 minutes) will not cause duplicate executions.

## Production Notes

- Replace `/path/to/your/project` with the actual path on your server
- Ensure the cron user has write permissions to `storage/logs/`
- Set `APP_ENV=production` and `APP_DEBUG=false` in `.env` for production
