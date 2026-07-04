<?php

namespace App\Console\Commands;

use App\Services\StatsTTService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ImportStatsTTCommand extends Command
{
    protected $signature = 'import:statstt {type : Entity to import (players|matches|rankings|tournaments)} {--file= : Specific JSON filename to import}';

    protected $description = 'Import data from StatsTT JSON files into the database';

    private StatsTTService $statsTTService;

    public function __construct(StatsTTService $statsTTService)
    {
        parent::__construct();
        $this->statsTTService = $statsTTService;
    }

    public function handle(): int
    {
        $type = $this->argument('type');
        $file = $this->option('file');

        $validTypes = ['players', 'matches', 'rankings', 'tournaments'];

        if (! in_array($type, $validTypes)) {
            $this->error("Invalid type: {$type}. Must be one of: ".implode(', ', $validTypes));

            return Command::FAILURE;
        }

        $importPath = config('statstt.import_path', storage_path('app/import/statstt'));

        if (! $file) {
            $file = $this->findLatestFile($importPath, $type);
            if (! $file) {
                $this->error("No import files found for type '{$type}' in {$importPath}");
                $this->newLine();
                $this->info("Usage: php import:statstt {$type} --file=filename.json");
                $this->info('Available files:');
                $this->listFiles($importPath, $type);

                return Command::FAILURE;
            }
        }

        $this->info("Importing {$type} from: {$file}");
        $this->newLine();

        try {
            $result = match ($type) {
                'players' => $this->statsTTService->importPlayers($file),
                'matches' => $this->statsTTService->importMatches($file),
                'rankings' => $this->statsTTService->importRankings($file),
                'tournaments' => $this->statsTTService->importTournaments($file),
            };

            $this->displayResult($result);

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Import failed: {$e->getMessage()}");
            Log::error('StatsTT import failed', [
                'type' => $type,
                'file' => $file,
                'error' => $e->getMessage(),
            ]);

            return Command::FAILURE;
        }
    }

    private function findLatestFile(string $path, string $type): ?string
    {
        if (! is_dir($path)) {
            return null;
        }

        $files = glob($path."/{$type}*.json");
        if (empty($files)) {
            return null;
        }

        usort($files, function ($a, $b) {
            return filemtime($b) - filemtime($a);
        });

        return basename($files[0]);
    }

    private function listFiles(string $path, string $type): void
    {
        if (! is_dir($path)) {
            return;
        }

        $files = glob($path."/{$type}*.json");
        if (empty($files)) {
            $this->info("  No {$type} files found.");

            return;
        }

        foreach ($files as $file) {
            $this->info('  '.basename($file));
        }
    }

    private function displayResult(array $result): void
    {
        if (isset($result['imported'])) {
            $this->info("Imported: {$result['imported']} new records");
        }
        if (isset($result['updated'])) {
            $this->info("Updated: {$result['updated']} existing records");
        }
        if (isset($result['errors']) && ! empty($result['errors'])) {
            $this->newLine();
            $this->warn('Errors ('.count($result['errors']).'):');
            foreach ($result['errors'] as $error) {
                $this->error("  - {$error}");
            }
        }
    }
}
