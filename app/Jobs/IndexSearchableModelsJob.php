<?php

namespace App\Jobs;

use App\Models\ProfessionalProfile;
use App\Models\ServiceOffer;
use App\Models\Achievement;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Exception;

class IndexSearchableModelsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300; // 5 minutes timeout
    public $tries = 3;

    protected $modelName;
    protected $fresh;

    /**
     * Create a new job instance.
     */
    public function __construct(?string $modelName = null, bool $fresh = true)
    {
        $this->modelName = $modelName;
        $this->fresh = $fresh;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Starting Meilisearch indexation job', [
            'model' => $this->modelName ?? 'all',
            'fresh' => $this->fresh
        ]);

        $models = [
            'professional_profiles' => ProfessionalProfile::class,
            'service_offers' => ServiceOffer::class,
            'achievements' => Achievement::class,
        ];

        // Filter models if specific model requested
        if ($this->modelName && isset($models[$this->modelName])) {
            $models = [$this->modelName => $models[$this->modelName]];
        }

        $totalIndexed = 0;
        $errors = [];

        foreach ($models as $modelName => $modelClass) {
            try {
                Log::info("Indexing {$modelName}...");

                $count = $modelClass::count();
                
                if ($count > 0) {
                    // Clear existing index if fresh
                    if ($this->fresh) {
                        $modelClass::removeAllFromSearch();
                        Log::info("Cleared existing index for {$modelName}");
                    }

                    // Index in chunks to avoid memory issues
                    $modelClass::chunk(100, function ($records) use ($modelName) {
                        $records->searchable();
                        Log::debug("Indexed chunk for {$modelName}", [
                            'count' => $records->count()
                        ]);
                    });

                    $totalIndexed += $count;
                    Log::info("Successfully indexed {$modelName}", ['count' => $count]);
                } else {
                    Log::info("No records found for {$modelName}");
                }

            } catch (Exception $e) {
                $error = "Failed to index {$modelName}: " . $e->getMessage();
                $errors[] = $error;
                Log::error($error, ['exception' => $e]);
            }
        }

        // Log summary
        Log::info('Meilisearch indexation job completed', [
            'total_indexed' => $totalIndexed,
            'errors_count' => count($errors),
            'errors' => $errors
        ]);

        // Throw exception if there were errors to trigger retry
        if (count($errors) > 0) {
            throw new Exception('Indexation completed with errors: ' . implode(', ', $errors));
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(Exception $exception): void
    {
        Log::error('Meilisearch indexation job failed', [
            'model' => $this->modelName ?? 'all',
            'fresh' => $this->fresh,
            'exception' => $exception->getMessage(),
            'attempts' => $this->attempts()
        ]);
    }
}
