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

class ReindexModelJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 300;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public array $backoff = [60, 180, 300];

    /**
     * The model type to reindex.
     */
    protected ?string $modelType;

    /**
     * Whether to show progress.
     */
    protected bool $showProgress;

    /**
     * Create a new job instance.
     */
    public function __construct(?string $modelType = null, bool $showProgress = false)
    {
        $this->modelType = $modelType;
        $this->showProgress = $showProgress;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $startTime = microtime(true);
        $totalIndexed = 0;

        Log::channel('meilisearch')->info('ReindexModelJob started', [
            'job_id' => $this->job?->getJobId(),
            'model_type' => $this->modelType,
            'show_progress' => $this->showProgress,
        ]);

        try {
            $models = $this->getModelsToReindex();

            foreach ($models as $modelType => $modelClass) {
                $indexed = $this->reindexModel($modelClass, $modelType);
                $totalIndexed += $indexed;
            }

            $duration = round((microtime(true) - $startTime) * 1000, 2);

            Log::channel('meilisearch')->info('ReindexModelJob completed successfully', [
                'job_id' => $this->job?->getJobId(),
                'total_indexed' => $totalIndexed,
                'duration_ms' => $duration,
                'memory_usage' => memory_get_usage(true),
            ]);

        } catch (\Exception $e) {
            Log::channel('meilisearch')->error('ReindexModelJob failed', [
                'job_id' => $this->job?->getJobId(),
                'model_type' => $this->modelType,
                'exception' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Get the models to reindex.
     */
    protected function getModelsToReindex(): array
    {
        $models = [
            'professional_profiles' => ProfessionalProfile::class,
            'service_offers' => ServiceOffer::class,
            'achievements' => Achievement::class,
        ];

        if ($this->modelType && isset($models[$this->modelType])) {
            return [$this->modelType => $models[$this->modelType]];
        }

        return $models;
    }

    /**
     * Reindex a specific model type.
     */
    protected function reindexModel(string $modelClass, string $modelType): int
    {
        $modelInstance = new $modelClass;
        
        // Check if model is searchable
        if (!in_array('Laravel\Scout\Searchable', class_uses($modelInstance))) {
            Log::channel('meilisearch')->warning('ReindexModelJob: Model not searchable', [
                'model' => $modelType,
            ]);
            return 0;
        }

        $count = $modelClass::count();
        
        if ($this->showProgress) {
            $this->line("🔄 Réindexation de {$modelType} ({$count} enregistrements)...");
        }

        // Clear existing index
        $modelClass::removeAllFromSearch();

        if ($this->showProgress) {
            $this->line("   ✅ Index clear");
        }

        // Reindex in chunks
        $indexed = 0;
        $chunkSize = 100;

        $modelClass::chunk($chunkSize, function ($records) use ($modelType, &$indexed) {
            $records->searchable();
            $indexed += $records->count();

            if ($this->showProgress) {
                $this->line("   📊 Indexé: {$indexed} enregistrements");
            }
        });

        if ($this->showProgress) {
            $this->line("   ✅ {$modelType} réindexé avec succès ({$indexed} enregistrements)");
        }

        Log::channel('meilisearch')->info('ReindexModelJob: Model reindexed', [
            'model' => $modelType,
            'indexed_count' => $indexed,
        ]);

        return $indexed;
    }

    /**
     * Handle a job failure.
     */
    public function failed(?\Throwable $exception): void
    {
        Log::channel('meilisearch')->error('ReindexModelJob permanently failed', [
            'job_id' => $this->job?->getJobId(),
            'model_type' => $this->modelType,
            'exception' => $exception->getMessage(),
            'attempts' => $this->attempts(),
        ]);
    }

    /**
     * Determine the time at which the job should timeout.
     */
    public function retryUntil(): \DateTime
    {
        return now()->addHours(1);
    }
}

