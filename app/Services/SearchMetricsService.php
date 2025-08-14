<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SearchMetricsService
{
    protected string $prefix = 'search_metrics:';
    protected int $retentionDays = 30;

    /**
     * Record search metrics.
     */
    public function recordSearch(string $query, array $options, array $results, float $executionTime): void
    {
        try {
            $date = now()->format('Y-m-d');
            $hour = now()->format('H');
            
            // Métriques générales
            $this->incrementMetric("searches:total:{$date}");
            $this->incrementMetric("searches:hourly:{$date}:{$hour}");
            
            // Métriques par type de recherche
            $types = $options['types'] ?? ['professional_profiles', 'service_offers', 'achievements'];
            foreach ($types as $type) {
                $this->incrementMetric("searches:type:{$type}:{$date}");
            }
            
            // Métriques de performance
            $this->recordPerformanceMetric($executionTime, $date);
            
            // Métriques de résultats
            $totalResults = $results['total_count'] ?? 0;
            $this->recordResultsMetric($totalResults, $date);
            
            // Métriques de requêtes
            $this->recordQueryMetrics($query, $date);
            
        } catch (\Exception $e) {
            Log::warning('Failed to record search metrics', [
                'query' => $query,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Record suggestion metrics.
     */
    public function recordSuggestion(string $query, array $suggestions, float $executionTime): void
    {
        try {
            $date = now()->format('Y-m-d');
            
            $this->incrementMetric("suggestions:total:{$date}");
            $this->recordPerformanceMetric($executionTime, $date, 'suggestions');
            
            // Enregistrer le nombre de suggestions retournées
            $count = count($suggestions);
            $this->addToAverageMetric("suggestions:count:{$date}", $count);
            
        } catch (\Exception $e) {
            Log::warning('Failed to record suggestion metrics', [
                'query' => $query,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Record cache hit/miss.
     */
    public function recordCacheHit(string $type, bool $hit): void
    {
        try {
            $date = now()->format('Y-m-d');
            $status = $hit ? 'hit' : 'miss';
            
            $this->incrementMetric("cache:{$type}:{$status}:{$date}");
            $this->incrementMetric("cache:total:{$status}:{$date}");
            
        } catch (\Exception $e) {
            Log::warning('Failed to record cache metrics', [
                'type' => $type,
                'hit' => $hit,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get search metrics for a date range.
     */
    public function getMetrics(string $startDate = null, string $endDate = null): array
    {
        $startDate = $startDate ?? now()->subDays(7)->format('Y-m-d');
        $endDate = $endDate ?? now()->format('Y-m-d');
        
        try {
            $metrics = [
                'period' => [
                    'start' => $startDate,
                    'end' => $endDate
                ],
                'searches' => $this->getSearchMetrics($startDate, $endDate),
                'performance' => $this->getPerformanceMetrics($startDate, $endDate),
                'cache' => $this->getCacheMetrics($startDate, $endDate),
                'popular_queries' => $this->getPopularQueries($startDate, $endDate),
            ];
            
            return $metrics;
        } catch (\Exception $e) {
            Log::warning('Failed to get search metrics', [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Get real-time metrics.
     */
    public function getRealTimeMetrics(): array
    {
        try {
            $now = now();
            $today = $now->format('Y-m-d');
            $currentHour = $now->format('H');
            
            return [
                'current_time' => $now->toISOString(),
                'today_total' => $this->getMetricValue("searches:total:{$today}"),
                'current_hour' => $this->getMetricValue("searches:hourly:{$today}:{$currentHour}"),
                'cache_hit_rate' => $this->getCacheHitRate($today),
                'avg_response_time' => $this->getAverageResponseTime($today),
                'top_searches_today' => $this->getTopSearchesToday(),
            ];
        } catch (\Exception $e) {
            Log::warning('Failed to get real-time metrics', [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Clean old metrics.
     */
    public function cleanOldMetrics(): int
    {
        try {
            $cutoffDate = now()->subDays($this->retentionDays)->format('Y-m-d');
            $pattern = $this->prefix . '*';
            $keys = Cache::getRedis()->keys($pattern);
            
            $deletedCount = 0;
            foreach ($keys as $key) {
                // Extraire la date de la clé si possible
                if (preg_match('/(\d{4}-\d{2}-\d{2})/', $key, $matches)) {
                    $keyDate = $matches[1];
                    if ($keyDate < $cutoffDate) {
                        Cache::forget(str_replace($this->prefix, '', $key));
                        $deletedCount++;
                    }
                }
            }
            
            Log::info('Cleaned old search metrics', [
                'deleted_count' => $deletedCount,
                'cutoff_date' => $cutoffDate
            ]);
            
            return $deletedCount;
        } catch (\Exception $e) {
            Log::warning('Failed to clean old metrics', [
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * Increment a metric counter.
     */
    protected function incrementMetric(string $key): void
    {
        $fullKey = $this->prefix . $key;
        Cache::increment($fullKey, 1);
        
        // Définir l'expiration pour éviter l'accumulation
        if (!Cache::has($fullKey . ':ttl')) {
            Cache::put($fullKey . ':ttl', true, now()->addDays($this->retentionDays));
        }
    }

    /**
     * Record performance metric.
     */
    protected function recordPerformanceMetric(float $executionTime, string $date, string $type = 'search'): void
    {
        $key = "performance:{$type}:{$date}";
        $this->addToAverageMetric($key, $executionTime);
    }

    /**
     * Record results metric.
     */
    protected function recordResultsMetric(int $totalResults, string $date): void
    {
        $key = "results:count:{$date}";
        $this->addToAverageMetric($key, $totalResults);
        
        // Catégoriser les résultats
        if ($totalResults === 0) {
            $this->incrementMetric("results:empty:{$date}");
        } elseif ($totalResults < 10) {
            $this->incrementMetric("results:few:{$date}");
        } else {
            $this->incrementMetric("results:many:{$date}");
        }
    }

    /**
     * Record query metrics.
     */
    protected function recordQueryMetrics(string $query, string $date): void
    {
        $length = strlen($query);
        $words = str_word_count($query);
        
        $this->addToAverageMetric("queries:length:{$date}", $length);
        $this->addToAverageMetric("queries:words:{$date}", $words);
        
        // Catégoriser par longueur
        if ($length < 5) {
            $this->incrementMetric("queries:short:{$date}");
        } elseif ($length < 20) {
            $this->incrementMetric("queries:medium:{$date}");
        } else {
            $this->incrementMetric("queries:long:{$date}");
        }
    }

    /**
     * Add value to average metric.
     */
    protected function addToAverageMetric(string $key, float $value): void
    {
        $fullKey = $this->prefix . $key;
        $countKey = $fullKey . ':count';
        $sumKey = $fullKey . ':sum';
        
        Cache::increment($countKey, 1);
        Cache::increment($sumKey, $value);
    }

    /**
     * Get metric value.
     */
    protected function getMetricValue(string $key): int
    {
        return Cache::get($this->prefix . $key, 0);
    }

    /**
     * Get average metric value.
     */
    protected function getAverageMetricValue(string $key): float
    {
        $fullKey = $this->prefix . $key;
        $count = Cache::get($fullKey . ':count', 0);
        $sum = Cache::get($fullKey . ':sum', 0);
        
        return $count > 0 ? $sum / $count : 0;
    }

    /**
     * Get search metrics for date range.
     */
    protected function getSearchMetrics(string $startDate, string $endDate): array
    {
        $metrics = [];
        $current = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        
        while ($current <= $end) {
            $date = $current->format('Y-m-d');
            $metrics[$date] = [
                'total' => $this->getMetricValue("searches:total:{$date}"),
                'by_type' => [
                    'professional_profiles' => $this->getMetricValue("searches:type:professional_profiles:{$date}"),
                    'service_offers' => $this->getMetricValue("searches:type:service_offers:{$date}"),
                    'achievements' => $this->getMetricValue("searches:type:achievements:{$date}"),
                ]
            ];
            $current->addDay();
        }
        
        return $metrics;
    }

    /**
     * Get performance metrics for date range.
     */
    protected function getPerformanceMetrics(string $startDate, string $endDate): array
    {
        $metrics = [];
        $current = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        
        while ($current <= $end) {
            $date = $current->format('Y-m-d');
            $metrics[$date] = [
                'avg_response_time' => $this->getAverageMetricValue("performance:search:{$date}"),
                'avg_suggestion_time' => $this->getAverageMetricValue("performance:suggestions:{$date}"),
            ];
            $current->addDay();
        }
        
        return $metrics;
    }

    /**
     * Get cache metrics for date range.
     */
    protected function getCacheMetrics(string $startDate, string $endDate): array
    {
        $metrics = [];
        $current = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        
        while ($current <= $end) {
            $date = $current->format('Y-m-d');
            $hits = $this->getMetricValue("cache:total:hit:{$date}");
            $misses = $this->getMetricValue("cache:total:miss:{$date}");
            $total = $hits + $misses;
            
            $metrics[$date] = [
                'hits' => $hits,
                'misses' => $misses,
                'hit_rate' => $total > 0 ? ($hits / $total) * 100 : 0,
            ];
            $current->addDay();
        }
        
        return $metrics;
    }

    /**
     * Get popular queries for date range.
     */
    protected function getPopularQueries(string $startDate, string $endDate): array
    {
        // Cette méthode pourrait être améliorée pour agréger sur une période
        // Pour l'instant, on retourne les recherches populaires d'aujourd'hui
        return app(SearchCacheService::class)->getPopularSearches(10);
    }

    /**
     * Get cache hit rate for today.
     */
    protected function getCacheHitRate(string $date): float
    {
        $hits = $this->getMetricValue("cache:total:hit:{$date}");
        $misses = $this->getMetricValue("cache:total:miss:{$date}");
        $total = $hits + $misses;
        
        return $total > 0 ? ($hits / $total) * 100 : 0;
    }

    /**
     * Get average response time for today.
     */
    protected function getAverageResponseTime(string $date): float
    {
        return $this->getAverageMetricValue("performance:search:{$date}");
    }

    /**
     * Get top searches for today.
     */
    protected function getTopSearchesToday(): array
    {
        return app(SearchCacheService::class)->getPopularSearches(5);
    }
}
