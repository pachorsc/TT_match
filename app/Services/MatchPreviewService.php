<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\GameMatch;
use App\Models\Player;

final class MatchPreviewService
{
    public function __construct(
        private readonly PlayerService $playerService,
        private readonly MatchService $matchService,
        private readonly RankingService $rankingService,
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

        $playerARankingMovement = $this->getRankingMovement($playerA);
        $playerBRankingMovement = $this->getRankingMovement($playerB);

        return [
            'match' => $match,
            'playerA' => [
                'player' => $playerA,
                'stats' => $playerAStats,
                'last7' => $playerALast7,
                'rankingMovement' => $playerARankingMovement,
            ],
            'playerB' => [
                'player' => $playerB,
                'stats' => $playerBStats,
                'last7' => $playerBLast7,
                'rankingMovement' => $playerBRankingMovement,
            ],
            'headToHead' => $headToHead,
            'tournament' => $match->tournament,
        ];
    }

    private function getRankingMovement(Player $player): ?int
    {
        $history = $this->rankingService->getRankingHistory($player, 2);

        if ($history->count() < 2) {
            return null;
        }

        $previous = $history->last()->ranking;
        $current = $history->first()->ranking;

        return $previous - $current;
    }
}
