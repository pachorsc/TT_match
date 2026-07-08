<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Player;
use App\Models\Ranking;
use Illuminate\Support\Collection;

final class RankingService
{
    public function getRankingHistory(Player $player, int $limit = 10): Collection
    {
        return $player->rankings()
            ->orderByDesc('ranking_date')
            ->limit($limit)
            ->get();
    }

    public function getCurrentRankings(string $gender, int $limit = 50): Collection
    {
        $latestDate = Ranking::max('ranking_date');

        if (! $latestDate) {
            return collect();
        }

        $twoWeeksAgo = now()->parse($latestDate)->subWeeks(2);

        return Ranking::query()
            ->select(
                'rankings.id',
                'rankings.player_id',
                'rankings.ranking',
                'rankings.rating_points',
                'rankings.ranking_date',
            )
            ->join('players', 'players.id', '=', 'rankings.player_id')
            ->where('players.gender', $gender)
            ->where('rankings.ranking_date', $latestDate)
            ->with('player:id,first_name,last_name,country,country_code')
            ->orderBy('rankings.ranking')
            ->limit($limit)
            ->get()
            ->map(function (Ranking $ranking) use ($twoWeeksAgo) {
                $previousRanking = Ranking::where('player_id', $ranking->player_id)
                    ->where('ranking_date', '<=', $twoWeeksAgo)
                    ->orderByDesc('ranking_date')
                    ->first();

                $ranking->movement = $previousRanking
                    ? $previousRanking->ranking - $ranking->ranking
                    : null;

                return $ranking;
            });
    }
}
