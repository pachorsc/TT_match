<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\GameMatch;
use App\Models\MatchSet;
use Illuminate\Console\Command;

class CleanInvalidMatches extends Command
{
    protected $signature = 'matches:clean-invalid
        {--dry-run : Show what would be deleted without actually deleting}';

    protected $description = 'Delete matches where players have different genders (impossible for singles/team events)';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $totalDeleted = 0;

        $this->info('Finding matches with gender-mismatched players...');

        $ids = GameMatch::where(function ($q) {
            $q->where(function ($q2) {
                $q2->whereHas('playerA', fn ($q3) => $q3->where('gender', 'M'))
                    ->whereHas('playerB', fn ($q3) => $q3->where('gender', 'F'));
            })->orWhere(function ($q2) {
                $q2->whereHas('playerA', fn ($q3) => $q3->where('gender', 'F'))
                    ->whereHas('playerB', fn ($q3) => $q3->where('gender', 'M'));
            });
        })->pluck('id');

        if ($ids->isEmpty()) {
            $this->info('No gender-mismatched matches found.');

            return Command::SUCCESS;
        }

        $this->info("Found {$ids->count()} matches to ".($dryRun ? 'review' : 'delete').'.');

        // Load matches for display
        $matches = GameMatch::with(['playerA', 'playerB'])->whereIn('id', $ids)->get();

        foreach ($matches as $match) {
            $playerA = $match->playerA;
            $playerB = $match->playerB;

            $this->line("  Match #{$match->id}: {$playerA->first_name} {$playerA->last_name} ({$playerA->gender}) [{$match->player_a_sets}] - [{$match->player_b_sets}] {$playerB->first_name} {$playerB->last_name} ({$playerB->gender})");
        }

        if (! $dryRun) {
            // Delete in chunks to avoid large transactions
            foreach ($ids->chunk(200) as $chunk) {
                MatchSet::whereIn('match_id', $chunk)->delete();
                GameMatch::whereIn('id', $chunk)->delete();
                $totalDeleted += count($chunk);
            }
        } else {
            $totalDeleted = $ids->count();
        }

        $this->info('Total matches '.($dryRun ? 'found' : 'deleted').": {$totalDeleted}");

        return Command::SUCCESS;
    }
}
