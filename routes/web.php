<?php

use App\Http\Controllers\MatchPreviewController;
use App\Models\GameMatch;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $matches = GameMatch::completed()
        ->with(['tournament', 'playerA', 'playerB'])
        ->orderByDesc('match_date')
        ->limit(20)
        ->get();

    return view('welcome', ['matches' => $matches]);
});

Route::get('/health', fn () => response()->json(['status' => 'ok']));

Route::get('/matches/{match}/preview', MatchPreviewController::class)
    ->name('matches.preview');
