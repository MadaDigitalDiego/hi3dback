<?php

namespace App\Console\Commands;

use App\Models\ProfessionalProfile;
use App\Models\ServiceOffer;
use App\Models\Achievement;
use Illuminate\Console\Command;

class FlushSearchIndexes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'search:flush {--model=} {--confirm}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Flush search indexes in Meilisearch';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $model = $this->option('model');
        $confirm = $this->option('confirm');

        $models = [
            'professional_profiles' => ProfessionalProfile::class,
            'service_offers' => ServiceOffer::class,
            'achievements' => Achievement::class,
        ];

        if ($model && !array_key_exists($model, $models)) {
            $this->error("Model '{$model}' not found. Available models: " . implode(', ', array_keys($models)));
            return 1;
        }

        $modelsToFlush = $model ? [$model => $models[$model]] : $models;

        if (!$confirm) {
            $modelNames = $model ? $model : 'all models';
            if (!$this->confirm("Are you sure you want to flush the search indexes for {$modelNames}?")) {
                $this->info('Operation cancelled.');
                return 0;
            }
        }

        foreach ($modelsToFlush as $modelName => $modelClass) {
            $this->info("Flushing search index for {$modelName}...");

            try {
                $modelClass::removeAllFromSearch();
                $this->info("✓ {$modelName} index flushed successfully");
            } catch (\Exception $e) {
                $this->error("✗ Failed to flush {$modelName} index: " . $e->getMessage());
            }
        }

        $this->info('Search indexes flushed successfully!');
        return 0;
    }
}
