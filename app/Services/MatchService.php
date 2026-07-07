<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\GameMatch;
use Illuminate\Support\Carbon;

final class MatchService
{
    public function getMatchById(int $id): GameMatch
    {
        return GameMatch::with([
            'tournament',
            'playerA',
            'playerB',
            'winner',
            'sets',
        ])->findOrFail($id);
    }

    public function getHeadToHead(int $playerAId, int $playerBId, int $years = 2): array
    {
        $cutoffYear = Carbon::now()->subYears($years)->year;

        // Use first day of cutoff year since ITTF matches use YYYY-01-01 dates
        $cutoffDate = Carbon::createFromDate($cutoffYear, 1, 1)->startOfDay();

        $matches = GameMatch::completed()
            ->with(['tournament', 'playerA', 'playerB', 'winner', 'sets'])
            ->betweenPlayers($playerAId, $playerBId)
            ->where('match_date', '>=', $cutoffDate)
            ->orderByDesc('match_date')
            ->orderByDesc('id')
            ->get();

        $playerAWins = $matches->where('winner_id', $playerAId)->count();
        $playerBWins = $matches->where('winner_id', $playerBId)->count();

        return [
            'total_matches' => $matches->count(),
            'player_a_wins' => $playerAWins,
            'player_b_wins' => $playerBWins,
            'matches' => $matches,
        ];
    }
}
