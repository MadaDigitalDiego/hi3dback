<?php

namespace App\Console\Commands;

use App\Jobs\ReindexModelJob;
use Illuminate\Console\Command;

class ForgeIndexIncrementalCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'forge:index:incremental 
                            {--model= : Specific model to reindex}
                            {--force : Force reindex even if not needed}';

    /**
     * The console command description.
     */
    protected $description = 'Perform incremental indexation of searchable models';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🚀 Starting incremental indexation...');
        $this->newLine();

        $modelType = $this->option('model');
        $force = $this->option('force');

        // Validate model type if provided
        $validModels = ['professional_profiles', 'service_offers', 'achievements'];
        if ($modelType && !in_array($modelType, $validModels)) {
            $this->error("Invalid model type. Valid models: " . implode(', ', $validModels));
            return 1;
        }

        try {
            // Dispatch job to queue for async processing
            ReindexModelJob::dispatch($modelType, showProgress: true)
                ->onQueue('indexation')
                ->onConnection('redis');

            $this->info('✅ Incremental indexation job dispatched to queue');
            $this->line('   Run: php artisan queue:work indexation --queue=indexation --daemon');
            $this->line('   Or monitor with: php artisan meilisearch:monitor --detailed');

            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Failed to dispatch indexation job: ' . $e->getMessage());
            return 1;
        }
    }
}

