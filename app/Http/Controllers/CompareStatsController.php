<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\PlayerService;
use App\Services\PlayerStatsService;
use Illuminate\Database\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class CompareStatsController extends Controller
{
    public function __construct(
        private readonly PlayerService $playerService,
        private readonly PlayerStatsService $playerStatsService,
    ) {}

    public function __invoke(Request $request): View
    {
        $gender = in_array($request->query('gender'), ['M', 'F'], true)
            ? $request->query('gender')
            : 'M';

        $players = $this->playerService->getPlayersByGender($gender);

        $data = [
            'gender' => $gender,
            'players' => $players,
            'playerA' => null,
            'playerB' => null,
            'stats' => null,
        ];

        $playerAId = $request->query('player_a');
        $playerBId = $request->query('player_b');

        if ($playerAId && $playerBId && $playerAId !== $playerBId) {
            try {
                $playerA = $this->playerService->getPlayerById((int) $playerAId);
                $playerB = $this->playerService->getPlayerById((int) $playerBId);
            } catch (ModelNotFoundException) {
                return view('pages.compare-stats', $data);
            }

            $playerAStats = $this->playerService->getPlayerStats($playerA);
            $playerBStats = $this->playerService->getPlayerStats($playerB);

            $advancedStats = $this->playerStatsService->getAdvancedStats($playerA, $playerB);

            $data['playerA'] = $playerA;
            $data['playerB'] = $playerB;
            $data['playerAStats'] = $playerAStats;
            $data['playerBStats'] = $playerBStats;
            $data['stats'] = $advancedStats;
        }

        return view('pages.compare-stats', $data);
    }
}
