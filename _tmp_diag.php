<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
$results = [];
$results['total_players'] = App\Models\Player::count();
$results['players_with_ittf'] = App\Models\Player::whereNotNull('ittf_id')->count();
$results['players_with_dob'] = App\Models\Player::whereNotNull('date_of_birth')->count();
$results['total_matches'] = App\Models\GameMatch::count();
$results['total_tournaments'] = App\Models\Tournament::count();
$results['total_rankings'] = App\Models\Ranking::count();
$results['total_news'] = App\Models\News::count();
// Check for any players with ittf_id but no DOB
$results['players_with_ittf_no_dob'] = App\Models\Player::whereNotNull('ittf_id')->whereNull('date_of_birth')->count();
// Check a sample of corrected players have matches
$corrected_ids = ['133835','211037','135039','134356','118893','133340','122842','100868','117463','116874','117455','101836','133713'];
$with_matches = 0;
foreach ($corrected_ids as $id) {
    $p = App\Models\Player::where('ittf_id', $id)->first();
    if ($p) {
        $count = App\Models\GameMatch::where('player_a_id', $p->id)->orWhere('player_b_id', $p->id)->count();
        if ($count > 0) $with_matches++;
    }
}
$results['corrected_with_matches'] = $with_matches;
echo json_encode($results, JSON_PRETTY_PRINT);
