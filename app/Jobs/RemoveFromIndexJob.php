<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RemoveFromIndexJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 60;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public array $backoff = [30, 60, 120];

    /**
     * The model to remove from index.
     */
    protected Model $model;

    /**
     * Create a new job instance.
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $modelClass = get_class($this->model);
        $modelId = $this->model->getKey();

        Log::channel('meilisearch')->info('RemoveFromIndexJob started', [
            'job_id' => $this->job?->getJobId(),
            'model' => $modelClass,
            'model_id' => $modelId,
        ]);

        try {
            $this->model->removeFromSearch();

            Log::channel('meilisearch')->info('RemoveFromIndexJob completed successfully', [
                'job_id' => $this->job?->getJobId(),
                'model' => $modelClass,
                'model_id' => $modelId,
            ]);

        } catch (\Exception $e) {
            Log::channel('meilisearch')->error('RemoveFromIndexJob failed', [
                'job_id' => $this->job?->getJobId(),
                'model' => $modelClass,
                'model_id' => $modelId,
                'exception' => $e->getMessage(),
                'attempt' => $this->attempts(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(?\Throwable $exception): void
    {
        $modelClass = get_class($this->model);
        $modelId = $this->model->getKey();

        Log::channel('meilisearch')->error('RemoveFromIndexJob permanently failed', [
            'job_id' => $this->job?->getJobId(),
            'model' => $modelClass,
            'model_id' => $modelId,
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

