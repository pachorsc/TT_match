<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\GameMatch;
use App\Models\Player;
use App\Models\Ranking;
use App\Models\Tournament;
use RuntimeException;

class IttfImportService
{
    private string $importPath;

    /** @var array<string, int> Cached tournament IDs by name */
    private array $tournamentCache = [];

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
                    $gender = $this->genderFromRow($row);
                    $playerId = $this->autoCreatePlayer($row, $gender);
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
        $skipped = 0;
        $errors = [];

        foreach ($rows as $row) {
            try {
                // Skip Type B entries (no player_a / player_b)
                if (empty($row['player_a']) || empty($row['player_b'])) {
                    $skipped++;

                    continue;
                }

                // Skip mixed team events (XT) — app only handles same-gender events
                $eventType = $row['event_type'] ?? '';
                if ($eventType === 'XT') {
                    $skipped++;

                    continue;
                }

                $expectedGender = $this->expectedGenderFromEventType($eventType);

                $playerAId = $this->resolveOrCreatePlayer(
                    $row['player_a_name'] ?? '',
                    $row['player_a_country'] ?? '',
                    $row['player_ittf_id'] ?? null,
                    $this->genderFromIttfId($row['player_ittf_id'] ?? null) ?? $expectedGender
                );
                $playerBId = $this->resolveOrCreatePlayer(
                    $row['player_b_name'] ?? '',
                    $row['player_b_country'] ?? '',
                    null,
                    $expectedGender
                );

                if (! $playerAId || ! $playerBId) {
                    $skipped++;

                    continue;
                }

                $winnerId = $this->resolveWinner(
                    $row['winner'] ?? '',
                    $row['player_a_name'] ?? '',
                    $row['player_b_name'] ?? '',
                    $playerAId,
                    $playerBId
                );

                $tournamentName = $this->normalizeTournamentName(
                    $row['tournament'] ?? '',
                    $row['year'] ?? date('Y')
                );

                $tournamentId = $this->resolveTournament(
                    $tournamentName,
                    $row['year'] ?? date('Y')
                );

                $year = $row['year'] ?? date('Y');
                $matchDate = "{$year}-01-01";

                // Sort player IDs so the same match always generates the same key
                // regardless of which player's perspective it was scraped from
                $sortedIds = [$playerAId, $playerBId];
                sort($sortedIds, SORT_NUMERIC);

                $dedupKey = md5(
                    $tournamentId.
                    $sortedIds[0].
                    $sortedIds[1].
                    ($row['event_type'] ?? '').
                    ($row['round'] ?? '').
                    $year
                );

                $existing = GameMatch::where('ittf_id', $dedupKey)->first();

                // Also check for cross-source duplicates (e.g. already imported via WTT)
                if (! $existing) {
                    $existing = $this->findMatchByPlayersRoundTournament(
                        $playerAId, $playerBId, $tournamentId, $row['round'] ?? ''
                    );
                }

                if ($existing) {
                    $skipped++;

                    continue;
                }

                GameMatch::create([
                    'ittf_id' => $dedupKey,
                    'tournament_id' => $tournamentId,
                    'player_a_id' => $playerAId,
                    'player_b_id' => $playerBId,
                    'winner_id' => $winnerId,
                    'player_a_sets' => (int) ($row['player_a_sets'] ?? 0),
                    'player_b_sets' => (int) ($row['player_b_sets'] ?? 0),
                    'match_date' => $matchDate,
                    'round' => $row['round'] ?? '',
                    'status' => 'Completed',
                ]);

                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Error importing match: {$e->getMessage()}";
            }
        }

