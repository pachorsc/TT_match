<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\GameMatch;
use App\Models\MatchSet;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ValidateMatchData extends Command
{
    protected $signature = 'matches:validate
        {--dry-run : Show what would be deleted without actually deleting}';

    protected $description = 'Remove impossible matches (same players facing each other more than once in the same tournament)';

    private const ROUND_ORDER = [
        'Final' => 6,
        'SemiFinal' => 5,
        'QuarterFinal' => 4,
        'R16' => 3,
        'Round of 16' => 3,
        'R32' => 2,
        'Round of 32' => 2,
        'R64' => 1,
        'Round of 64' => 1,
    ];

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $totalDeleted = 0;

        $this->info('Finding impossible match groups (same players, same tournament, multiple matches)...');

        $duplicates = DB::table('matches')
            ->select(
                'tournament_id',
                DB::raw('LEAST(player_a_id, player_b_id) as p1'),
                DB::raw('GREATEST(player_a_id, player_b_id) as p2'),
                DB::raw('COUNT(*) as cnt'),
                DB::raw('GROUP_CONCAT(id ORDER BY id) as ids')
            )
            ->where('status', 'Completed')
            ->groupBy('tournament_id', 'p1', 'p2')
            ->having('cnt', '>', 1)
            ->get();

        if ($duplicates->isEmpty()) {
            $this->info('No duplicate match groups found.');

            return Command::SUCCESS;
        }

        $this->info("Found {$duplicates->count()} groups with multiple matches.");

        foreach ($duplicates as $group) {
            $idList = array_map('intval', explode(',', $group->ids));

            $matches = GameMatch::with(['playerA', 'playerB', 'tournament'])
                ->whereIn('id', $idList)
                ->get();

            $tournament = $matches->first()->tournament;
            $playerA = $matches->first()->playerA;
            $playerB = $matches->first()->playerB;

            $this->line("  {$tournament->name} | {$playerA->first_name} vs {$playerB->first_name} ({$group->cnt} matches)");

            // Keep the match with the highest round importance
            $bestMatch = $matches->sortByDesc(function (GameMatch $m) {
                $round = strtolower($m->round);

                // Check exact match first
                foreach (self::ROUND_ORDER as $key => $order) {
                    if (strtolower($key) === $round) {
                        return $order;
                    }
                }

                // Partial match
                foreach (self::ROUND_ORDER as $key => $order) {
                    if (str_contains($round, strtolower($key))) {
                        return $order;
                    }
                }

                return 0;
            })->first();

            $keepId = $bestMatch->id;
            $deleteIds = array_filter($idList, fn ($id) => $id !== $keepId);

            $this->line("    Keep:   ID {$keepId} ({$bestMatch->round})");

            foreach ($deleteIds as $deleteId) {
                $del = $matches->firstWhere('id', $deleteId);
                $this->line("    Delete: ID {$deleteId} ({$del?->round})");

                if (! $dryRun) {
                    MatchSet::where('match_id', $deleteId)->delete();
                    GameMatch::where('id', $deleteId)->delete();
                    $totalDeleted++;
                }
            }
        }

        $verb = $dryRun ? 'found' : 'deleted';
        $this->info("Total impossible matches {$verb}: {$totalDeleted}");

        return Command::SUCCESS;
    }
}
