<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SearchCacheService
{
    protected int $defaultTtl = 3600; // 1 heure
    protected int $suggestionsTtl = 7200; // 2 heures
    protected int $statsTtl = 1800; // 30 minutes
    protected string $prefix = 'search_cache:';

    /**
     * Get cached search results.
     */
    public function getSearchResults(string $query, array $options = []): ?array
    {
        $key = $this->generateSearchKey($query, $options);

        try {
            return Cache::get($key);
        } catch (\Exception $e) {
            Log::warning('Failed to get search cache', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Cache search results.
     */
    public function cacheSearchResults(string $query, array $options, array $results, ?int $ttl = null): bool
    {
        $key = $this->generateSearchKey($query, $options);
        $ttl = $ttl ?? $this->defaultTtl;

        try {
            return Cache::put($key, $results, $ttl);
        } catch (\Exception $e) {
            Log::warning('Failed to cache search results', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get cached suggestions.
     */
    public function getSuggestions(string $query, int $limit = 5): ?array
    {
        $key = $this->prefix . 'suggestions:' . md5($query . ':' . $limit);

        try {
            return Cache::get($key);
        } catch (\Exception $e) {
            Log::warning('Failed to get suggestions cache', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Cache suggestions.
     */
    public function cacheSuggestions(string $query, int $limit, array $suggestions): bool
    {
        $key = $this->prefix . 'suggestions:' . md5($query . ':' . $limit);

        try {
            return Cache::put($key, $suggestions, $this->suggestionsTtl);
        } catch (\Exception $e) {
            Log::warning('Failed to cache suggestions', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get cached stats.
     */
    public function getStats(): ?array
    {
        $key = $this->prefix . 'stats';

        try {
            return Cache::get($key);
        } catch (\Exception $e) {
            Log::warning('Failed to get stats cache', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Cache stats.
     */
    public function cacheStats(array $stats): bool
    {
        $key = $this->prefix . 'stats';

        try {
            return Cache::put($key, $stats, $this->statsTtl);
        } catch (\Exception $e) {
            Log::warning('Failed to cache stats', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Track popular searches.
     */
    public function trackSearch(string $query): void
    {
        if (strlen($query) < 2) {
            return;
        }

        $key = $this->prefix . 'popular:' . date('Y-m-d');

        try {
            // Incrémenter le compteur pour cette requête
            Cache::increment($key . ':' . md5($query), 1);

            // Définir l'expiration à la fin de la journée
            $endOfDay = now()->endOfDay()->timestamp;
            Cache::put($key . ':' . md5($query), Cache::get($key . ':' . md5($query), 0), $endOfDay);

            // Stocker aussi la requête originale pour pouvoir la récupérer
            Cache::put($key . ':query:' . md5($query), $query, $endOfDay);
        } catch (\Exception $e) {
            Log::warning('Failed to track search', [
                'query' => $query,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get popular searches for today.
     */
    public function getPopularSearches(int $limit = 10): array
    {
        // Si Redis n'est pas disponible, retourner un tableau vide
        if (!$this->isRedisAvailable()) {
            return [];
        }

        $key = $this->prefix . 'popular:' . date('Y-m-d');

        try {
            $pattern = $key . ':*';
            $keys = Cache::getRedis()->keys($pattern);

            $searches = [];
            foreach ($keys as $cacheKey) {
                if (strpos($cacheKey, ':query:') !== false) {
                    continue; // Skip query storage keys
                }

                $count = Cache::get($cacheKey, 0);
                $queryHash = str_replace($key . ':', '', $cacheKey);
                $query = Cache::get($key . ':query:' . $queryHash);

                if ($query && $count > 0) {
                    $searches[] = [
                        'query' => $query,
                        'count' => $count
                    ];
                }
            }

            // Trier par popularité
            usort($searches, function ($a, $b) {
                return $b['count'] <=> $a['count'];
            });

            return array_slice($searches, 0, $limit);
        } catch (\Exception $e) {
            Log::warning('Failed to get popular searches', [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Clear search cache.
     */
    public function clearSearchCache(?string $pattern = null): bool
    {
        if (!$this->isRedisAvailable()) {
            // Pour les autres drivers de cache, on peut seulement vider tout le cache
            Cache::flush();
            return true;
        }

        try {
            if ($pattern) {
                $keys = Cache::getRedis()->keys($this->prefix . $pattern);
                if (!empty($keys)) {
                    Cache::getRedis()->del($keys);
                }
            } else {
                $keys = Cache::getRedis()->keys($this->prefix . '*');
                if (!empty($keys)) {
                    Cache::getRedis()->del($keys);
                }
            }
            return true;
        } catch (\Exception $e) {
            Log::warning('Failed to clear search cache', [
                'pattern' => $pattern,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Generate cache key for search.
     */
    protected function generateSearchKey(string $query, array $options): string
    {
        $keyData = [
            'query' => $query,
            'options' => $options
        ];

        return $this->prefix . 'search:' . md5(json_encode($keyData));
    }

    /**
     * Check if caching is enabled.
     */
    public function isCacheEnabled(): bool
    {
        return config('cache.default') !== 'array' && config('app.env') !== 'testing';
    }

    /**
     * Check if Redis is available.
     */
    public function isRedisAvailable(): bool
    {
        try {
            return config('cache.default') === 'redis' && Cache::getRedis() !== null;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get cache statistics.
     */
    public function getCacheStats(): array
    {
        if (!$this->isRedisAvailable()) {
            return [
                'total_keys' => 0,
                'search_keys' => 0,
                'suggestion_keys' => 0,
                'stats_keys' => 0,
                'popular_keys' => 0,
                'cache_driver' => config('cache.default'),
                'redis_available' => false,
            ];
        }

        try {
            $redis = Cache::getRedis();
            $keys = $redis->keys($this->prefix . '*');

            $stats = [
                'total_keys' => count($keys),
                'search_keys' => 0,
                'suggestion_keys' => 0,
                'stats_keys' => 0,
                'popular_keys' => 0,
                'cache_driver' => config('cache.default'),
                'redis_available' => true,
            ];

            foreach ($keys as $key) {
                if (strpos($key, ':search:') !== false) {
                    $stats['search_keys']++;
                } elseif (strpos($key, ':suggestions:') !== false) {
                    $stats['suggestion_keys']++;
                } elseif (strpos($key, ':stats') !== false) {
                    $stats['stats_keys']++;
                } elseif (strpos($key, ':popular:') !== false) {
                    $stats['popular_keys']++;
                }
            }

            return $stats;
        } catch (\Exception $e) {
            Log::warning('Failed to get cache stats', [
                'error' => $e->getMessage()
            ]);
            return [
                'total_keys' => 0,
                'search_keys' => 0,
                'suggestion_keys' => 0,
                'stats_keys' => 0,
                'popular_keys' => 0,
                'cache_driver' => config('cache.default'),
                'redis_available' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
