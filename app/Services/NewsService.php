<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\News;
use App\Models\Player;
use Illuminate\Support\Collection;

final class NewsService
{
    public function getLatestNews(int $limit = 10): Collection
    {
        return News::with('player')
            ->latest('published_at')
            ->limit($limit)
            ->get();
    }

    public function getNewsByPlayer(Player $player, int $limit = 5): Collection
    {
        return News::where('player_id', $player->id)
            ->latest('published_at')
            ->limit($limit)
            ->get();
    }
}
