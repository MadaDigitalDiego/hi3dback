<?php

namespace App\Console\Commands;

use App\Services\FileManagerService;
use Illuminate\Console\Command;

class CleanExpiredFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'files:clean-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean expired SwissTransfer files and update their status';

    /**
     * Execute the console command.
     */
    public function handle(FileManagerService $fileManagerService): int
    {
        $this->info('Starting cleanup of expired files...');

        $expiredCount = $fileManagerService->checkExpiredFiles();

        if ($expiredCount > 0) {
            $this->info("Marked {$expiredCount} files as expired.");
        } else {
            $this->info('No expired files found.');
        }

        return Command::SUCCESS;
    }
}
