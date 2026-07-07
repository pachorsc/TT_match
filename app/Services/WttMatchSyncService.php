<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\GameMatch;
use App\Models\MatchSet;
use App\Models\Player;
use App\Models\Tournament;

final class WttMatchSyncService
{
    public function __construct(
        private readonly WttApiClient $apiClient,
    ) {}

    public function sync(int $eventId, ?int $tournamentId = null, ?string $tournamentName = null): array
    {
        $apiData = $this->apiClient->fetchAllMatches($eventId);
        $apiMatches = $apiData['matches'];
        $competition = $apiData['competition'];

        $tournamentName = $tournamentName
            ?? $competition['Name']
            ?? $competition['Title']
            ?? null;

        $tournament = $tournamentId
            ? Tournament::findOrFail($tournamentId)
            : $this->resolveOrCreateTournament($tournamentName, $eventId, $apiMatches);

        $existingByCode = GameMatch::where('tournament_id', $tournament->id)
            ->whereNotNull('ittf_id')
            ->get(['id', 'ittf_id', 'player_a_sets', 'player_b_sets', 'status', 'winner_id'])
            ->keyBy('ittf_id');

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $errors = [];

        foreach ($apiMatches as $match) {
            try {
                $docCode = $match['document_code'] ?? '';
                $existing = $existingByCode->get($docCode);

                $result = $this->processMatch($match, $tournament->id, $existing);

                if ($result === 'created') {
                    $created++;
                } elseif ($result === 'updated') {
                    $updated++;
                } else {
                    $skipped++;
                }
            } catch (\Exception $e) {
                $code = $match['document_code'] ?? 'unknown';
                $errors[] = "{$code}: {$e->getMessage()}";
            }
        }

        return [
            'event_id' => $eventId,
            'tournament' => $tournament->name,
            'total_in_api' => count($apiMatches),
            'existing_in_db' => $existingByCode->count(),
            'created' => $created,
            'updated' => $updated,
            'skipped' => $skipped,
            'errors' => $errors,
        ];
    }

    private function processMatch(array $match, int $tournamentId, ?GameMatch $existing): string
    {
        $playerAIttfId = $match['player_a_ittf_id'] ?? '';
        $playerBIttfId = $match['player_b_ittf_id'] ?? '';
        $docCode = $match['document_code'] ?? '';

        if ($playerAIttfId === '' || $playerBIttfId === '') {
            return 'skipped';
        }

        $playerA = $this->resolvePlayerByWttId($playerAIttfId);
        $playerB = $this->resolvePlayerByWttId($playerBIttfId);

        if ($playerA === null || $playerB === null) {
            return 'skipped';
        }

        if ($playerA->id === $playerB->id) {
            return 'skipped';
        }

        $playerASets = (int) ($match['player_a_sets'] ?? 0);
        $playerBSets = (int) ($match['player_b_sets'] ?? 0);

        $overallScores = $match['overall_scores'] ?? '';

        if ($playerASets === 0 && $playerBSets === 0 && $overallScores !== '') {
            if (preg_match('/(\d+)\s*[-:]\s*(\d+)/', $overallScores, $m)) {
                $playerASets = (int) $m[1];
                $playerBSets = (int) $m[2];
            }
        }

        $winnerId = $this->resolveWinner(
            $match['winner_ittf_id'] ?? '',
            $playerA,
            $playerB,
            $playerASets,
            $playerBSets,
        );

        $matchDate = $this->parseDate($match['date'] ?? '');
        $round = $this->extractRound($match['sub_event'] ?? '');
        $status = ($match['completed'] ?? false) ? 'Completed' : 'Scheduled';

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

        if ($existing !== null) {
            $changed = (
                $existing->player_a_sets !== $playerASets
                || $existing->player_b_sets !== $playerBSets
                || $existing->status !== $status
                || $existing->winner_id !== $winnerId
            );

            if (! $changed) {
                return 'skipped';
            }

            $existing->update($matchData);
            $this->syncMatchSets($existing->id, $match['game_scores'] ?? '');

            return 'updated';
        }

        $gameMatch = GameMatch::create(
            array_merge($matchData, ['ittf_id' => $docCode]),
        );

        $this->syncMatchSets($gameMatch->id, $match['game_scores'] ?? '');

        return 'created';
    }

    private function syncMatchSets(int $matchId, string $gameScores): void
    {
        if ($gameScores === '') {
            return;
        }

        MatchSet::where('match_id', $matchId)->delete();

        $games = array_filter(explode(',', $gameScores));
        $setNumber = 1;

        foreach ($games as $game) {
            $game = trim($game);
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
        if ($ittfId === '') {
            return null;
        }

        return Player::where('wtt_id', $ittfId)->first();
    }

    private function resolveWinner(
        string $winnerIttfId,
        Player $playerA,
        Player $playerB,
        int $playerASets,
        int $playerBSets,
    ): ?int {
        if ($winnerIttfId !== '') {
            $winnerPlayer = $this->resolvePlayerByWttId($winnerIttfId);

            if ($winnerPlayer !== null) {
                return $winnerPlayer->id;
            }
        }

        if ($playerASets > $playerBSets) {
            return $playerA->id;
        }

        if ($playerBSets > $playerASets) {
            return $playerB->id;
        }

        return null;
    }

    private function resolveOrCreateTournament(?string $name, int $eventId, array $apiMatches): Tournament
    {
        // Check if any of these matches already exist in the DB
        $docCodes = array_column($apiMatches, 'document_code');
        $existingMatch = GameMatch::whereIn('ittf_id', $docCodes)->first();

        if ($existingMatch !== null) {
            $tournament = $existingMatch->tournament;

            // Update name if provided and different
            if ($name && $tournament->name !== $name) {
                $tournament->update(['name' => $name]);
            }

            return $tournament;
        }

        $name = $name ?? "WTT Event {$eventId}";

        return Tournament::create([
            'name' => $name,
            'location' => '',
            'country' => '',
            'country_code' => '',
            'start_date' => now()->subDays(7),
            'end_date' => now()->addDays(14),
            'category' => 'WTT Grand Smash',
        ]);
    }

    private function parseDate(string $dateStr): string
    {
        if ($dateStr === '') {
            return now()->format('Y-m-d');
        }

        $parsed = date_create_from_format('Y-m-d\TH:i:s', $dateStr);
        if ($parsed !== false) {
            return $parsed->format('Y-m-d');
        }

        $parsed = date_create($dateStr);
        if ($parsed !== false) {
            return $parsed->format('Y-m-d');
        }

        return now()->format('Y-m-d');
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
}
