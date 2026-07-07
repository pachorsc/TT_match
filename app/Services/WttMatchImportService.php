<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\GameMatch;
use App\Models\MatchSet;
use App\Models\Player;
use App\Models\Tournament;
use RuntimeException;

class WttMatchImportService
{
    private string $importPath;

    public function __construct()
    {
        $this->importPath = storage_path('app/import/wtt');
    }

    public function importFromJson(string $filename): array
    {
        $data = $this->loadImportFile($filename);
        $matches = $data['matches'] ?? [];
        $imported = 0;
        $skipped = 0;
        $errors = [];

        $tournament = $this->resolveOrCreateTournament($data);

        foreach ($matches as $match) {
            try {
                $result = $this->processMatch($match, $tournament->id);
                if ($result) {
                    $imported++;
                } else {
                    $skipped++;
                }
            } catch (\Exception $e) {
                $errors[] = "Error processing match: {$e->getMessage()}";
            }
        }

        return [
            'imported' => $imported,
            'skipped' => $skipped,
            'errors' => $errors,
        ];
    }

    private function processMatch(array $match, int $tournamentId): bool
    {
        $playerAIddf = $match['player_a_ittf_id'] ?? '';
        $playerBIddf = $match['player_b_ittf_id'] ?? '';

        if (! $playerAIddf || ! $playerBIddf) {
            return false;
        }

        $playerA = $this->resolvePlayerByWttId($playerAIddf);
        $playerB = $this->resolvePlayerByWttId($playerBIddf);

        if (! $playerA || ! $playerB) {
            return false;
        }

        if ($playerA->id === $playerB->id) {
            return false;
        }

        $overallScores = $match['overall_scores'] ?? '';
        [$playerASets, $playerBSets] = $this->parseScore($overallScores);

        $winnerIttfId = $match['winner_ittf_id'] ?? '';
        $winnerId = null;
        if ($winnerIttfId) {
            $winnerPlayer = $this->resolvePlayerByWttId($winnerIttfId);
            $winnerId = $winnerPlayer?->id;
        }

        if (! $winnerId) {
            if ($playerASets > $playerBSets) {
                $winnerId = $playerA->id;
            } elseif ($playerBSets > $playerASets) {
                $winnerId = $playerB->id;
            }
        }

        $matchDate = $this->parseDate($match['date'] ?? '');

        $round = $this->extractRound($match['sub_event'] ?? '');

        $completed = $match['completed'] ?? false;
        $status = $completed ? 'Completed' : 'Scheduled';

        $documentCode = $match['document_code'] ?? '';

        $existing = GameMatch::where('ittf_id', $documentCode)->first();

        $matchData = [
            'tournament_id' => $tournamentId,
            'player_a_id' => $playerA->id,
            'player_b_id' => $playerB->id,
            'winner_id' => $winnerId,
            'player_a_sets' => $playerASets,
            'player_b_sets' => $playerBSets,
            'match_date' => $matchDate,
            'round' => $round,
            'status' => $status,
        ];

        if ($existing) {
            $existing->update($matchData);
        } else {
            $existing = GameMatch::create(array_merge($matchData, ['ittf_id' => $documentCode]));
        }

        $this->importMatchSets($existing->id, $match['game_scores'] ?? '');

        return true;
    }

    private function importMatchSets(int $matchId, string $gameScores): void
    {
        if (! $gameScores) {
            return;
        }

        MatchSet::where('match_id', $matchId)->delete();

        $games = array_filter(explode(',', $gameScores));
        $setNumber = 1;

        foreach ($games as $game) {
            $game = trim($game);
            if ($game === '0-0' || $game === '0:0') {
                continue;
            }

            $parts = preg_split('/[-:]/', $game);
            if (count($parts) === 2) {
                $aPoints = (int) $parts[0];
                $bPoints = (int) $parts[1];

                if ($aPoints > 0 || $bPoints > 0) {
                    MatchSet::create([
                        'match_id' => $matchId,
                        'set_number' => $setNumber,
                        'player_a_points' => $aPoints,
                        'player_b_points' => $bPoints,
                    ]);
                    $setNumber++;
                }
            }
        }
    }

    private function resolvePlayerByWttId(string $ittfId): ?Player
    {
        if (! $ittfId) {
            return null;
        }

        return Player::where('wtt_id', (string) $ittfId)->first();
    }

    private function resolveOrCreateTournament(array $data): Tournament
    {
        $existing = Tournament::where('name', 'United States Smash 2026')->first();

        if ($existing) {
            return $existing;
        }

        return Tournament::create([
            'name' => 'United States Smash 2026',
            'location' => 'Ontario, California',
            'country' => 'United States',
            'country_code' => 'US',
            'start_date' => '2026-06-26',
            'end_date' => '2026-07-05',
            'category' => 'WTT Grand Smash',
        ]);
    }

    private function parseScore(string $score): array
    {
        if (preg_match('/(\d+)\s*[-:]\s*(\d+)/', $score, $matches)) {
            return [(int) $matches[1], (int) $matches[2]];
        }

        return [0, 0];
    }

    private function parseDate(string $dateStr): string
    {
        if (! $dateStr) {
            return date('Y-m-d');
        }

        $parsed = date_create_from_format('Y-m-d\TH:i:s', $dateStr);
        if ($parsed) {
            return $parsed->format('Y-m-d');
        }

        $parsed = date_create($dateStr);
        if ($parsed) {
            return $parsed->format('Y-m-d');
        }

        return date('Y-m-d');
    }

    private function extractRound(string $description): string
    {
        $description = strtolower($description);

        if (str_contains($description, 'final') && ! str_contains($description, 'semifinal') && ! str_contains($description, 'quarterfinal')) {
            return 'Final';
        }
        if (str_contains($description, 'semifinal') || str_contains($description, 'sf')) {
            return 'Semifinal';
        }
        if (str_contains($description, 'quarterfinal') || str_contains($description, 'qf')) {
            return 'Quarterfinal';
        }
        if (str_contains($description, 'round of 64') || str_contains($description, 'r64')) {
            return 'Round of 64';
        }
        if (str_contains($description, 'round of 32') || str_contains($description, 'r32')) {
            return 'Round of 32';
        }
        if (str_contains($description, 'round of 16') || str_contains($description, 'r16') || str_contains($description, '8fnl')) {
            return 'Round of 16';
        }

        return $description;
    }

    private function loadImportFile(string $filename): array
    {
        $path = $this->importPath.'/'.$filename;

        if (! file_exists($path)) {
            throw new RuntimeException("Import file not found: {$path}");
        }

        $content = file_get_contents($path);
        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException('Invalid JSON in import file: '.json_last_error_msg());
        }

        return $data;
    }
}
