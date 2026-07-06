<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Player;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixPlayerGenders extends Command
{
    protected $signature = 'players:fix-genders
        {--dry-run : Show what would change without updating}';

    protected $description = 'Fix incorrect player genders using ITTF rankings data and opponent analysis';

    private string $importPath;

    public function __construct()
    {
        parent::__construct();
        $this->importPath = config('ittf.import_path', storage_path('app/import/ittf'));
    }

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $totalFixed = 0;

        $totalFixed += $this->fixFromRankings($dryRun);
        $totalFixed += $this->fixFromOpponentAnalysis($dryRun);

        $this->newLine();
        $this->info("Total players fixed: {$totalFixed}");

        return Command::SUCCESS;
    }

    private function fixFromRankings(bool $dryRun): int
    {
        $fixed = 0;
        $womenFile = $this->importPath.'/rankings_women_2026-07-06_all.json';

        if (! file_exists($womenFile)) {
            $this->warn('Women rankings file not found, skipping rankings-based fix.');

            return 0;
        }

        $this->info('Strategy 1: Fixing genders from ITTF women rankings...');
        $data = json_decode(file_get_contents($womenFile), true);
        $rows = $data['rows'] ?? [];

        $ittfIds = collect($rows)->pluck('ittf_id')->filter()->unique()->values();

        $this->line("  Found {$ittfIds->count()} women in rankings data.");

        // Load women's ITTF IDs that we have in our DB with wrong gender
        $candidates = Player::whereIn('ittf_id', $ittfIds)
            ->where('gender', '!=', 'F')
            ->get(['id', 'ittf_id', 'first_name', 'last_name', 'gender']);

        foreach ($candidates as $player) {
            $this->line("  {$player->id} {$player->first_name} {$player->last_name} (ittf_id={$player->ittf_id}) gender={$player->gender} -> F");

            if (! $dryRun) {
                $player->update(['gender' => 'F']);
            }

            $fixed++;
        }

        $this->info("  Fixed: {$fixed} players from rankings data.");

        return $fixed;
    }

    private function fixFromOpponentAnalysis(bool $dryRun): int
    {
        $fixed = 0;

        $this->info('Strategy 2: Checking players marked M who only face women...');

        Player::where('gender', 'M')->chunk(200, function ($players) use ($dryRun, &$fixed) {
            foreach ($players as $player) {
                $opponentGenders = DB::table('matches')
                    ->where('player_a_id', $player->id)
                    ->join('players as pb', 'matches.player_b_id', '=', 'pb.id')
                    ->select('pb.gender')
                    ->union(
                        DB::table('matches')
                            ->where('player_b_id', $player->id)
                            ->join('players as pa', 'matches.player_a_id', '=', 'pa.id')
                            ->select('pa.gender')
                    )
                    ->distinct()
                    ->pluck('gender');

                if ($opponentGenders->count() !== 1 || $opponentGenders->first() !== 'F') {
                    continue;
                }

                $this->line("  {$player->id} {$player->first_name} {$player->last_name} -> F (only faces women)");

                if (! $dryRun) {
                    $player->update(['gender' => 'F']);
                }

                $fixed++;
            }
        });

        $this->info("  Fixed: {$fixed} players from opponent analysis.");

        return $fixed;
    }
}
