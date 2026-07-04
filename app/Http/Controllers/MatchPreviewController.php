<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\MatchPreviewService;
use App\Services\MatchService;

final class MatchPreviewController extends Controller
{
    public function __construct(
        private readonly MatchPreviewService $matchPreviewService,
        private readonly MatchService $matchService,
    ) {}

    public function __invoke(int $match)
    {
        $match = $this->matchService->getMatchById($match);

        $previewData = $this->matchPreviewService->getPreviewData($match);

        return view('pages.match-preview', $previewData);
    }
}
