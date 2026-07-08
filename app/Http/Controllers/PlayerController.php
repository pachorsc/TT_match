<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Player;
use App\Services\PlayerService;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class PlayerController extends Controller
{
    public function __construct(
        private readonly PlayerService $playerService,
    ) {}

    public function __invoke(Request $request, Player $player): View
    {
        $year = $request->query('year');
        $tournamentId = $request->query('tournament_id');

        $data = $this->playerService->getPlayerProfile($player, $year ? (int) $year : null, $tournamentId ? (int) $tournamentId : null);

        return view('pages.player-profile', $data);
    }
}
