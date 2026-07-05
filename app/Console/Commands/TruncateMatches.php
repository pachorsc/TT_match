<?php

namespace App\Console\Commands;

use App\Models\GameMatch;
use App\Models\MatchSet;
use Illuminate\Console\Command;

class TruncateMatches extends Command
{
    protected $signature = 'matches:truncate {--force : Skip confirmation}';

    protected $description = 'Delete all matches and match sets from the database';

    public function handle(): int
    {
        $matchCount = GameMatch::count();
        $matchSetCount = MatchSet::count();

        if ($matchCount === 0 && $matchSetCount === 0) {
            $this->info('No matches or match sets to delete.');

            return Command::SUCCESS;
        }

        if (! $this->option('force')) {
            $this->warn("This will delete {$matchCount} matches and {$matchSetCount} match sets.");
            if (! $this->confirm('Are you sure you want to continue?')) {
                $this->info('Aborted.');

                return Command::SUCCESS;
            }
        }

        $deletedSets = MatchSet::query()->delete();
        $deletedMatches = GameMatch::query()->delete();

        $this->info("Deleted {$deletedMatches} matches and {$deletedSets} match sets.");

        return Command::SUCCESS;
    }
}
