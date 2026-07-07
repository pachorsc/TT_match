<?php

namespace App\Console\Commands;

use App\Services\IttfImportService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class ImportIttf extends Command
{
    protected $signature = 'import:ittf {type : Entity to import (rankings|players|matches)} {--file= : Specific JSON filename to import}';

    protected $description = 'Import data from ITTF portal JSON files into the database';

    private IttfImportService $ittfService;

    public function __construct(IttfImportService $ittfService)
    {
        parent::__construct();
        $this->ittfService = $ittfService;
    }

    public function handle(): int
    {
        $type = $this->argument('type');
        $file = $this->option('file');

        $validTypes = ['rankings', 'players', 'matches'];

        if (! in_array($type, $validTypes)) {
            $this->error("Invalid type: {$type}. Must be one of: ".implode(', ', $validTypes));

            return Command::FAILURE;
        }

        $importPath = config('ittf.import_path', storage_path('app/import/ittf'));

        if ($type === 'matches') {
            return $this->importAllMatchFiles($importPath, $file);
        }

        if (! $file) {
            $file = $this->findLatestFile($importPath, $type);
            if (! $file) {
                $this->error("No import files found for type '{$type}' in {$importPath}");
                $this->newLine();
                $this->info("Usage: php artisan import:ittf {$type} --file=filename.json");
                $this->info('Available files:');
                $this->listFiles($importPath, $type);

                return Command::FAILURE;
            }
        }

        $this->info("Importing {$type} from: {$file}");
        $this->newLine();

        try {
            $result = match ($type) {
                'rankings' => $this->ittfService->importRankings($file),
                'players' => $this->ittfService->importPlayers($file),
                'matches' => $this->ittfService->importMatches($file),
            };

            $this->displayResult($result);

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Import failed: {$e->getMessage()}");
            Log::error('ITTF import failed', [
                'type' => $type,
                'file' => $file,
                'error' => $e->getMessage(),
            ]);

            return Command::FAILURE;
        }
    }

    private function importAllMatchFiles(string $importPath, ?string $specificFile): int
    {
        if ($specificFile) {
            $this->info("Importing matches from: {$specificFile}");
            $this->newLine();

            try {
                $result = $this->ittfService->importMatches($specificFile);
                $this->displayResult($result);

                return Command::SUCCESS;
            } catch (\Exception $e) {
                $this->error("Import failed: {$e->getMessage()}");

                return Command::FAILURE;
            }
        }

        $files = $this->findAllMatchFiles($importPath);

        if (empty($files)) {
            $this->error("No match import files found in {$importPath}");

            return Command::FAILURE;
        }

        $this->info('Found '.count($files).' match files to import');
        $this->newLine();

        $totalImported = 0;
        $totalErrors = 0;
        $exitCode = Command::SUCCESS;

        foreach ($files as $file) {
            $this->info("  Importing: {$file}");
            $this->newLine();

            try {
                $result = $this->ittfService->importMatches($file);

                $imported = $result['imported'] ?? 0;
                $errors = $result['errors'] ?? [];
                $skipped = $result['skipped'] ?? 0;

                $totalImported += $imported;
                $totalErrors += count($errors);

                $this->info("    Imported: {$imported}, Skipped: {$skipped}, Errors: ".count($errors));
                $this->newLine();
            } catch (\Exception $e) {
                $this->error("    Failed: {$e->getMessage()}");
                $this->newLine();
                Log::error('ITTF match import failed', [
                    'file' => $file,
                    'error' => $e->getMessage(),
                ]);
                $totalErrors++;
                $exitCode = Command::FAILURE;
            }
        }

        $this->info("Done. Total imported: {$totalImported}, Total errors: {$totalErrors}");

        // Auto-validate: remove impossible matches (same players, same tournament, multiple matches)
        $this->newLine();
        $this->info('Running post-import validation...');
        $validateResult = Artisan::call('matches:validate', ['--dry-run' => false]);
        $this->info(Artisan::output());

        return $exitCode;
    }

    private function findAllMatchFiles(string $path): array
    {
        if (! is_dir($path)) {
            return [];
        }

        $files = glob($path.'/{matches_*.json,player_matches_*.json}', GLOB_BRACE);
        usort($files, function ($a, $b) {
            return filemtime($a) - filemtime($b);
        });

        return array_map('basename', $files);
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
