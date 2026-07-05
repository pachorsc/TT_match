<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\GameMatch;
use App\Models\Player;
use App\Models\Tournament;

final class HomeController extends Controller
{
    public function __invoke()
    {
        $totalMatches = GameMatch::completed()->count();
        $totalTournaments = Tournament::count();
        $totalPlayers = Player::count();

        return view('welcome', compact(
            'totalMatches',
            'totalTournaments',
            'totalPlayers',
        ));
    }
}
