<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Player;

final class VideoController extends Controller
{
    public function __invoke()
    {
        $players = Player::orderBy('world_ranking')
            ->select('id', 'first_name', 'last_name', 'country_code', 'world_ranking', 'rating_points')
            ->get();

        return view('pages.videos', compact('players'));
    }
}
