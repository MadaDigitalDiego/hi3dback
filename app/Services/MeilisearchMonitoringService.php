<?php

namespace App\Services;

use App\Models\ProfessionalProfile;
use App\Models\ServiceOffer;
use App\Models\Achievement;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class MeilisearchMonitoringService
{
    protected ?string $host;
    protected ?string $key;
    protected array $stats = [];

    public function __construct()
    {
        $this->host = config('scout.meilisearch.host');
        $this->key = config('scout.meilisearch.key');
    }

    /**
     * Get complete health and stats of Meilisearch.
     */
    public function getFullStatus(): array
    {
        return [
            'timestamp' => now()->toIso8601String(),
            'health' => $this->checkHealth(),
            'stats' => $this->getAllIndexStats(),
            'models' => $this->getModelsInfo(),
            'queue_status' => $this->getQueueStatus(),
        ];
    }

    /**
     * Check Meilisearch health.
     */
    public function checkHealth(): array
    {
        try {
            $response = Http::timeout(10)
                ->withBasicAuth($this->key ? : '', $this->key ? : '')
                ->get("{$this->host}/health");

            if ($response->successful()) {
                return [
                    'status' => 'healthy',
                    'response' => $response->json(),
                    'error' => null,
                ];
            }

            return [
                'status' => 'unhealthy',
                'response' => null,
                'error' => "HTTP {$response->status()}",
            ];
        } catch (Exception $e) {
            return [
                'status' => 'unreachable',
                'response' => null,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get stats for all indexes.
     */
    public function getAllIndexStats(): array
    {
        $indexes = [
            'professional_profiles_index',
            'service_offers_index',
            'achievements_index',
        ];

        $stats = [];

        foreach ($indexes as $index) {
            $stats[$index] = $this->getIndexStats($index);
        }

        return $stats;
    }

    /**
     * Get stats for a specific index.
     */
    public function getIndexStats(string $index): array
    {
        try {
            $response = Http::timeout(10)
                ->withBasicAuth($this->key ? : '', $this->key ? : '')
                ->get("{$this->host}/indexes/{$index}/stats");

            if ($response->successful()) {
                return [
                    'exists' => true,
                    'data' => $response->json(),
                    'error' => null,
                ];
            }

            return [
                'exists' => false,
                'data' => null,
                'error' => "HTTP {$response->status()}",
            ];
        } catch (Exception $e) {
            return [
                'exists' => false,
                'data' => null,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get information about searchable models.
     */
    public function getModelsInfo(): array
    {
        return [
            'professional_profiles' => [
                'model' => ProfessionalProfile::class,
                'total_in_db' => ProfessionalProfile::count(),
                'searchable_criteria' => 'completion_percentage >= 50',
                'should_be_searchable' => ProfessionalProfile::where('completion_percentage', '>=', 50)->count(),
            ],
            'service_offers' => [
                'model' => ServiceOffer::class,
                'total_in_db' => ServiceOffer::count(),
                'searchable_criteria' => "status === 'active' && !is_private",
                'should_be_searchable' => ServiceOffer::where('status', 'active')->where('is_private', false)->count(),
            ],
            'achievements' => [
                'model' => Achievement::class,
                'total_in_db' => Achievement::count(),
                'searchable_criteria' => 'title && category not empty',
                'should_be_searchable' => Achievement::whereNotNull('title')->where('title', '!=', '')->whereNotNull('category')->where('category', '!=', '')->count(),
            ],
        ];
    }

    /**
     * Get queue status for indexation jobs.
     */
    public function getQueueStatus(): array
    {
        try {
            $jobs = \Illuminate\Support\Facades\DB::table('jobs')
                ->where('queue', 'indexation')
                ->count();

            $failedJobs = \Illuminate\Support\Facades\DB::table('failed_jobs')
                ->where('queue', 'indexation')
                ->count();

            return [
                'pending_jobs' => $jobs,
                'failed_jobs' => $failedJobs,
                'queue_exists' => true,
            ];
        } catch (Exception $e) {
            return [
                'pending_jobs' => 0,
                'failed_jobs' => 0,
                'queue_exists' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get recent indexing logs.
     */
    public function getRecentLogs(int $limit = 100): array
    {
        try {
            $logs = \Illuminate\Support\Facades\DB::table('laravel_log')
                ->where('message', 'like', '%Meilisearch%')
                ->orWhere('message', 'like', '%IndexModelJob%')
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();

            return $logs->toArray();
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Check if Meilisearch is properly configured.
     */
    public function isConfigured(): array
    {
        return [
            'driver_configured' => config('scout.driver') === 'meilisearch',
            'host_configured' => !empty($this->host),
            'key_configured' => !empty($this->key),
            'host' => $this->host ? substr($this->host, 0, 30) . '...' : null,
        ];
    }

    /**
     * Perform a test search.
     */
    public function testSearch(string $query = 'test'): array
    {
        try {
            $response = Http::timeout(10)
                ->withBasicAuth($this->key ? : '', $this->key ? : '')
                ->post("{$this->host}/indexes/professional_profiles_index/search", [
                    'q' => $query,
                    'limit' => 5,
                ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'results' => $response->json(),
                    'error' => null,
                ];
            }

            return [
                'success' => false,
                'results' => null,
                'error' => "HTTP {$response->status()}",
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'results' => null,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get summary metrics.
     */
    public function getSummary(): array
    {
        $health = $this->checkHealth();
        $modelsInfo = $this->getModelsInfo();
        $queueStatus = $this->getQueueStatus();

        $totalInDb = 0;
        $totalSearchable = 0;

        foreach ($modelsInfo as $info) {
            $totalInDb += $info['total_in_db'];
            $totalSearchable += $info['should_be_searchable'];
        }

        return [
            'overall_status' => $health['status'] === 'healthy' ? 'healthy' : 'issues',
            'meilisearch_health' => $health['status'],
            'total_records_in_db' => $totalInDb,
            'total_records_searchable' => $totalSearchable,
            'pending_indexation_jobs' => $queueStatus['pending_jobs'],
            'failed_indexation_jobs' => $queueStatus['failed_jobs'],
            'indexes' => count($this->getAllIndexStats()),
        ];
    }
}

