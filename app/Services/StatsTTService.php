<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\GameMatch;
use App\Models\Player;
use App\Models\Ranking;
use App\Models\Tournament;
use RuntimeException;

class StatsTTService
{
    private string $importPath;

    public function __construct()
    {
        $this->importPath = config('statstt.import_path', storage_path('app/import/statstt'));
    }

    public function importPlayers(string $filename): array
    {
        $data = $this->loadImportFile($filename);
        $rows = $data['rows'] ?? [];
        $imported = 0;
        $updated = 0;
        $errors = [];

        foreach ($rows as $row) {
            try {
                $statsttId = (string) ($row['player_id'] ?? '');

                if (! $statsttId) {
                    $errors[] = 'Missing StatsTT ID for row: '.json_encode($row);

                    continue;
                }

                $playerData = $this->transformPlayer($row);
                $existing = Player::where('statstt_id', $statsttId)->first();

                if ($existing) {
                    $existing->update($playerData);
                    $updated++;
                } else {
                    Player::create(array_merge($playerData, ['statstt_id' => $statsttId]));
                    $imported++;
                }
            } catch (\Exception $e) {
                $errors[] = "Error importing player: {$e->getMessage()}";
            }
        }

        return [
            'imported' => $imported,
            'updated' => $updated,
            'errors' => $errors,
        ];
    }

    public function importMatches(string $filename): array
    {
        $data = $this->loadImportFile($filename);
        $rows = $data['rows'] ?? [];
        $imported = 0;
        $updated = 0;
        $errors = [];

        foreach ($rows as $row) {
            try {
                $statsttId = (string) ($row['match_id'] ?? '');

                if (! $statsttId) {
                    $errors[] = 'Missing StatsTT ID for match row';

                    continue;
                }

                $playerAId = $this->resolvePlayerId($row['player_a_id'] ?? null);
                $playerBId = $this->resolvePlayerId($row['player_b_id'] ?? $row['player_x_id'] ?? null);
                $winnerId = $this->resolvePlayerId($row['winner_s_id'] ?? $row['winner_d_id'] ?? null);
                $tournamentId = $this->resolveTournamentId($row['event_id'] ?? null);

                // Auto-create tournament if not found
                if (! $tournamentId && ! empty($row['event_id'])) {
                    $tournament = Tournament::create([
                        'statstt_id' => (string) $row['event_id'],
                        'name' => $row['event_name'] ?? 'Unknown Tournament',
                        'location' => $row['location'] ?? '',
                        'country' => '',
                        'country_code' => '',
                        'start_date' => $row['date'] ?? date('Y-m-d'),
                        'end_date' => $row['date'] ?? date('Y-m-d'),
                        'category' => null,
                    ]);
                    $tournamentId = $tournament->id;
                }

                if (! $playerAId || ! $playerBId) {
                    $errors[] = "Could not resolve players for match {$statsttId}";

                    continue;
                }

                if ($playerAId === $playerBId) {
                    $errors[] = "Match {$statsttId}: player_a and player_b are the same";

                    continue;
                }

                if (! $tournamentId) {
                    $errors[] = "Could not resolve tournament for match {$statsttId}";

                    continue;
                }

                $score = $row['result'] ?? '0-0';
                [$playerASets, $playerBSets] = $this->parseScore($score);

                $matchData = [
                    'tournament_id' => $tournamentId,
                    'player_a_id' => $playerAId,
                    'player_b_id' => $playerBId,
                    'winner_id' => $winnerId,
                    'player_a_sets' => $playerASets,
                    'player_b_sets' => $playerBSets,
                    'match_date' => $row['date'] ?? date('Y-m-d'),
                    'round' => $row['round'] ?? '',
                    'status' => 'Completed',
                ];

                $existing = GameMatch::where('statstt_id', $statsttId)->first();

                if ($existing) {
                    $existing->update($matchData);
                    $updated++;
                } else {
                    GameMatch::create(array_merge($matchData, ['statstt_id' => $statsttId]));
                    $imported++;
                }
            } catch (\Exception $e) {
                $errors[] = "Error importing match: {$e->getMessage()}";
            }
        }

        return [
            'imported' => $imported,
            'updated' => $updated,
            'errors' => $errors,
        ];
    }

