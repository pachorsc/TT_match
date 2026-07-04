<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\WttMatchImportService;
use Illuminate\Console\Command;

class ImportWttMatches extends Command
{
    protected $signature = 'wtt:import-matches {--file= : JSON filename in storage/app/import/wtt/}';

    protected $description = 'Import WTT match results from a JSON file into the database';

    public function __construct(
        private WttMatchImportService $importService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $filename = $this->option('file');

        if (! $filename) {
            $this->error('Please specify a file with --file=filename.json');
            $this->newLine();
            $this->info('Available files:');

            $importPath = storage_path('app/import/wtt');
            if (is_dir($importPath)) {
                $files = glob($importPath.'/*.json');
                foreach ($files as $file) {
                    $this->line('  '.basename($file));
                }
            }

            return Command::FAILURE;
        }

        $this->info("Importing matches from {$filename}...");
        $this->newLine();

        try {
            $stats = $this->importService->importFromJson($filename);
        } catch (\Exception $e) {
            $this->error("Import failed: {$e->getMessage()}");

            return Command::FAILURE;
        }

        $this->displayResult($stats);

        return Command::SUCCESS;
    }

    private function displayResult(array $stats): void
    {
        $this->info('Import completed:');
        $this->info("  Imported: {$stats['imported']} matches");
        $this->info("  Skipped: {$stats['skipped']} (players not in database)");

        if (! empty($stats['errors'])) {
            $this->newLine();
            $this->warn('Errors ('.count($stats['errors']).'):');
            foreach ($stats['errors'] as $error) {
                $this->error("  - {$error}");
            }
        }
    }
}
