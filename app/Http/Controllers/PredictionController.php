<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Player;
use App\Services\MatchService;
use App\Services\NewsService;
use App\Services\PlayerService;
use App\Services\RankingService;
use Illuminate\Http\Request;

final class PredictionController extends Controller
{
    public function __construct(
        private readonly PlayerService $playerService,
        private readonly MatchService $matchService,
        private readonly NewsService $newsService,
        private readonly RankingService $rankingService,
    ) {}

    public function __invoke(Request $request)
    {
        $gender = in_array($request->query('gender'), ['M', 'F'], true)
            ? $request->query('gender')
            : 'M';

        $playerAId = $request->query('player_a');
        $playerBId = $request->query('player_b');

        $players = $this->playerService->getPlayersByGender($gender);

        $data = [
            'gender' => $gender,
            'players' => $players,
            'playerA' => null,
            'playerB' => null,
            'playerAData' => null,
            'playerBData' => null,
            'headToHead' => null,
            'news' => null,
        ];

        if ($playerAId && $playerBId && $playerAId !== $playerBId) {
            $playerA = $this->playerService->getPlayerById((int) $playerAId);
            $playerB = $this->playerService->getPlayerById((int) $playerBId);

            $playerAStats = $this->playerService->getPlayerStats($playerA);
            $playerBStats = $this->playerService->getPlayerStats($playerB);

            $playerALast7 = $this->playerService->getLast7Matches($playerA);
            $playerBLast7 = $this->playerService->getLast7Matches($playerB);

            $headToHead = $this->matchService->getHeadToHead($playerA->id, $playerB->id);

            $news = $this->newsService->getLatestNews(5);

            $data['playerA'] = $playerA;
            $data['playerB'] = $playerB;
            $data['playerAData'] = [
                'player' => $playerA,
                'stats' => $playerAStats,
                'last7' => $playerALast7,
                'rankingMovement' => $this->getRankingMovement($playerA),
            ];
            $data['playerBData'] = [
                'player' => $playerB,
                'stats' => $playerBStats,
                'last7' => $playerBLast7,
                'rankingMovement' => $this->getRankingMovement($playerB),
            ];
            $data['headToHead'] = $headToHead;
            $data['news'] = $news;
        }

        return view('pages.predictions', $data);
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
