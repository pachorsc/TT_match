<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\MatchService;
use Illuminate\View\View;

final class MatchDetailController extends Controller
{
    public function __construct(
        private readonly MatchService $matchService,
    ) {}

    public function __invoke(int $match): View
    {
        $match = $this->matchService->getMatchById($match);

        return view('pages.match-detail', [
            'match' => $match,
            'playerA' => $match->playerA,
            'playerB' => $match->playerB,
            'winner' => $match->winner,
            'sets' => $match->sets()->orderBy('set_number')->get(),
            'tournament' => $match->tournament,
        ]);
    }
}
