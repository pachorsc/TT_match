<?php

namespace App\Console\Commands;

use App\Services\IttfImportService;
use Illuminate\Console\Command;
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
