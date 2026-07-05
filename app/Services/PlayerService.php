<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\GameMatch;
use App\Models\Player;
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
        $totalMatches = GameMatch::completed()
            ->where(fn ($q) => $q
                ->where('player_a_id', $player->id)
                ->orWhere('player_b_id', $player->id)
            )
            ->count();

        $wins = $player->wonMatches()->completed()->count();

        $winRate = $totalMatches > 0
            ? round(($wins / $totalMatches) * 100, 1)
            : 0.0;

        return [
            'total_matches' => $totalMatches,
            'wins' => $wins,
            'losses' => $totalMatches - $wins,
            'win_rate' => $winRate,
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
            ->with(['tournament', 'playerA', 'playerB', 'winner', 'sets'])
            ->where(fn ($q) => $q
                ->where('player_a_id', $player->id)
                ->orWhere('player_b_id', $player->id)
            )
            ->orderByDesc('match_date')
            ->limit(7)
            ->get();
    }
}
