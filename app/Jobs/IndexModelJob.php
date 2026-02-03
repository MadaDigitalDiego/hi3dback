<?php

namespace App\Jobs;

use App\Models\ProfessionalProfile;
use App\Models\ServiceOffer;
use App\Models\Achievement;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Laravel\Scout\Searchable;

class IndexModelJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 5;

    /**
     * The maximum number of unhandled exceptions to allow before failing.
     */
    public int $maxExceptions = 3;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 120;

    /**
     * The number of seconds to wait before retrying the job.
     * Exponential backoff: 30, 60, 120, 240, 480 seconds
     */
    public array $backoff = [30, 60, 120, 240, 480];

    /**
     * The model to index.
     */
    protected Searchable $model;

    /**
     * The type of operation.
     */
    protected string $operation;

    /**
     * Create a new job instance.
     */
    public function __construct(Searchable $model, string $operation = 'index')
    {
        $this->model = $model;
        $this->operation = $operation;
        
        // Set unique job ID based on model type and ID
        $this->uniqueId = get_class($model) . '_' . $model->getKey();
    }

    /**
     * Get the unique ID for this job.
     */
    public function uniqueId(): string
    {
        return $this->uniqueId ?? get_class($this->model) . '_' . $this->model->getKey();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $modelClass = get_class($this->model);
        $modelId = $this->model->getKey();

        Log::channel('meilisearch')->info('IndexModelJob started', [
            'job_id' => $this->job?->getJobId(),
            'model' => $modelClass,
            'model_id' => $modelId,
            'operation' => $this->operation,
        ]);

        try {
            // Check if model should be searchable
            if (method_exists($this->model, 'shouldBeSearchable') && !$this->model->shouldBeSearchable()) {
                // If operation is 'index' but model shouldn't be searchable, remove from index instead
                if ($this->operation === 'index') {
                    $this->model->removeFromSearch();
                    Log::channel('meilisearch')->info('Model removed from index (not searchable)', [
                        'model' => $modelClass,
                        'model_id' => $modelId,
                    ]);
                }
                return;
            }

            switch ($this->operation) {
                case 'index':
                    $this->model->searchable();
                    break;
                    
                case 'update':
                    $this->model->searchable();
                    break;
                    
                case 'remove':
                    $this->model->removeFromSearch();
                    break;
                    
                default:
                    $this->model->searchable();
            }

            Log::channel('meilisearch')->info('IndexModelJob completed successfully', [
                'job_id' => $this->job?->getJobId(),
                'model' => $modelClass,
                'model_id' => $modelId,
                'operation' => $this->operation,
                'memory_usage' => memory_get_usage(true),
            ]);

        } catch (\Exception $e) {
            Log::channel('meilisearch')->error('IndexModelJob failed', [
                'job_id' => $this->job?->getJobId(),
                'model' => $modelClass,
                'model_id' => $modelId,
                'operation' => $this->operation,
                'exception' => $e->getMessage(),
                'attempt' => $this->attempts(),
            ]);

            throw $e; // Re-throw to trigger retry
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(?\Throwable $exception): void
    {
        $modelClass = get_class($this->model);
        $modelId = $this->model->getKey();

        Log::channel('meilisearch')->error('IndexModelJob permanently failed', [
            'job_id' => $this->job?->getJobId(),
            'model' => $modelClass,
            'model_id' => $modelId,
            'operation' => $this->operation,
            'exception' => $exception->getMessage(),
            'attempts' => $this->attempts(),
            'max_tries' => $this->tries,
        ]);

        // Optionally send alert notification
        $this->sendAlert($modelClass, $modelId, $exception);
    }

    /**
     * Send alert notification when job fails permanently.
     */
    protected function sendAlert(string $modelClass, int $modelId, \Throwable $exception): void
    {
        try {
            $webhook = config('meilisearch.alert_webhook');
            
            if (!$webhook) {
                return;
            }

            $message = "🚨 **Échec permanent d'indexation Meilisearch**\n\n";
            $message .= "**Modèle:** `{$modelClass}`\n";
            $message .= "**ID:** `{$modelId}`\n";
            $message .= "**Erreur:** `{$exception->getMessage()}`\n";
            $message .= "**Tentatives:** {$this->attempts()}/{$this->tries}";

            \Illuminate\Support\Facades\Http::post($webhook, [
                'text' => $message,
            ]);

            Log::channel('meilisearch')->info('Alert sent for failed indexing job', [
                'model' => $modelClass,
                'model_id' => $modelId,
            ]);
        } catch (\Exception $e) {
            Log::channel('meilisearch')->error('Failed to send alert notification', [
                'exception' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Determine the time at which the job should timeout.
     */
    public function retryUntil(): \DateTime
    {
        return now()->addHours(2);
    }
}

