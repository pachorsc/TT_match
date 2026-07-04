<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Player;
use App\Models\Ranking;
use Illuminate\Support\Collection;

final class RankingService
{
    public function getCurrentRanking(Player $player): ?Ranking
    {
        return $player->rankings()
            ->orderByDesc('ranking_date')
            ->first();
    }

    public function getRankingHistory(Player $player, int $limit = 10): Collection
    {
        return $player->rankings()
            ->orderByDesc('ranking_date')
            ->limit($limit)
            ->get();
    }
}
