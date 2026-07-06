<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$players = App\Models\Player::whereNotNull('ittf_id')
    ->where('ittf_id', '!=', '')
    ->get(['ittf_id', 'first_name', 'last_name', 'country_code', 'date_of_birth']);

$output = [];
foreach ($players as $p) {
    $fullName = trim($p->first_name . ' ' . $p->last_name);
    $output[] = [
        'ittf_id' => $p->ittf_id,
        'first_name' => $p->first_name,
        'last_name' => $p->last_name,
        'full_name' => $fullName,
        'country_code' => $p->country_code,
        'birth_year' => $p->date_of_birth ? $p->date_of_birth->format('Y') : null,
    ];
}

echo json_encode($output);
