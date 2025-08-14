<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\GlobalSearchService;
use App\Services\SearchCacheService;
use App\Services\SearchMetricsService;
use App\Models\ProfessionalProfile;
use App\Models\ServiceOffer;
use App\Models\Achievement;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class SearchController extends Controller
{
    protected GlobalSearchService $searchService;
    protected SearchCacheService $cacheService;
    protected SearchMetricsService $metricsService;

    public function __construct(
        GlobalSearchService $searchService,
        SearchCacheService $cacheService,
        SearchMetricsService $metricsService
    ) {
        $this->searchService = $searchService;
        $this->cacheService = $cacheService;
        $this->metricsService = $metricsService;
    }

    /**
     * Global search across all models.
     */
    public function globalSearch(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:2|max:255',
            'per_page' => 'integer|min:1|max:100',
            'page' => 'integer|min:1',
            'types' => 'array',
            'types.*' => Rule::in(['professional_profiles', 'service_offers', 'achievements']),
            'filters' => 'array',
            'filters.city' => 'string|max:100',
            'filters.availability_status' => Rule::in(['available', 'unavailable', 'busy']),
            'filters.min_experience' => 'integer|min:0',
            'filters.max_hourly_rate' => 'numeric|min:0',
            'filters.max_price' => 'numeric|min:0',
            'filters.categories' => 'array',
            'filters.organization' => 'string|max:100',
            'filters.date_from' => 'date',
        ]);

        try {
            $query = $request->input('q');
            $options = [
                'per_page' => $request->input('per_page', 15),
                'page' => $request->input('page', 1),
                'types' => $request->input('types', ['professional_profiles', 'service_offers', 'achievements']),
                'filters' => $request->input('filters', []),
            ];

            $results = $this->searchService->search($query, $options);

            return response()->json([
                'success' => true,
                'data' => $results,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la recherche.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Search only in professional profiles.
     */
    public function searchProfessionals(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:2|max:255',
            'filters' => 'array',
            'filters.city' => 'string|max:100',
            'filters.availability_status' => Rule::in(['available', 'unavailable', 'busy']),
            'filters.min_experience' => 'integer|min:0',
            'filters.max_hourly_rate' => 'numeric|min:0',
        ]);

        try {
            $query = $request->input('q');
            $filters = $request->input('filters', []);

            $results = $this->searchService->searchProfessionalProfiles($query, $filters);

            return response()->json([
                'success' => true,
                'data' => [
                    'query' => $query,
                    'count' => $results->count(),
                    'results' => $results,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la recherche de professionnels.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Search only in service offers.
     */
    public function searchServices(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:2|max:255',
            'filters' => 'array',
            'filters.max_price' => 'numeric|min:0',
            'filters.categories' => 'array',
        ]);

        try {
            $query = $request->input('q');
            $filters = $request->input('filters', []);

            $results = $this->searchService->searchServiceOffers($query, $filters);

            return response()->json([
                'success' => true,
                'data' => [
                    'query' => $query,
                    'count' => $results->count(),
                    'results' => $results,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la recherche de services.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Search only in achievements.
     */
    public function searchAchievements(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:2|max:255',
            'filters' => 'array',
            'filters.organization' => 'string|max:100',
            'filters.date_from' => 'date',
        ]);

        try {
            $query = $request->input('q');
            $filters = $request->input('filters', []);

            $results = $this->searchService->searchAchievements($query, $filters);

            return response()->json([
                'success' => true,
                'data' => [
                    'query' => $query,
                    'count' => $results->count(),
                    'results' => $results,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la recherche de réalisations.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get search suggestions.
     */
    public function suggestions(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:1|max:255',
            'limit' => 'integer|min:1|max:10',
        ]);

        try {
            $query = $request->input('q');
            $limit = $request->input('limit', 5);

            $suggestions = $this->searchService->getSuggestions($query, $limit);

            return response()->json([
                'success' => true,
                'data' => [
                    'query' => $query,
                    'suggestions' => $suggestions,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des suggestions.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get search statistics.
     */
    public function stats(): JsonResponse
    {
        try {
            // Vérifier le cache d'abord
            if ($this->cacheService->isCacheEnabled()) {
                $cachedStats = $this->cacheService->getStats();
                if ($cachedStats !== null) {
                    return response()->json([
                        'success' => true,
                        'data' => $cachedStats,
                    ]);
                }
            }

            $stats = [
                'total_professionals' => ProfessionalProfile::count(),
                'total_services' => ServiceOffer::count(),
                'total_achievements' => Achievement::count(),
                'searchable_professionals' => ProfessionalProfile::where('completion_percentage', '>=', 50)->count(),
                'active_services' => ServiceOffer::where('status', 'active')->where('is_private', false)->count(),
                'popular_searches' => $this->cacheService->getPopularSearches(5),
                'cache_stats' => $this->cacheService->getCacheStats(),
            ];

            // Mettre en cache les statistiques
            if ($this->cacheService->isCacheEnabled()) {
                $this->cacheService->cacheStats($stats);
            }

            return response()->json([
                'success' => true,
                'data' => $stats,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get popular searches.
     */
    public function popularSearches(Request $request): JsonResponse
    {
        $request->validate([
            'limit' => 'integer|min:1|max:20',
        ]);

        try {
            $limit = $request->input('limit', 10);
            $popularSearches = $this->cacheService->getPopularSearches($limit);

            return response()->json([
                'success' => true,
                'data' => [
                    'popular_searches' => $popularSearches,
                    'date' => date('Y-m-d'),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des recherches populaires.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Clear search cache (admin only).
     */
    public function clearCache(Request $request): JsonResponse
    {
        $request->validate([
            'pattern' => 'string|max:100',
        ]);

        try {
            $pattern = $request->input('pattern');
            $cleared = $this->cacheService->clearSearchCache($pattern);

            return response()->json([
                'success' => $cleared,
                'message' => $cleared ? 'Cache vidé avec succès.' : 'Erreur lors du vidage du cache.',
                'pattern' => $pattern,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du vidage du cache.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get search metrics.
     */
    public function metrics(Request $request): JsonResponse
    {
        $request->validate([
            'start_date' => 'date|before_or_equal:today',
            'end_date' => 'date|after_or_equal:start_date|before_or_equal:today',
        ]);

        try {
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');

            $metrics = $this->metricsService->getMetrics($startDate, $endDate);

            return response()->json([
                'success' => true,
                'data' => $metrics,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des métriques.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get real-time metrics.
     */
    public function realTimeMetrics(): JsonResponse
    {
        try {
            $metrics = $this->metricsService->getRealTimeMetrics();

            return response()->json([
                'success' => true,
                'data' => $metrics,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des métriques en temps réel.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Clean old metrics (admin only).
     */
    public function cleanMetrics(): JsonResponse
    {
        try {
            $deletedCount = $this->metricsService->cleanOldMetrics();

            return response()->json([
                'success' => true,
                'message' => "Métriques anciennes supprimées avec succès.",
                'deleted_count' => $deletedCount,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du nettoyage des métriques.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
