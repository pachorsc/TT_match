<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\GameMatch;
use App\Models\Player;
use App\Models\Ranking;
use RuntimeException;

class IttfImportService
{
    private string $importPath;

    public function __construct()
    {
        $this->importPath = config('ittf.import_path', storage_path('app/import/ittf'));
    }

    public function importRankings(string $filename): array
    {
        $data = $this->loadImportFile($filename);
        $rows = $data['rows'] ?? [];
        $imported = 0;
        $errors = [];

        foreach ($rows as $row) {
            try {
                $ittfId = (string) ($row['ittf_id'] ?? '');

                if (! $ittfId) {
                    $errors[] = 'Missing ITTF ID for ranking row';

                    continue;
                }

                $playerId = $this->resolvePlayerId($ittfId);

                if (! $playerId) {
                    $playerId = $this->autoCreatePlayer($row);
                }

                if (! $playerId) {
                    $errors[] = "Could not resolve player for ITTF ID {$ittfId}";

                    continue;
                }

                Ranking::create([
                    'player_id' => $playerId,
                    'ranking' => $row['rank_position'] ?? 0,
                    'rating_points' => $row['rating_points'] ?? 0,
                    'ranking_date' => $data['fetched_at'] ?? date('Y-m-d'),
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

    public function importPlayers(string $filename): array
    {
        $data = $this->loadImportFile($filename);
        $rows = $data['rows'] ?? [];
        $imported = 0;
        $updated = 0;
        $errors = [];

        foreach ($rows as $row) {
            try {
                $ittfId = (string) ($row['ittf_id'] ?? '');

                if (! $ittfId) {
                    $errors[] = 'Missing ITTF ID for player row';

                    continue;
                }

                $existing = Player::where('ittf_id', $ittfId)->first();

                if ($existing) {
                    $existing->update($this->transformPlayer($row));
                    $updated++;
                } else {
                    Player::create(array_merge(
                        $this->transformPlayer($row),
                        ['ittf_id' => $ittfId]
                    ));
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
        $errors = [];

        foreach ($rows as $row) {
            try {
                $matchData = $this->transformMatch($row);

                GameMatch::create($matchData);
                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Error importing match: {$e->getMessage()}";
            }
        }

        return [
            'imported' => $imported,
            'errors' => $errors,
        ];
    }

    private function transformPlayer(array $row): array
    {
        $fullName = $row['name'] ?? ($row['Name'] ?? '');
        [$firstName, $lastName] = $this->parsePlayerName($fullName);

        $countryCode = $row['country'] ?? ($row['Country'] ?? '');
        if (strlen($countryCode) > 2) {
            $countryCode = substr($countryCode, 0, 2);
        }

        return [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'country_code' => $countryCode,
        ];
    }

    private function transformMatch(array $row): array
    {
        return [
            'player_a_id' => null,
            'player_b_id' => null,
            'player_a_sets' => 0,
            'player_b_sets' => 0,
            'match_date' => date('Y-m-d'),
            'round' => $row['round'] ?? '',
            'status' => 'Completed',
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

    private function resolvePlayerId(string $ittfId): ?int
    {
        $player = Player::where('ittf_id', $ittfId)->orWhere('wtt_id', $ittfId)->first();

        return $player?->id;
    }

    private function autoCreatePlayer(array $row): ?int
    {
        $ittfId = (string) ($row['ittf_id'] ?? '');

        if (! $ittfId) {
            return null;
        }

        $fullName = $row['name'] ?? '';
        [$firstName, $lastName] = $this->parsePlayerName($fullName);

        $countryCode = $row['country'] ?? '';
        if (strlen($countryCode) > 2) {
            $countryCode = substr($countryCode, 0, 2);
        }

        $player = Player::create([
            'ittf_id' => $ittfId,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'country' => $row['country'] ?? '',
            'country_code' => $countryCode,
            'world_ranking' => $row['rank_position'] ?? null,
            'rating_points' => $row['rating_points'] ?? null,
        ]);

        return $player->id;
    }

    private function loadImportFile(string $filename): array
    {
        // Support both bare name and full filename
        if (! str_contains($filename, '.json')) {
            $filename .= '.json';
        }

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
