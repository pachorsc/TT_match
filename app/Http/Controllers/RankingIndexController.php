<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\RankingService;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class RankingIndexController extends Controller
{
    public function __construct(
        private readonly RankingService $rankingService,
    ) {}

    public function __invoke(Request $request): View
    {
        $gender = in_array($request->query('gender'), ['M', 'F'], true)
            ? $request->query('gender')
            : 'M';

        $rankings = $this->rankingService->getCurrentRankings($gender);

        return view('pages.rankings', compact('gender', 'rankings'));
    }
}
