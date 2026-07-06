<?php

namespace App\Console\Commands;

use App\Models\Player;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class UpdatePlayerBirthYears extends Command
{
    protected $signature = 'players:update-birth-years {--file= : JSON file with ittf_id => birth_year mappings} {--scrape : Scrape birth years from ITTF portal}';

    protected $description = 'Update player birth years from ITTF data or a JSON file';

    public function handle(): int
    {
        $file = $this->option('file');
        $scrape = $this->option('scrape');

        if ($file) {
            return $this->updateFromFile($file);
        }

        if ($scrape) {
            return $this->scrapeBirthYears();
        }

        $this->error('Please specify --file=path.json or --scrape');

        return Command::FAILURE;
    }

    private function updateFromFile(string $file): int
    {
        if (! file_exists($file)) {
            $this->error("File not found: {$file}");

            return Command::FAILURE;
        }

        $data = json_decode(file_get_contents($file), true);
        if (! is_array($data)) {
            $this->error('Invalid JSON format');

            return Command::FAILURE;
        }

        $updated = 0;
        $notFound = 0;

        foreach ($data as $ittfId => $birthYear) {
            $player = Player::where('ittf_id', (string) $ittfId)->first();
            if (! $player) {
                $notFound++;

                continue;
            }

            $player->date_of_birth = "{$birthYear}-01-01";
            $player->save();
            $updated++;

            $this->line("  Updated {$player->full_name} (ID:{$ittfId}) -> birth_year={$birthYear}");
        }

        $this->info("Updated: {$updated}, Not found: {$notFound}");

        return Command::SUCCESS;
    }

    private function scrapeBirthYears(): int
    {
        $this->info('Scraping birth years from ITTF portal...');

        // Get all players with null DOB and an ITTF ID
        $players = Player::whereNull('date_of_birth')
            ->whereNotNull('ittf_id')
            ->where('ittf_id', '!=', '')
            ->orderBy('world_ranking')
            ->get();

        $this->info("Found {$players->count()} players without DOB");

        $updated = 0;
        $errors = 0;

        foreach ($players as $player) {
            $birthYear = $this->fetchBirthYearFromIttf($player->ittf_id);

            if ($birthYear) {
                $player->date_of_birth = "{$birthYear}-01-01";
                $player->save();
                $updated++;
                $this->line("  {$player->full_name} -> {$birthYear}");
            } else {
                $errors++;
            }

            // Rate limit
            usleep(500_000);
        }

        $this->info("Updated: {$updated}, Errors: {$errors}");

        return Command::SUCCESS;
    }

    private function fetchBirthYearFromIttf(string $ittfId): ?int
    {
        try {
            $url = "https://results.ittf.link/index.php/player-profile/list/60?resetfilters=1&vw_profiles___player_id_raw%5Bvalue%5D%5B%5D={$ittfId}";
            $response = Http::timeout(15)->get($url);

            if (! $response->successful()) {
                return null;
            }

            $html = $response->body();
            if (preg_match('/Birth Year:\s*(\d{4})/', $html, $matches)) {
                return (int) $matches[1];
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }
}
