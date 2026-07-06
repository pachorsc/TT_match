<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$players = App\Models\Player::whereNotNull('ittf_id')
    ->where('ittf_id', '!=', '')
    ->take(3)
    ->get(['ittf_id', 'first_name', 'last_name', 'country_code']);

echo json_encode($players->toArray(), JSON_PRETTY_PRINT) . PHP_EOL;
