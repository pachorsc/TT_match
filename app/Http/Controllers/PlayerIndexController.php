<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\PlayerService;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class PlayerIndexController extends Controller
{
    public function __construct(
        private readonly PlayerService $playerService,
    ) {}

    public function __invoke(Request $request): View
    {
        $gender = in_array($request->query('gender'), ['M', 'F'], true)
            ? $request->query('gender')
            : 'M';

        $players = $this->playerService->getPlayersByGender($gender);

        return view('pages.players-index', compact('gender', 'players'));
    }
}
