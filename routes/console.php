<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('wtt:sync-matches 3242', ['--name' => 'US Smash 2026'])
    ->hourly()
    ->withoutOverlapping()
    ->runInBackground();

Schedule::command('wtt:import-ranking', ['--limit' => 100, '--gender' => 'men'])
    ->weeklyOn(2, '6:00')
    ->withoutOverlapping()
    ->runInBackground();

Schedule::command('wtt:import-ranking', ['--limit' => 100, '--gender' => 'women'])
    ->weeklyOn(2, '6:15')
    ->withoutOverlapping()
    ->runInBackground();
