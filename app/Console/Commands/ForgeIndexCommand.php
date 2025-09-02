<?php

namespace App\Console\Commands;

use App\Models\ProfessionalProfile;
use App\Models\ServiceOffer;
use App\Models\Achievement;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Exception;

class ForgeIndexCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'forge:index {--check-health} {--force} {--notify=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Index searchable models for Laravel Forge deployment with health checks';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Starting Forge indexation process...');

        // Check Meilisearch health if requested
        if ($this->option('check-health')) {
            if (!$this->checkMeilisearchHealth()) {
                if (!$this->option('force')) {
                    $this->error('❌ Meilisearch health check failed. Use --force to proceed anyway.');
                    return 1;
                }
                $this->warn('⚠️  Proceeding despite health check failure (--force used)');
            }
        }

        $models = [
            'professional_profiles' => ProfessionalProfile::class,
            'service_offers' => ServiceOffer::class,
            'achievements' => Achievement::class,
        ];

        $totalIndexed = 0;
        $errors = [];

        foreach ($models as $modelName => $modelClass) {
            try {
                $this->info("📊 Indexing {$modelName}...");
                
                $count = $modelClass::count();
                $this->line("   Found {$count} records");

                if ($count > 0) {
                    // Clear existing index
                    $modelClass::removeAllFromSearch();
                    $this->line("   Cleared existing index");

                    // Create progress bar
                    $bar = $this->output->createProgressBar($count);
                    $bar->start();

                    // Index in chunks
                    $modelClass::chunk(100, function ($records) use ($bar) {
                        $records->searchable();
                        $bar->advance($records->count());
                    });

                    $bar->finish();
                    $this->newLine();
                    $totalIndexed += $count;
                }

                $this->info("   ✅ {$modelName} indexed successfully ({$count} records)");

            } catch (Exception $e) {
                $error = "Failed to index {$modelName}: " . $e->getMessage();
                $errors[] = $error;
                $this->error("   ❌ {$error}");
            }
        }

        // Summary
        $this->newLine();
        $this->info("📈 Indexation Summary:");
        $this->line("   Total records indexed: {$totalIndexed}");
        
        if (count($errors) > 0) {
            $this->line("   Errors encountered: " . count($errors));
            foreach ($errors as $error) {
                $this->line("   - {$error}");
            }
        } else {
            $this->info("   ✅ All models indexed successfully!");
        }

        // Send notification if webhook provided
        if ($webhook = $this->option('notify')) {
            $this->sendNotification($webhook, $totalIndexed, $errors);
        }

        return count($errors) > 0 ? 1 : 0;
    }

    /**
     * Check Meilisearch health
     */
    private function checkMeilisearchHealth(): bool
    {
        try {
            $host = config('scout.meilisearch.host');
            $this->line("   Checking Meilisearch health at: {$host}");

            $response = Http::timeout(10)->get("{$host}/health");
            
            if ($response->successful()) {
                $this->info("   ✅ Meilisearch is healthy");
                return true;
            } else {
                $this->error("   ❌ Meilisearch health check failed (HTTP {$response->status()})");
                return false;
            }
        } catch (Exception $e) {
            $this->error("   ❌ Meilisearch health check failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send notification webhook
     */
    private function sendNotification(string $webhook, int $totalIndexed, array $errors): void
    {
        try {
            $status = count($errors) > 0 ? '⚠️ Partial Success' : '✅ Success';
            $message = "🔍 Meilisearch Indexation Complete\n";
            $message .= "Status: {$status}\n";
            $message .= "Records indexed: {$totalIndexed}\n";
            
            if (count($errors) > 0) {
                $message .= "Errors: " . count($errors) . "\n";
            }

            Http::post($webhook, [
                'text' => $message
            ]);

            $this->info("   📢 Notification sent to webhook");
        } catch (Exception $e) {
            $this->warn("   ⚠️  Failed to send notification: " . $e->getMessage());
        }
    }
}
