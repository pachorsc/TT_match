<?php

use App\Http\Controllers\Api\PlayerVideosController;
use Illuminate\Support\Facades\Route;

Route::get('/players/{player}/videos', PlayerVideosController::class)
    ->middleware('throttle:30,1')
    ->name('api.players.videos');
