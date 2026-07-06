<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Models\Player;
use App\Services\YouTubeService;
use Illuminate\Http\JsonResponse;

final class PlayerVideosController
{
    public function __construct(
        private readonly YouTubeService $youTubeService,
    ) {}

    public function __invoke(Player $player): JsonResponse
    {
        $videos = $this->youTubeService->getPlayerVideos($player);

        return response()->json([
            'player' => [
                'id' => $player->id,
                'full_name' => $player->full_name,
            ],
            'videos' => $videos,
        ]);
    }
}
