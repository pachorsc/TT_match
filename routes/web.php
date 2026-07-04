<?php

use App\Http\Controllers\MatchPreviewController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/health', fn () => response()->json(['status' => 'ok']));

Route::get('/matches/{match}/preview', MatchPreviewController::class)
    ->name('matches.preview');
