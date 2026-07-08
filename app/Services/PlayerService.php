<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\GameMatch;
use App\Models\Player;
use App\Models\Tournament;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

final class PlayerService
{
    public function getPlayerById(int $id): Player
    {
        return Player::with([
            'rankings' => fn ($q) => $q->latest('ranking_date')->limit(1),
        ])->findOrFail($id);
    }

    public function getPlayerStats(Player $player): array
    {
        $result = GameMatch::completed()
            ->where(fn ($q) => $q
                ->where('player_a_id', $player->id)
                ->orWhere('player_b_id', $player->id)
            )
            ->selectRaw('
                COUNT(*) as total_matches,
                SUM(CASE WHEN winner_id = ? THEN 1 ELSE 0 END) as wins
            ', [$player->id])
            ->first();

        $totalMatches = (int) $result->total_matches;
        $wins = (int) $result->wins;

        return [
            'total_matches' => $totalMatches,
            'wins' => $wins,
            'losses' => $totalMatches - $wins,
            'win_rate' => $totalMatches > 0
                ? round(($wins / $totalMatches) * 100, 1)
                : 0.0,
        ];
    }

    public function getPlayersByGender(string $gender): Collection
    {
        return Player::where('gender', $gender)
            ->orderBy('world_ranking')
            ->get(['id', 'first_name', 'last_name', 'country_code', 'world_ranking', 'rating_points', 'gender']);
    }

    public function getLast7Matches(Player $player): Collection
    {
        return GameMatch::completed()
            ->with(['tournament', 'playerA', 'playerB', 'winner'])
            ->where(fn ($q) => $q
                ->where('player_a_id', $player->id)
                ->orWhere('player_b_id', $player->id)
            )
            ->orderByDesc('match_date')
            ->orderByDesc('id')
            ->limit(7)
            ->get();
    }

    public function getPlayerProfile(Player $player, ?int $year = null, ?int $tournamentId = null): array
    {
        $player->load(['rankings' => fn ($q) => $q->latest('ranking_date')->limit(1)]);

        $stats = $this->getPlayerStats($player);
        $rankingHistory = $this->getRankingHistory($player, 12);
        $matchHistory = $this->getMatchHistory($player, 20, $year, $tournamentId);
        $streak = $this->getCurrentStreak($player);

        $availableYears = GameMatch::completed()
            ->where(fn ($q) => $q
                ->where('player_a_id', $player->id)
                ->orWhere('player_b_id', $player->id)
            )
            ->pluck('match_date')
            ->map(fn ($date) => (int) $date->format('Y'))
            ->unique()
            ->sortDesc()
            ->values();

        $tournaments = Tournament::whereIn('id', function ($q) use ($player) {
            $q->select('tournament_id')
                ->from('matches')
                ->where('status', 'Completed')
                ->where(fn ($sub) => $sub
                    ->where('player_a_id', $player->id)
                    ->orWhere('player_b_id', $player->id)
                );
        })->get(['id', 'name']);

        return [
            'player' => $player,
            'stats' => $stats,
            'rankingHistory' => $rankingHistory,
            'matches' => $matchHistory,
            'streak' => $streak,
            'availableYears' => $availableYears,
            'tournaments' => $tournaments,
            'selectedYear' => $year,
            'selectedTournamentId' => $tournamentId,
        ];
    }

    public function getMatchHistory(Player $player, int $perPage = 20, ?int $year = null, ?int $tournamentId = null): LengthAwarePaginator
    {
        $query = GameMatch::completed()
            ->with(['tournament', 'playerA', 'playerB', 'winner'])
            ->where(fn ($q) => $q
                ->where('player_a_id', $player->id)
                ->orWhere('player_b_id', $player->id)
            );

        if ($year) {
            $query->whereYear('match_date', $year);
        }

        if ($tournamentId) {
            $query->where('tournament_id', $tournamentId);
        }

        return $query->orderByDesc('match_date')
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    public function getRankingHistory(Player $player, int $limit = 12): Collection
    {
        return $player->rankings()
            ->orderByDesc('ranking_date')
            ->limit($limit)
            ->get();
    }

    public function getCurrentStreak(Player $player): array
    {
        $recentMatches = GameMatch::completed()
            ->where(fn ($q) => $q
                ->where('player_a_id', $player->id)
                ->orWhere('player_b_id', $player->id)
            )
            ->orderByDesc('match_date')
            ->orderByDesc('id')
            ->get();

        if ($recentMatches->isEmpty()) {
            return ['type' => null, 'count' => 0];
        }

        $firstResult = $recentMatches->first()->winner_id === $player->id ? 'W' : 'L';
        $count = 0;

        foreach ($recentMatches as $match) {
            $won = $match->winner_id === $player->id;
            $currentResult = $won ? 'W' : 'L';

            if ($currentResult === $firstResult) {
                $count++;
            } else {
                break;
            }
        }

        return ['type' => $firstResult, 'count' => $count];
    }
}
