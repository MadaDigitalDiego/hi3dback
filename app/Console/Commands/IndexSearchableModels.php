<?php

namespace App\Console\Commands;

use App\Models\ProfessionalProfile;
use App\Models\ServiceOffer;
use App\Models\Achievement;
use Illuminate\Console\Command;

class IndexSearchableModels extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'search:index {--model=} {--fresh} {--show-progress}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Index searchable models in Meilisearch';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $model = $this->option('model');
        $fresh = $this->option('fresh');
        $showProgress = $this->option('show-progress');

        $models = [
            'professional_profiles' => ProfessionalProfile::class,
            'service_offers' => ServiceOffer::class,
            'achievements' => Achievement::class,
        ];

        if ($model && !array_key_exists($model, $models)) {
            $this->error("Model '{$model}' not found. Available models: " . implode(', ', array_keys($models)));
            return 1;
        }

        $modelsToIndex = $model ? [$model => $models[$model]] : $models;

        foreach ($modelsToIndex as $modelName => $modelClass) {
            $this->info("Indexing {$modelName}...");

            if ($fresh) {
                $this->info("Flushing existing index for {$modelName}...");
                $modelClass::removeAllFromSearch();
            }

            $count = $modelClass::count();
            $this->info("Found {$count} records to index for {$modelName}");

            if ($count > 0) {
                $bar = $this->output->createProgressBar($count);
                $bar->start();

                $modelClass::chunk(100, function ($records) use ($bar, $showProgress) {
                    $records->searchable();
                    $bar->advance($records->count());

                    if ($showProgress) {
                        $this->line("\nIndexed {$records->count()} records");
                    }
                });

                $bar->finish();
                $this->line('');
            }

            $this->info("✓ {$modelName} indexed successfully");
        }

        $this->info('All models indexed successfully!');
        return 0;
    }
}
