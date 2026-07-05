<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\GameMatch;
use App\Models\Player;

final class HomeController extends Controller
{
    public function __invoke()
    {
        $matches = GameMatch::completed()
            ->with(['tournament', 'playerA', 'playerB', 'winner'])
            ->orderByDesc('match_date')
            ->limit(30)
            ->get();

        $grouped = $matches->groupBy(fn ($match) => $match->tournament_id);

        $tournaments = $matches->pluck('tournament')->unique('id')->sortByDesc('start_date');

        $totalMatches = $matches->count();
        $totalTournaments = $tournaments->count();
        $totalPlayers = Player::count();

        return view('welcome', compact(
            'grouped',
            'tournaments',
            'totalMatches',
            'totalTournaments',
            'totalPlayers',
        ));
    }
}
