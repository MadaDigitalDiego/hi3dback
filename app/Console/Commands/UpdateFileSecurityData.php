<?php

namespace App\Console\Commands;

use App\Models\File;
use App\Models\Message;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateFileSecurityData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'files:update-security-data {--dry-run : Run without making changes}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Update existing files with security data (receiver_id, is_shared, etc.)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');

        $this->info('Starting file security data update...');
        $this->info($dryRun ? '(DRY RUN - No changes will be made)' : '');

        try {
            // Get all files with messages
            $filesWithMessages = File::whereNotNull('message_id')
                ->where('receiver_id', null)
                ->get();

            $this->info("Found {$filesWithMessages->count()} files with messages to update");

            $updated = 0;
            $skipped = 0;

            foreach ($filesWithMessages as $file) {
                try {
                    $message = $file->message;

                    if (!$message) {
                        $this->warn("File {$file->id}: Message not found");
                        $skipped++;
                        continue;
                    }

                    // Determine receiver based on message
                    $receiver_id = null;
                    
                    // If file owner is the sender, receiver is the message receiver
                    if ($file->user_id === $message->sender_id) {
                        $receiver_id = $message->receiver_id;
                    }
                    // If file owner is the receiver, receiver_id stays null (owner only)
                    // Or we could set it to the sender for shared files

                    if (!$dryRun) {
                        $file->update([
                            'receiver_id' => $receiver_id,
                            'is_shared' => $receiver_id !== null,
                            'shared_at' => $receiver_id !== null ? now() : null,
                        ]);
                    }

                    $this->line("File {$file->id}: Updated (receiver_id: {$receiver_id})");
                    $updated++;

                } catch (\Exception $e) {
                    $this->error("File {$file->id}: Error - {$e->getMessage()}");
                    $skipped++;
                }
            }

            $this->info("\n=== Summary ===");
            $this->info("Updated: {$updated}");
            $this->info("Skipped: {$skipped}");

            if ($dryRun) {
                $this->warn("\nDRY RUN: No changes were made. Run without --dry-run to apply changes.");
            }

            Log::info('File security data update completed', [
                'updated' => $updated,
                'skipped' => $skipped,
                'dry_run' => $dryRun,
            ]);

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("Error: {$e->getMessage()}");
            Log::error('File security data update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return Command::FAILURE;
        }
    }
}

