<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\WttMatchSyncService;
use Illuminate\Console\Command;

final class SyncWttMatches extends Command
{
    protected $signature = 'wtt:sync-matches
        {eventId=3242 : WTT Event ID}
        {--tournament-id= : Optional tournament ID (auto-resolves from API if omitted)}
        {--name= : Tournament name (overrides API response)}';

    protected $description = 'Sync WTT matches directly from the API — only processes new or changed matches';

    public function handle(WttMatchSyncService $syncService): int
    {
        $eventId = (int) $this->argument('eventId');
        $tournamentId = $this->option('tournament-id')
            ? (int) $this->option('tournament-id')
            : null;
        $tournamentName = $this->option('name') ?: null;

        $this->info("Syncing matches for WTT event {$eventId}...");
        $this->newLine();

        $result = $syncService->sync($eventId, $tournamentId, $tournamentName);

        $this->line(" Tournament: {$result['tournament']}");
        $this->line(" Total in API: {$result['total_in_api']}");
        $this->line(" Existing in DB: {$result['existing_in_db']}");
        $this->newLine();
        $this->info("   ✓ Created: {$result['created']}");
        $this->info("   ✓ Updated: {$result['updated']}");
        $this->info("   - Skipped: {$result['skipped']}");

        if ($result['errors'] !== []) {
            $this->newLine();
            $this->warn('Errors ('.count($result['errors']).'):');

            foreach ($result['errors'] as $error) {
                $this->error("  - {$error}");
            }
        }

        $this->newLine();

        return Command::SUCCESS;
    }
}