    public function importRankings(string $filename): array
    {
        $data = $this->loadImportFile($filename);
        $rows = $data['rows'] ?? [];
        $imported = 0;
        $errors = [];

        foreach ($rows as $row) {
            try {
                $playerId = $this->resolvePlayerId($row['player_id'] ?? null);

                if (! $playerId) {
                    $errors[] = 'Could not resolve player for ranking: '.json_encode($row);

                    continue;
                }

                Ranking::create([
                    'player_id' => $playerId,
                    'ranking' => $row['rank_position'] ?? 0,
                    'rating_points' => $row['rating'] ?? 0,
                    'ranking_date' => $row['last_match_date'] ?? date('Y-m-d'),
                ]);

                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Error importing ranking: {$e->getMessage()}";
            }
        }

        return [
            'imported' => $imported,
            'errors' => $errors,
        ];
    }

    public function importTournaments(string $filename): array
    {
        $data = $this->loadImportFile($filename);
        $rows = $data['rows'] ?? [];
        $imported = 0;
        $updated = 0;
        $errors = [];

        foreach ($rows as $row) {
            try {
                $statsttId = (string) ($row['event_id'] ?? '');

                if (! $statsttId) {
                    $errors[] = 'Missing StatsTT ID for tournament';

                    continue;
                }

                $tournamentData = [
                    'name' => $row['event_name'] ?? '',
                    'location' => $row['location'] ?? '',
                    'country' => '',
                    'country_code' => '',
                    'start_date' => $row['start_date'] ?? date('Y-m-d'),
                    'end_date' => $row['end_date'] ?? date('Y-m-d'),
                    'category' => $row['level'] ?? null,
                ];

                $existing = Tournament::where('statstt_id', $statsttId)->first();

                if ($existing) {
                    $existing->update($tournamentData);
                    $updated++;
                } else {
                    Tournament::create(array_merge($tournamentData, ['statstt_id' => $statsttId]));
                    $imported++;
                }
            } catch (\Exception $e) {
                $errors[] = "Error importing tournament: {$e->getMessage()}";
            }
        }

        return [
            'imported' => $imported,
            'updated' => $updated,
            'errors' => $errors,
        ];
    }

    private function transformPlayer(array $row): array
    {
        $fullName = $row['name'] ?? '';
        [$firstName, $lastName] = $this->parsePlayerName($fullName);

        $countryCode = $row['association_code'] ?? '';
        if (strlen($countryCode) > 2) {
            $countryCode = substr($countryCode, 0, 2);
        }

        return [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'country' => $row['association'] ?? '',
            'country_code' => $countryCode,
            'date_of_birth' => $row['birth_date'] ?? '2000-01-01',
            'dominant_hand' => $this->mapHand($row['hand'] ?? null),
            'playing_style' => $this->mapStyle($row['style'] ?? null),
            'height_cm' => $row['height'] ?? null,
        ];
    }

    private function parsePlayerName(string $fullName): array
    {
        $parts = preg_split('/\s+/', trim($fullName));

        if (count($parts) <= 1) {
            return ['', $fullName];
        }

        $surnameParts = [];
        $givenParts = [];

        foreach ($parts as $i => $part) {
            if ($part === strtoupper($part) && ! preg_match('/\d/', $part) && empty($givenParts)) {
                $surnameParts[] = $part;
            } else {
                $givenParts = array_slice($parts, $i);
                break;
            }
        }

        if (empty($givenParts)) {
            return [$fullName, ''];
        }

        $firstName = implode(' ', $givenParts);
        $lastName = ucwords(implode(' ', $surnameParts));

        return [$firstName, $lastName];
    }

    private function mapHand(?string $hand): string
    {
        $hand = strtoupper(trim($hand ?? ''));

        return in_array($hand, ['L', 'LEFT']) ? 'Left' : 'Right';
    }

    private function mapStyle(?string $style): ?string
    {
        if (! $style) {
            return null;
        }
        $style = strtolower(trim($style));
        if (str_contains($style, 'off')) {
            return 'Offensive';
        }
        if (str_contains($style, 'def')) {
            return 'Defensive';
        }
        if (str_contains($style, 'all')) {
            return 'All-round';
        }

        return null;
    }

    private function parseScore(string $score): array
    {
        if (preg_match('/(\d+)\s*[-:]\s*(\d+)/', $score, $matches)) {
            return [(int) $matches[1], (int) $matches[2]];
        }

        return [0, 0];
    }

    private function resolvePlayerId(string|int|null $externalId): ?int
    {
        if (! $externalId) {
            return null;
        }
        $player = Player::where('statstt_id', (string) $externalId)->first();

        return $player?->id;
    }

    private function resolveTournamentId(string|int|null $externalId): ?int
    {
        if (! $externalId) {
            return null;
        }
        $tournament = Tournament::where('statstt_id', (string) $externalId)->first();

        return $tournament?->id;
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
