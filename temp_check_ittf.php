<?php

use App\Models\Player;
use Illuminate\Contracts\Console\Kernel;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

$players = Player::whereNotNull('ittf_id')
    ->where('ittf_id', '!=', '')
    ->get(['ittf_id', 'first_name', 'last_name', 'country_code', 'date_of_birth']);

echo 'TOTAL_PLAYERS_WITH_ITTF_ID: '.$players->count().PHP_EOL;
echo '---'.PHP_EOL;

foreach ($players as $p) {
    echo $p->ittf_id.'|'.$p->first_name.' '.$p->last_name.'|'.$p->country_code.'|'.($p->date_of_birth ? $p->date_of_birth->format('Y') : 'N/A').PHP_EOL;
}
