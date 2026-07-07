<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\GameMatch;
use App\Models\MatchSet;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanDuplicateMatches extends Command
{
    protected $signature = 'matches:clean-duplicates
        {--dry-run : Show what would be deleted without actually deleting}';

    protected $description = 'Remove duplicate matches (same tournament, players, round, and score)';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $totalDeleted = 0;

        $this->info('Finding duplicate matches...');

        $duplicates = DB::table('matches')
            ->select(
                'tournament_id',
                DB::raw('LEAST(player_a_id, player_b_id) as p1'),
                DB::raw('GREATEST(player_a_id, player_b_id) as p2'),
                'round',
                'match_date',
                DB::raw('COUNT(*) as cnt'),
                DB::raw('GROUP_CONCAT(id ORDER BY id) as ids')
            )
            ->where('status', 'Completed')
            ->groupBy('tournament_id', 'p1', 'p2', 'round', 'match_date')
            ->having('cnt', '>', 1)
            ->get();

        if ($duplicates->isEmpty()) {
            $this->info('No duplicate matches found.');

            return Command::SUCCESS;
        }

        $this->info("Found {$duplicates->count()} groups of duplicate matches.");

        foreach ($duplicates as $group) {
            $idList = explode(',', $group->ids);

            // Keep the first (lowest ID), delete the rest
            $keepId = (int) $idList[0];
            $deleteIds = array_map('intval', array_slice($idList, 1));

            $matches = GameMatch::with(['playerA', 'playerB', 'tournament'])
                ->whereIn('id', [$keepId, ...$deleteIds])
                ->get()
                ->keyBy('id');

            $keep = $matches->get($keepId);
            $playerA = $keep?->playerA;
            $playerB = $keep?->playerB;

            $this->line(
                "  Group: {$keep?->tournament?->name} | {$playerA?->first_name} vs {$playerB?->first_name} | {$keep?->round}"
            );
            $this->line("    Keep:   ID {$keepId}");

            foreach ($deleteIds as $deleteId) {
                $del = $matches->get($deleteId);
                $this->line("    Delete: ID {$deleteId} (ittf_id: {$del?->ittf_id})");

                if (! $dryRun) {
                    MatchSet::where('match_id', $deleteId)->delete();
                    GameMatch::where('id', $deleteId)->delete();
                    $totalDeleted++;
                }
            }
        }

        $verb = $dryRun ? 'found' : 'deleted';
        $this->info("Total duplicate matches {$verb}: {$totalDeleted}");

        return Command::SUCCESS;
    }
}
