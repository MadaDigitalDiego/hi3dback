<?php

namespace App\Console\Commands;

use App\Services\MeilisearchMonitoringService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Exception;

class MeilisearchMonitorCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'meilisearch:monitor 
                            {--detailed : Show detailed information}
                            {--json : Output as JSON}
                            {--test-search= : Perform a test search with the given query}';

    /**
     * The console command description.
     */
    protected $description = 'Monitor Meilisearch health and statistics';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $monitoringService = new MeilisearchMonitoringService();

        // Get full status
        $status = $monitoringService->getFullStatus();

        // Output as JSON if requested
        if ($this->option('json')) {
            $this->line(json_encode($status, JSON_PRETTY_PRINT));
            return 0;
        }

        // Display summary
        $this->displaySummary($status, $monitoringService);

        // Display detailed info if requested
        if ($this->option('detailed')) {
            $this->displayDetailedInfo($status, $monitoringService);
        }

        // Perform test search if requested
        if ($testQuery = $this->option('test-search')) {
            $this->performTestSearch($testQuery, $monitoringService);
        }

        return $status['health']['status'] === 'healthy' ? 0 : 1;
    }

    /**
     * Display summary information.
     */
    protected function displaySummary(array $status, MeilisearchMonitoringService $service): void
    {
        $this->newLine();
        $this->info('╔═══════════════════════════════════════════════════════════════╗');
        $this->info('║              📊 MEILISEARCH MONITORING REPORT                 ║');
        $this->info('╚═══════════════════════════════════════════════════════════════╝');
        $this->newLine();

        // Health Status
        $healthStatus = $status['health']['status'];
        
        if ($healthStatus === 'healthy') {
            $healthIcon = '✅';
            $color = 'green';
        } elseif ($healthStatus === 'unhealthy') {
            $healthIcon = '⚠️';
            $color = 'yellow';
        } elseif ($healthStatus === 'unreachable') {
            $healthIcon = '❌';
            $color = 'red';
        } else {
            $healthIcon = '❓';
            $color = 'gray';
        }

        $this->line("  {$healthIcon} Meilisearch Health: <fg={$color};options=bold>{$healthStatus}</>");

        // Summary stats
        $summary = $service->getSummary();
        $this->newLine();
        $this->line('  📈 Statistics:');
        $this->line("     • Total records in DB: <fg=cyan>{$summary['total_records_in_db']}</>");
        $this->line("     • Searchable records: <fg=cyan>{$summary['total_records_searchable']}</>");
        $this->line("     • Pending indexation jobs: <fg=yellow>{$summary['pending_indexation_jobs']}</>");
        $this->line("     • Failed indexation jobs: <fg=red>{$summary['failed_indexation_jobs']}</>");

        // Index stats
        $this->newLine();
        $this->line('  📊 Index Statistics:');
        
        foreach ($status['stats'] as $indexName => $indexStats) {
            $exists = $indexStats['exists'] ?? false;
            $icon = $exists ? '✅' : '❌';
            $count = $indexStats['data']['numberOfDocuments'] ?? 0;
            $this->line("     {$icon} {$indexName}: <fg=cyan>{$count}</> documents");
        }
    }

    /**
     * Display detailed information.
     */
    protected function displayDetailedInfo(array $status, MeilisearchMonitoringService $service): void
    {
        $this->newLine();
        $this->info('  ═══════════════════════════════════════════════════════════');
        $this->info('  📋 DETAILED INFORMATION');
        $this->info('  ═══════════════════════════════════════════════════════════');
        $this->newLine();

        // Models info
        $this->line('  📁 Models Information:');
        $this->newLine();

        foreach ($status['models'] as $modelName => $modelInfo) {
            $this->line("     <fg=yellow>{$modelName}</>");
            $this->line("        Model: {$modelInfo['model']}");
            $this->line("        Total in DB: {$modelInfo['total_in_db']}");
            $this->line("        Should be searchable: {$modelInfo['should_be_searchable']}");
            $this->line("        Criteria: {$modelInfo['searchable_criteria']}");
            $this->newLine();
        }

        // Queue status
        $this->line('  📋 Queue Status:');
        $queueStatus = $status['queue_status'];
        $this->line("     Queue exists: " . ($queueStatus['queue_exists'] ? '✅' : '❌'));
        $this->line("     Pending jobs: {$queueStatus['pending_jobs']}");
        $this->line("     Failed jobs: {$queueStatus['failed_jobs']}");
        $this->newLine();

        // Configuration
        $this->line('  ⚙️  Configuration:');
        $config = $service->isConfigured();
        $this->line("     Scout driver: " . ($config['driver_configured'] ? '✅' : '❌'));
        $this->line("     Host configured: " . ($config['host_configured'] ? '✅' : '❌'));
        $this->line("     Key configured: " . ($config['key_configured'] ? '✅' : '❌'));
        $this->line("     Host: {$config['host']}");
    }

    /**
     * Perform a test search.
     */
    protected function performTestSearch(string $query, MeilisearchMonitoringService $service): void
    {
        $this->newLine();
        $this->info("  🔍 Test Search for '{$query}':");
        
        $result = $service->testSearch($query);
        
        if ($result['success']) {
            $this->line("     ✅ Search successful!");
            $hits = $result['results']['hits'] ?? [];
            $processingTime = $result['results']['processingTimeMs'] ?? 'N/A';
            $this->line("     • Hits: " . count($hits));
            $this->line("     • Processing time: {$processingTime}ms");
        } else {
            $this->line("     ❌ Search failed: {$result['error']}");
        }
    }
}

