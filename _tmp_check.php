<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$match = App\Models\GameMatch::whereHas('playerA', function($q) { $q->whereNotNull('ittf_id'); })
    ->whereHas('playerB', function($q) { $q->whereNotNull('ittf_id'); })
    ->with(['playerA', 'playerB', 'tournament'])
    ->first();

if ($match) {
    echo "Match ID: {$match->id}\n";
    echo "Player A: {$match->playerA->first_name} {$match->playerA->last_name} (ITTF: {$match->playerA->ittf_id})\n";
    echo "Player B: {$match->playerB->first_name} {$match->playerB->last_name} (ITTF: {$match->playerB->ittf_id})\n";
    echo "Tournament: {$match->tournament->name}\n";
} else {
    echo "No match found with both players having ITTF IDs\n";
}
