<?php

use App\Http\Controllers\CompareController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MatchDetailController;
use App\Http\Controllers\MatchPreviewController;
use App\Http\Controllers\VideoController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::get('/health', fn () => response()->json(['status' => 'ok']));

Route::get('/matches/{match}', MatchDetailController::class)
    ->name('matches.show');

Route::get('/matches/{match}/preview', MatchPreviewController::class)
    ->name('matches.preview');

Route::get('/compare', CompareController::class)
    ->name('compare');

Route::get('/videos', VideoController::class)
    ->name('videos');
