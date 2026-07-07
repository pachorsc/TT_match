<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\GameMatch;
use App\Models\Tournament;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class CleanDuplicateTournaments extends Command
{
    protected $signature = 'tournaments:clean-duplicates
        {--dry-run : Show what would be done without making changes}';

    protected $description = 'Merge duplicate tournaments and remove duplicate matches';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        $this->info('Finding duplicate tournaments...');

        $duplicates = $this->findDuplicateTournaments();

        if ($duplicates->isEmpty()) {
            $this->info('No duplicate tournaments found.');

            return Command::SUCCESS;
        }

        $this->info("Found {$duplicates->count()} duplicate tournament groups.");

        $mergedCount = 0;
        $matchUpdates = 0;

        foreach ($duplicates as $group) {
            /** @var Tournament $keep */
            $keep = $group->shift();

            $this->line("  Keeping: [{$keep->id}] {$keep->name}");

            foreach ($group as $dupe) {
                $this->line("    Merging: [{$dupe->id}] {$dupe->name}");

                if (! $dryRun) {
                    $affected = GameMatch::where('tournament_id', $dupe->id)
                        ->where('tournament_id', '!=', $keep->id)
                        ->update(['tournament_id' => $keep->id]);

                    $matchUpdates += $affected;
                    $mergedCount++;

                    $dupe->delete();
                } else {
                    $affected = GameMatch::where('tournament_id', $dupe->id)->count();
                    $this->line("      Would move {$affected} matches and delete tournament");
                }
            }
        }

        $verb = $dryRun ? 'found' : 'deleted';
        $this->info("Duplicate tournaments {$verb}: {$mergedCount}");

        if (! $dryRun) {
            $this->info("Matches re-assigned: {$matchUpdates}");
            $this->warn('Run matches:clean-duplicates next to remove duplicate match rows.');
        }

        return Command::SUCCESS;
    }

    private function findDuplicateTournaments(): Collection
    {
        $all = Tournament::all();
        $groups = collect();

        foreach ($all as $tournament) {
            [$baseName, $year] = $this->extractNameAndYear($tournament->name);
            $key = $baseName.'|'.$year;

            if (! isset($groups[$key])) {
                $groups[$key] = collect();
            }

            $groups[$key]->push($tournament);
        }

        return $groups->filter(fn (Collection $group) => $group->count() > 1);
    }

    /**
     * Extract base name and year from tournament name.
     * Returns [baseName, year] where year is '0000' if not found.
     */
    private function extractNameAndYear(string $name): array
    {
        $normalized = $name;

        $year = '0000';
        if (preg_match('/\b(20\d{2})\b/', $normalized, $matches)) {
            $year = $matches[1];
        }

        // Remove year for base name comparison
        $normalized = str_replace($year, '', $normalized);

        // Remove "Presented by ..." and similar
        $normalized = preg_replace('/\s+(Presented by|Sponsored by|Powered by).+$/i', '', $normalized);

        // Normalize "Men's & Women's" / "Men's and Women's" -> ""
        $normalized = preg_replace("/ITTF\s+(Men's\s*[&and]\s*Women's\s+)?World\s+Cup/i", 'ITTF World Cup', $normalized);

        // Collapse whitespace
        $normalized = preg_replace('/\s+/', ' ', $normalized);
        $normalized = trim($normalized);

        return [$normalized, $year];
    }
}
