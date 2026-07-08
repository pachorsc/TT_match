<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\GameMatch;
use App\Models\Player;

final class PlayerStatsService
{
    public function __construct(
        private readonly PlayerService $playerService,
    ) {}

    public function getAdvancedStats(Player $playerA, Player $playerB): array
    {
        return [
            'winRateByRound' => $this->getWinRateByRound($playerA, $playerB),
            'winRateByFormat' => $this->getWinRateByFormat($playerA, $playerB),
            'setDifferential' => $this->getSetDifferential($playerA, $playerB),
            'winRateVsRankRange' => $this->getWinRateVsRankRange($playerA, $playerB),
            'recentForm' => $this->getRecentForm($playerA, $playerB),
            'streaks' => [
                'playerA' => $this->playerService->getCurrentStreak($playerA),
                'playerB' => $this->playerService->getCurrentStreak($playerB),
            ],
        ];
    }

    public function getWinRateByRound(Player $playerA, Player $playerB): array
    {
        $rounds = ['Final', 'Semifinal', 'Quarterfinal', 'Round of 16', 'Round of 32'];

        $results = [];
        foreach ($rounds as $round) {
            foreach ([$playerA, $playerB] as $key => $player) {
                $matches = GameMatch::completed()
                    ->where('round', $round)
                    ->where(fn ($q) => $q
                        ->where('player_a_id', $player->id)
                        ->orWhere('player_b_id', $player->id)
                    )
                    ->get();

                $total = $matches->count();
                $wins = $matches->where('winner_id', $player->id)->count();

                $results[$round][$key === 0 ? 'playerA' : 'playerB'] = [
                    'wins' => $wins,
                    'losses' => $total - $wins,
                    'total' => $total,
                    'win_rate' => $total > 0 ? round(($wins / $total) * 100, 1) : 0.0,
                ];
            }
        }

        return $results;
    }

    public function getWinRateByFormat(Player $playerA, Player $playerB): array
    {
        $formats = [
            'BO5' => ['min' => 3, 'max' => 5],
            'BO7' => ['min' => 4, 'max' => 7],
        ];

        $results = [];
        foreach ($formats as $label => $range) {
            foreach ([$playerA, $playerB] as $key => $player) {
                $matches = GameMatch::completed()
                    ->where(fn ($q) => $q
                        ->where('player_a_id', $player->id)
                        ->orWhere('player_b_id', $player->id)
                    )
                    ->whereRaw('(player_a_sets + player_b_sets) BETWEEN ? AND ?', [$range['min'], $range['max']])
                    ->get();

                $total = $matches->count();
                $wins = $matches->where('winner_id', $player->id)->count();

                $results[$label][$key === 0 ? 'playerA' : 'playerB'] = [
                    'wins' => $wins,
                    'losses' => $total - $wins,
                    'total' => $total,
                    'win_rate' => $total > 0 ? round(($wins / $total) * 100, 1) : 0.0,
                ];
            }
        }

        return $results;
    }

    public function getSetDifferential(Player $playerA, Player $playerB): array
    {
        $results = [];

        foreach ([$playerA, $playerB] as $key => $player) {
            $matches = GameMatch::completed()
                ->where(fn ($q) => $q
                    ->where('player_a_id', $player->id)
                    ->orWhere('player_b_id', $player->id)
                )
                ->get();

            $total = $matches->count();

            if ($total === 0) {
                $results[$key === 0 ? 'playerA' : 'playerB'] = [
                    'avg_differential' => 0.0,
                    'total_matches' => 0,
                ];

                continue;
            }

            $totalDiff = $matches->sum(function ($match) use ($player) {
                $isPlayerA = $match->player_a_id === $player->id;

                return $isPlayerA
                    ? ($match->player_a_sets - $match->player_b_sets)
                    : ($match->player_b_sets - $match->player_a_sets);
            });

            $results[$key === 0 ? 'playerA' : 'playerB'] = [
                'avg_differential' => round($totalDiff / $total, 2),
                'total_matches' => $total,
            ];
        }

        return $results;
    }

    public function getWinRateVsRankRange(Player $playerA, Player $playerB): array
    {
        $ranges = [
            'Top 10' => [1, 10],
            'Top 11-25' => [11, 25],
            'Top 26-50' => [26, 50],
            'Top 51-100' => [51, 100],
            '100+' => [101, 9999],
        ];

        $results = [];
        foreach ($ranges as $label => [$min, $max]) {
            foreach ([$playerA, $playerB] as $key => $player) {
                $matches = GameMatch::completed()
                    ->where(fn ($q) => $q
                        ->where('player_a_id', $player->id)
                        ->orWhere('player_b_id', $player->id)
                    )
                    ->whereExists(function ($q) use ($player, $min, $max) {
                        $q->select('id')
                            ->from('players as opp')
                            ->where('opp.id', '!=', $player->id)
                            ->whereBetween('opp.world_ranking', [$min, $max])
                            ->where(function ($sub) {
                                $sub->whereColumn('opp.id', 'matches.player_a_id')
                                    ->orWhereColumn('opp.id', 'matches.player_b_id');
                            });
                    })
                    ->get();

                $total = $matches->count();
                $wins = $matches->where('winner_id', $player->id)->count();

                $results[$label][$key === 0 ? 'playerA' : 'playerB'] = [
                    'wins' => $wins,
                    'losses' => $total - $wins,
                    'total' => $total,
                    'win_rate' => $total > 0 ? round(($wins / $total) * 100, 1) : 0.0,
                ];
            }
        }

        return $results;
    }

    public function getRecentForm(Player $playerA, Player $playerB, int $lastN = 10): array
    {
        $results = [];

        foreach ([$playerA, $playerB] as $key => $player) {
            $matches = GameMatch::completed()
                ->with('tournament')
                ->where(fn ($q) => $q
                    ->where('player_a_id', $player->id)
                    ->orWhere('player_b_id', $player->id)
                )
                ->orderByDesc('match_date')
                ->orderByDesc('id')
                ->limit($lastN)
                ->get();

            $wins = $matches->where('winner_id', $player->id)->count();
            $total = $matches->count();

            $results[$key === 0 ? 'playerA' : 'playerB'] = [
                'wins' => $wins,
                'losses' => $total - $wins,
                'total' => $total,
                'win_rate' => $total > 0 ? round(($wins / $total) * 100, 1) : 0.0,
                'matches' => $matches,
            ];
        }

        return $results;
    }
}
