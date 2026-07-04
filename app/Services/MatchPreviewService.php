<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\GameMatch;

final class MatchPreviewService
{
    public function __construct(
        private readonly PlayerService $playerService,
        private readonly MatchService $matchService,
        private readonly NewsService $newsService,
    ) {}

    public function getPreviewData(GameMatch $match): array
    {
        $playerA = $match->playerA;
        $playerB = $match->playerB;

        $playerAStats = $this->playerService->getPlayerStats($playerA);
        $playerBStats = $this->playerService->getPlayerStats($playerB);

        $playerALast7 = $this->playerService->getLast7Matches($playerA);
        $playerBLast7 = $this->playerService->getLast7Matches($playerB);

        $headToHead = $this->matchService->getHeadToHead($playerA->id, $playerB->id);

        $news = $this->newsService->getLatestNews(5);

        return [
            'match' => $match,
            'playerA' => [
                'player' => $playerA,
                'stats' => $playerAStats,
                'last7' => $playerALast7,
            ],
            'playerB' => [
                'player' => $playerB,
                'stats' => $playerBStats,
                'last7' => $playerBLast7,
            ],
            'headToHead' => $headToHead,
            'tournament' => $match->tournament,
            'news' => $news,
        ];
    }
}