        return [
            'imported' => $imported,
            'skipped' => $skipped,
            'errors' => $errors,
        ];
    }

    public function importTop100Matches(string $filename): array
    {
        return $this->importMatches($filename);
    }

    private function resolveOrCreatePlayer(string $fullName, string $countryCode = '', ?string $ittfId = null, ?string $gender = null): ?int
    {
        if (empty($fullName)) {
            return null;
        }

        // If we have an ITTF ID, try resolving by it
        if ($ittfId) {
            $player = Player::where('ittf_id', $ittfId)->first();
            if ($player) {
                return $player->id;
            }
        }

        // Parse ITTF name format: "WANG Chuqin" -> surname="WANG", given="Chuqin"
        [$surname, $givenName] = $this->parseIttfName($fullName);

        // Try exact match on surname + given name, with optional gender filter
        $player = Player::where(function ($q) use ($surname, $givenName, $gender) {
            if ($gender) {
                $q->where('gender', $gender);
            }

            $q->where(function ($q2) use ($surname, $givenName) {
                $q2->where(function ($q3) use ($surname, $givenName) {
                    // Standard: last_name=HARIMOTO, first_name=Tomokazu
                    $q3->where('last_name', $surname)->where('first_name', $givenName);
                })->orWhere(function ($q3) use ($surname, $givenName) {
                    // WTT format: first_name="Tomokazu HARIMOTO", last_name=""
                    $q3->where('first_name', 'like', "%{$surname}%")
                        ->where('first_name', 'like', "%{$givenName}%");
                })->orWhere(function ($q3) use ($surname) {
                    // Reversed: first_name="Tomokazu", last_name="" (surname in first_name)
                    $q3->where('first_name', $surname)->whereNull('last_name');
                })->orWhere(function ($q3) use ($surname) {
                    // Surname match only
                    $q3->where('last_name', $surname);
                });
            });
        })->first();
        if ($player) {
            if ($ittfId && ! $player->ittf_id) {
                $player->update(['ittf_id' => $ittfId]);
            }

            return $player->id;
        }

        // Auto-create player if not found
        if ($givenName) {
            $cc = strlen($countryCode) > 2 ? substr($countryCode, 0, 2) : $countryCode;
            $playerData = [
                'ittf_id' => $ittfId,
                'first_name' => $givenName,
                'last_name' => $surname,
                'country_code' => $cc,
                'country' => $countryCode,
                'date_of_birth' => null,
            ];

            if ($gender) {
                $playerData['gender'] = $gender;
            }

            $player = Player::create($playerData);

            return $player->id;
        }

        return null;
    }

    private function genderFromIttfId(?string $ittfId): ?string
    {
        if (! $ittfId) {
            return null;
        }

        $player = Player::where('ittf_id', $ittfId)->first();

        return $player?->gender;
    }

    private function resolveWinner(string $winnerName, string $playerAName, string $playerBName, int $playerAId, int $playerBId): ?int
    {
        if (empty($winnerName)) {
            return null;
        }

        $winnerLower = strtolower($winnerName);

        // Check if winner is player A or B
        $aLower = strtolower($playerAName);
        $bLower = strtolower($playerBName);

        if ($winnerLower === $aLower || str_contains($aLower, $winnerLower) || str_contains($winnerLower, $aLower)) {
            return $playerAId;
        }

        if ($winnerLower === $bLower || str_contains($bLower, $winnerLower) || str_contains($winnerLower, $bLower)) {
            return $playerBId;
        }

        // Try resolving winner as a separate player
        $player = Player::whereRaw('LOWER(first_name) LIKE ?', ["%{$winnerLower}%"])
            ->orWhereRaw('LOWER(last_name) LIKE ?', ["%{$winnerLower}%"])
            ->first();

        return $player?->id;
    }

    /**
     * Parse ITTF name format: "WANG Chuqin" -> ["WANG", "Chuqin"]
     * ITTF format has surname (UPPERCASE) first, then given name(s).
     */
    private function parseIttfName(string $fullName): array
    {
        $parts = preg_split('/\s+/', trim($fullName));

        if (count($parts) <= 1) {
            return [$fullName, ''];
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

        $surname = implode(' ', $surnameParts);
        $givenName = implode(' ', $givenParts);

        if (empty($surname)) {
            $surname = $fullName;
        }

        return [$surname, $givenName];
    }

    private function resolveTournament(string $name, string $year): int
    {
        $cacheKey = $name.'|'.$year;
        if (isset($this->tournamentCache[$cacheKey])) {
            return $this->tournamentCache[$cacheKey];
        }

        $tournament = Tournament::where('name', 'like', "%{$name}%")->first();

        if (! $tournament) {
            $tournament = Tournament::create([
                'name' => $name,
                'location' => '',
                'country' => '',
                'country_code' => '',
                'start_date' => "{$year}-01-01",
                'end_date' => "{$year}-12-31",
            ]);
        }

        $this->tournamentCache[$cacheKey] = $tournament->id;

        return $tournament->id;
    }

    private function transformPlayer(array $row): array
    {
        $fullName = $row['name'] ?? ($row['Name'] ?? '');
        [$firstName, $lastName] = $this->parsePlayerName($fullName);

        $countryCode = $row['country'] ?? ($row['Country'] ?? '');
        if (strlen($countryCode) > 2) {
            $countryCode = substr($countryCode, 0, 2);
        }

        $data = [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'country_code' => $countryCode,
        ];

        // Parse gender and birth_year from details field if available
        $details = $row['details'] ?? '';
        if ($details) {
            if (preg_match('/Gender:\s*(Male|Female)/i', $details, $matches)) {
                $data['gender'] = strtolower($matches[1]) === 'female' ? 'F' : 'M';
            }

            if (preg_match('/Birth Year:\s*(\d{4})/', $details, $matches)) {
                $birthYear = (int) $matches[1];
                if ($birthYear > 1900 && $birthYear < date('Y')) {
                    $data['date_of_birth'] = "{$birthYear}-01-01";
                }
            }
        }

        return $data;
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

    private function autoCreatePlayer(array $row, ?string $gender = null): ?int
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

        $playerData = [
            'ittf_id' => $ittfId,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'country' => $row['country'] ?? '',
            'country_code' => $countryCode,
            'world_ranking' => $row['rank_position'] ?? null,
            'rating_points' => $row['rating_points'] ?? null,
        ];

        if ($gender) {
            $playerData['gender'] = $gender;
        }

        $player = Player::create($playerData);

        return $player->id;
    }

    /**
     * Normalize tournament name by stripping sponsor suffixes and standardizing format.
     */
    private function normalizeTournamentName(string $name, string $year): string
    {
        $normalized = $name;

        // Remove "Presented by ..." and similar sponsor suffixes
        $normalized = preg_replace(
            '/\s+(Presented by|Sponsored by|Powered by|By)\s+.+$/i',
            '',
            $normalized
        );

        // Remove trailing whitespace/punctuation
        $normalized = trim($normalized);
        $normalized = rtrim($normalized, '. ');

        // Normalize "Men's & Women's World Cup" -> "World Cup" for brevity
        $normalized = preg_replace(
            "/ITTF\s+(Men's\s*[&and]\s*Women's\s+)?World\s+Cup/i",
            'ITTF World Cup',
            $normalized
        );

        // Ensure year is present at the end
        if (! str_contains($normalized, $year)) {
            $normalized = trim($normalized)." {$year}";
        }

        return $normalized;
    }

    /**
     * Find an existing match by players, tournament, and round.
     * Used as a fallback dedup check across different import sources (ITTF vs WTT).
     */
    private function findMatchByPlayersRoundTournament(
        int $playerAId,
        int $playerBId,
        int $tournamentId,
        string $round
    ): ?GameMatch {
        return GameMatch::where('tournament_id', $tournamentId)
            ->where('round', $round)
            ->where(function ($q) use ($playerAId, $playerBId) {
                $q->where(function ($q2) use ($playerAId, $playerBId) {
                    $q2->where('player_a_id', $playerAId)
                        ->where('player_b_id', $playerBId);
                })->orWhere(function ($q2) use ($playerAId, $playerBId) {
                    $q2->where('player_a_id', $playerBId)
                        ->where('player_b_id', $playerAId);
                });
            })->first();
    }

    private function genderFromRow(array $row): string
    {
        return ($row['gender'] ?? 'men') === 'women' ? 'F' : 'M';
    }

    private function expectedGenderFromEventType(string $eventType): ?string
    {
        // Extract base gender from event type (MS/WS/MT/WT/MD/WD + youth prefixes)
        $baseType = preg_replace('/^U\d{2}/', '', $eventType);

        return match ($baseType) {
            'MS', 'MT', 'MD' => 'M',
            'WS', 'WT', 'WD' => 'F',
            default => null,
        };
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
