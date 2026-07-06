<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Player;
use App\Models\Ranking;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ImportWttRanking extends Command
{
    protected $signature = 'wtt:import-ranking {--limit=100 : Number of players to import} {--gender=men : Gender (men|women)}';

    protected $description = 'Scrape top rankings from World Table Tennis and import into the database';

    private const API_BASE = 'https://wttcmsapigateway-new.azure-api.net/internalttu/RankingsCurrentWeek/CurrentWeek/GetRankingIndividuals';

    private const API_KEYS = [
        'apikey' => 'REPLACED_WITH_ENV_VAR',
        'secapimkey' => 'REPLACED_WITH_ENV_VAR',
    ];

    private const COUNTRY_MAP = [
        'CHN' => ['code' => 'CN', 'name' => 'China'],
        'JPN' => ['code' => 'JP', 'name' => 'Japan'],
        'KOR' => ['code' => 'KR', 'name' => 'South Korea'],
        'GER' => ['code' => 'DE', 'name' => 'Germany'],
        'SWE' => ['code' => 'SE', 'name' => 'Sweden'],
        'FRA' => ['code' => 'FR', 'name' => 'France'],
        'GBR' => ['code' => 'GB', 'name' => 'Great Britain'],
        'ITA' => ['code' => 'IT', 'name' => 'Italy'],
        'BRA' => ['code' => 'BR', 'name' => 'Brazil'],
        'USA' => ['code' => 'US', 'name' => 'United States'],
        'TPE' => ['code' => 'TW', 'name' => 'Chinese Taipei'],
        'HKG' => ['code' => 'HK', 'name' => 'Hong Kong'],
        'SIN' => ['code' => 'SG', 'name' => 'Singapore'],
        'IND' => ['code' => 'IN', 'name' => 'India'],
        'RUS' => ['code' => 'RU', 'name' => 'Russia'],
        'POL' => ['code' => 'PL', 'name' => 'Poland'],
        'CZE' => ['code' => 'CZ', 'name' => 'Czech Republic'],
        'AUT' => ['code' => 'AT', 'name' => 'Austria'],
        'NED' => ['code' => 'NL', 'name' => 'Netherlands'],
        'BEL' => ['code' => 'BE', 'name' => 'Belgium'],
        'ESP' => ['code' => 'ES', 'name' => 'Spain'],
        'POR' => ['code' => 'PT', 'name' => 'Portugal'],
        'CRO' => ['code' => 'HR', 'name' => 'Croatia'],
        'SRB' => ['code' => 'RS', 'name' => 'Serbia'],
        'ROU' => ['code' => 'RO', 'name' => 'Romania'],
        'BUL' => ['code' => 'BG', 'name' => 'Bulgaria'],
        'HUN' => ['code' => 'HU', 'name' => 'Hungary'],
        'GRE' => ['code' => 'GR', 'name' => 'Greece'],
        'TUR' => ['code' => 'TR', 'name' => 'Turkey'],
        'EGY' => ['code' => 'EG', 'name' => 'Egypt'],
        'NGR' => ['code' => 'NG', 'name' => 'Nigeria'],
        'ARG' => ['code' => 'AR', 'name' => 'Argentina'],
        'CHI' => ['code' => 'CL', 'name' => 'Chile'],
        'COL' => ['code' => 'CO', 'name' => 'Colombia'],
        'MEX' => ['code' => 'MX', 'name' => 'Mexico'],
        'AUS' => ['code' => 'AU', 'name' => 'Australia'],
        'NZL' => ['code' => 'NZ', 'name' => 'New Zealand'],
        'CAN' => ['code' => 'CA', 'name' => 'Canada'],
        'UKR' => ['code' => 'UA', 'name' => 'Ukraine'],
        'BLR' => ['code' => 'BY', 'name' => 'Belarus'],
        'KAZ' => ['code' => 'KZ', 'name' => 'Kazakhstan'],
        'UZB' => ['code' => 'UZ', 'name' => 'Uzbekistan'],
        'IRI' => ['code' => 'IR', 'name' => 'Iran'],
        'IRQ' => ['code' => 'IQ', 'name' => 'Iraq'],
        'LBN' => ['code' => 'LB', 'name' => 'Lebanon'],
        'THA' => ['code' => 'TH', 'name' => 'Thailand'],
        'VIE' => ['code' => 'VN', 'name' => 'Vietnam'],
        'MAS' => ['code' => 'MY', 'name' => 'Malaysia'],
        'INA' => ['code' => 'ID', 'name' => 'Indonesia'],
        'PHI' => ['code' => 'PH', 'name' => 'Philippines'],
        'PUR' => ['code' => 'PR', 'name' => 'Puerto Rico'],
        'DOM' => ['code' => 'DO', 'name' => 'Dominican Republic'],
        'CUB' => ['code' => 'CU', 'name' => 'Cuba'],
        'PRK' => ['code' => 'KP', 'name' => 'North Korea'],
        'LUX' => ['code' => 'LU', 'name' => 'Luxembourg'],
        'SUI' => ['code' => 'CH', 'name' => 'Switzerland'],
        'MRI' => ['code' => 'MU', 'name' => 'Mauritius'],
        'SEN' => ['code' => 'SN', 'name' => 'Senegal'],
        'ALG' => ['code' => 'DZ', 'name' => 'Algeria'],
        'TUN' => ['code' => 'TN', 'name' => 'Tunisia'],
        'MAR' => ['code' => 'MA', 'name' => 'Morocco'],
        'CMR' => ['code' => 'CM', 'name' => 'Cameroon'],
        'GHA' => ['code' => 'GH', 'name' => 'Ghana'],
        'RSA' => ['code' => 'ZA', 'name' => 'South Africa'],
        'IRL' => ['code' => 'IE', 'name' => 'Ireland'],
        'NOR' => ['code' => 'NO', 'name' => 'Norway'],
        'DEN' => ['code' => 'DK', 'name' => 'Denmark'],
        'FIN' => ['code' => 'FI', 'name' => 'Finland'],
        'SVK' => ['code' => 'SK', 'name' => 'Slovakia'],
        'SLO' => ['code' => 'SI', 'name' => 'Slovenia'],
        'LTU' => ['code' => 'LT', 'name' => 'Lithuania'],
        'LAT' => ['code' => 'LV', 'name' => 'Latvia'],
        'EST' => ['code' => 'EE', 'name' => 'Estonia'],
    ];

    public function handle(): int
    {
        $limit = (int) $this->option('limit');
        $gender = $this->option('gender');
        $subEventCode = $gender === 'women' ? 'WS' : 'MS';
        $genderCode = $gender === 'women' ? 'F' : 'M';

        $this->info("Fetching top {$limit} {$gender} rankings from WTT API Gateway...");
        $this->newLine();

        try {
            $data = $this->fetchRankings($subEventCode, $limit);
        } catch (\Exception $e) {
            $this->error("Failed to fetch rankings: {$e->getMessage()}");
            Log::error('WTT ranking fetch failed', ['error' => $e->getMessage()]);

            return Command::FAILURE;
        }

        $this->info('Fetched '.count($data).' players from WTT API');
        $this->newLine();

        $stats = ['imported' => 0, 'updated' => 0, 'rankings_created' => 0, 'errors' => []];
        $rankingDate = now()->toDateString();

        DB::beginTransaction();

        try {
            foreach ($data as $entry) {
                try {
                    $this->processPlayer($entry, $rankingDate, $genderCode, $stats);
                } catch (\Exception $e) {
                    $stats['errors'][] = "Error processing player: {$e->getMessage()}";
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Transaction failed: {$e->getMessage()}");
            Log::error('WTT import transaction failed', ['error' => $e->getMessage()]);

            return Command::FAILURE;
        }

        $this->displayResult($stats);

        return Command::SUCCESS;
    }

    private function fetchRankings(string $subEventCode, int $limit): array
    {
        $allResults = [];
        $batchSize = 100;
        $startRank = 1;

        while ($startRank <= $limit) {
            $endRank = min($startRank + $batchSize - 1, $limit);

            $url = self::API_BASE.'?CategoryCode=SEN&SubEventCode='.$subEventCode.'&StartRank='.$startRank.'&EndRank='.$endRank.'&q=1';

            $response = Http::withHeaders(array_merge(self::API_KEYS, [
                'Origin' => 'https://www.worldtabletennis.com',
                'Referer' => 'https://www.worldtabletennis.com/',
                'Accept-Language' => 'en-US,en;q=0.9',
            ]))->timeout(30)->get($url);

            if (! $response->successful()) {
                throw new \RuntimeException("HTTP {$response->status()} at ranks {$startRank}-{$endRank}");
            }

            $json = $response->json();

            if (! isset($json['Result'])) {
                throw new \RuntimeException('Invalid API response: missing Result key');
            }

            $results = $json['Result'];

            if (empty($results)) {
                break;
            }

            $allResults = array_merge($allResults, $results);

            if (count($allResults) >= $limit) {
                break;
            }

            $startRank += $batchSize;

            // Small delay to avoid rate limiting
            usleep(500_000);
        }

        return array_slice($allResults, 0, $limit);
    }

    private function processPlayer(array $entry, string $rankingDate, string $genderCode, array &$stats): void
    {
        $wttId = (string) ($entry['IttfId'] ?? '');
        $playerName = $entry['PlayerName'] ?? '';
        $countryCode3 = $entry['AssociationCountryCode'] ?? $entry['CountryCode'] ?? '';
        $countryName = $entry['AssociationCountryName'] ?? $entry['CountryName'] ?? '';
        $rankPosition = (int) ($entry['RankingPosition'] ?? 0);
        $rankPoints = (int) ($entry['RankingPointsYTD'] ?? 0);

        if (! $wttId || ! $playerName) {
            $stats['errors'][] = 'Skipping entry with missing data: '.json_encode($entry);

            return;
        }

        // Resolve country code
        $countryInfo = self::COUNTRY_MAP[$countryCode3] ?? ['code' => substr($countryCode3, 0, 2), 'name' => $countryName];

        // Parse name
        [$firstName, $lastName] = $this->parseName($playerName);

        // Find or create player
        $player = Player::where('wtt_id', $wttId)->first();

        if (! $player) {
            $player = Player::where('first_name', $firstName)
                ->where('last_name', $lastName)
                ->where('country_code', $countryInfo['code'])
                ->first();
        }

        if ($player) {
            $player->update([
                'wtt_id' => $wttId,
                'gender' => $genderCode,
                'world_ranking' => $rankPosition,
                'rating_points' => $rankPoints,
                'country' => $countryInfo['name'],
                'country_code' => $countryInfo['code'],
            ]);
            $stats['updated']++;
        } else {
            $player = Player::create([
                'wtt_id' => $wttId,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'gender' => $genderCode,
                'country' => $countryInfo['name'],
                'country_code' => $countryInfo['code'],
                'dominant_hand' => 'Right',
                'date_of_birth' => null,
                'world_ranking' => $rankPosition,
                'rating_points' => $rankPoints,
            ]);
            $stats['imported']++;
        }

        // Create ranking record
        Ranking::create([
            'player_id' => $player->id,
            'ranking' => $rankPosition,
            'rating_points' => $rankPoints,
            'ranking_date' => $rankingDate,
        ]);

        $stats['rankings_created']++;
    }

    private function parseName(string $fullName): array
    {
        $parts = preg_split('/\s+/', trim($fullName));

        if (count($parts) <= 1) {
            return [$fullName, ''];
        }

        $surnameParts = [];
        $givenParts = [];

        foreach ($parts as $i => $part) {
            if ($part === strtoupper($part) && ! preg_match('/\d/', $part) && empty($givenParts)) {
                $surnameParts[] = $part;
            } else {
                $givenParts = array_slice($parts, $i);
                break;
            }
        }

        if (empty($givenParts)) {
            return [$fullName, ''];
        }

        $firstName = implode(' ', $givenParts);
        $lastName = ucwords(implode(' ', $surnameParts));

        return [$firstName, $lastName];
    }

    private function displayResult(array $stats): void
    {
        $this->info('Import completed:');
        $this->info("  Imported: {$stats['imported']} new players");
        $this->info("  Updated: {$stats['updated']} existing players");
        $this->info("  Rankings created: {$stats['rankings_created']}");

        if (! empty($stats['errors'])) {
            $this->newLine();
            $this->warn('Errors ('.count($stats['errors']).'):');
            foreach ($stats['errors'] as $error) {
                $this->error("  - {$error}");
            }
        }
    }
}
