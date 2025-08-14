<?php

namespace App\Services;

use App\Models\ProfessionalProfile;
use App\Models\ServiceOffer;
use App\Models\Achievement;
use App\Services\SearchCacheService;
use App\Services\SearchMetricsService;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class GlobalSearchService
{
    protected SearchCacheService $cacheService;
    protected SearchMetricsService $metricsService;

    public function __construct(SearchCacheService $cacheService, SearchMetricsService $metricsService)
    {
        $this->cacheService = $cacheService;
        $this->metricsService = $metricsService;
    }

    /**
     * Search across all searchable models.
     */
    public function search(string $query, array $options = []): array
    {
        $startTime = microtime(true);

        // Vérifier le cache d'abord
        if ($this->cacheService->isCacheEnabled()) {
            $cachedResults = $this->cacheService->getSearchResults($query, $options);
            if ($cachedResults !== null) {
                // Tracker la recherche même si elle vient du cache
                $this->cacheService->trackSearch($query);
                $this->metricsService->recordCacheHit('search', true);
                return $cachedResults;
            }
            $this->metricsService->recordCacheHit('search', false);
        }

        $perPage = $options['per_page'] ?? 15;
        $page = $options['page'] ?? 1;
        $types = $options['types'] ?? ['professional_profiles', 'service_offers', 'achievements'];
        $filters = $options['filters'] ?? [];

        $results = [];
        $totalCount = 0;

        // Search in ProfessionalProfiles
        if (in_array('professional_profiles', $types)) {
            $professionalResults = $this->searchProfessionalProfiles($query, $filters);
            $results['professional_profiles'] = $professionalResults;
            $totalCount += $professionalResults->count();
        }

        // Search in ServiceOffers
        if (in_array('service_offers', $types)) {
            $serviceResults = $this->searchServiceOffers($query, $filters);
            $results['service_offers'] = $serviceResults;
            $totalCount += $serviceResults->count();
        }

        // Search in Achievements
        if (in_array('achievements', $types)) {
            $achievementResults = $this->searchAchievements($query, $filters);
            $results['achievements'] = $achievementResults;
            $totalCount += $achievementResults->count();
        }

        // Combine and format results
        $combinedResults = $this->combineResults($results, $perPage, $page);

        $searchResults = [
            'query' => $query,
            'total_count' => $totalCount,
            'results_by_type' => $results,
            'combined_results' => $combinedResults,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $totalCount,
                'last_page' => ceil($totalCount / $perPage),
            ],
        ];

        // Calculer le temps d'exécution
        $executionTime = microtime(true) - $startTime;

        // Mettre en cache les résultats et tracker la recherche
        if ($this->cacheService->isCacheEnabled()) {
            $this->cacheService->cacheSearchResults($query, $options, $searchResults);
            $this->cacheService->trackSearch($query);
        }

        // Enregistrer les métriques
        $this->metricsService->recordSearch($query, $options, $searchResults, $executionTime);

        return $searchResults;
    }

    /**
     * Search in ProfessionalProfiles.
     */
    public function searchProfessionalProfiles(string $query, array $filters = []): Collection
    {
        $searchQuery = ProfessionalProfile::search($query);

        // Build Meilisearch filter string
        $filterConditions = [];

        if (!empty($filters['city'])) {
            $filterConditions[] = "city = \"{$filters['city']}\"";
        }

        if (!empty($filters['country'])) {
            $filterConditions[] = "country = \"{$filters['country']}\"";
        }

        if (!empty($filters['availability_status'])) {
            $filterConditions[] = "availability_status = \"{$filters['availability_status']}\"";
        }

        if (!empty($filters['min_rating'])) {
            $filterConditions[] = "rating >= {$filters['min_rating']}";
        }

        if (!empty($filters['max_rating'])) {
            $filterConditions[] = "rating <= {$filters['max_rating']}";
        }

        if (!empty($filters['max_hourly_rate'])) {
            $filterConditions[] = "hourly_rate <= {$filters['max_hourly_rate']}";
        }

        if (!empty($filters['min_hourly_rate'])) {
            $filterConditions[] = "hourly_rate >= {$filters['min_hourly_rate']}";
        }

        if (!empty($filters['min_experience'])) {
            $filterConditions[] = "years_of_experience >= {$filters['min_experience']}";
        }

        if (!empty($filters['max_experience'])) {
            $filterConditions[] = "years_of_experience <= {$filters['max_experience']}";
        }

        if (!empty($filters['skills'])) {
            foreach ($filters['skills'] as $skill) {
                $filterConditions[] = "skills = \"{$skill}\"";
            }
        }

        if (!empty($filters['languages'])) {
            foreach ($filters['languages'] as $language) {
                $filterConditions[] = "languages = \"{$language}\"";
            }
        }

        // Apply filters using Meilisearch syntax
        if (!empty($filterConditions)) {
            $filterString = implode(' AND ', $filterConditions);
            $searchQuery->options(['filter' => $filterString]);
        }

        $results = $searchQuery->get();

        return $results->map(function ($profile) {
            return $this->formatProfessionalProfile($profile);
        });
    }

    /**
     * Search in ServiceOffers.
     */
    public function searchServiceOffers(string $query, array $filters = []): Collection
    {
        $searchQuery = ServiceOffer::search($query);

        // Build Meilisearch filter string
        $filterConditions = [];

        if (!empty($filters['max_price'])) {
            $filterConditions[] = "price <= {$filters['max_price']}";
        }

        if (!empty($filters['min_price'])) {
            $filterConditions[] = "price >= {$filters['min_price']}";
        }

        if (!empty($filters['categories'])) {
            foreach ($filters['categories'] as $category) {
                $filterConditions[] = "categories = \"{$category}\"";
            }
        }

        if (!empty($filters['status'])) {
            $filterConditions[] = "status = \"{$filters['status']}\"";
        }

        if (!empty($filters['user_id'])) {
            $filterConditions[] = "user_id = {$filters['user_id']}";
        }

        // Apply filters using Meilisearch syntax
        if (!empty($filterConditions)) {
            $filterString = implode(' AND ', $filterConditions);
            $searchQuery->options(['filter' => $filterString]);
        }

        $results = $searchQuery->get();

        return $results->map(function ($service) {
            return $this->formatServiceOffer($service);
        });
    }

    /**
     * Search in Achievements.
     */
    public function searchAchievements(string $query, array $filters = []): Collection
    {
        $searchQuery = Achievement::search($query);

        // Apply filters
        if (!empty($filters['organization'])) {
            $searchQuery->where('organization', $filters['organization']);
        }

        if (!empty($filters['date_from'])) {
            $searchQuery->where('date_obtained', '>=', $filters['date_from']);
        }

        $results = $searchQuery->get();

        return $results->map(function ($achievement) {
            return $this->formatAchievement($achievement);
        });
    }

    /**
     * Combine results from different models with pagination.
     */
    private function combineResults(array $results, int $perPage, int $page): LengthAwarePaginator
    {
        $combined = collect();

        foreach ($results as $type => $typeResults) {
            $combined = $combined->merge($typeResults);
        }

        // Sort by relevance (you can customize this)
        $combined = $combined->sortByDesc(function ($item) {
            return $item['relevance_score'] ?? 0;
        });

        // Paginate
        $offset = ($page - 1) * $perPage;
        $items = $combined->slice($offset, $perPage)->values();

        return new LengthAwarePaginator(
            $items,
            $combined->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }

    /**
     * Format ProfessionalProfile for search results.
     */
    private function formatProfessionalProfile(ProfessionalProfile $profile): array
    {
        return [
            'id' => $profile->id,
            'type' => 'professional_profile',
            'title' => $profile->title ?? $profile->profession,
            'name' => $profile->first_name . ' ' . $profile->last_name,
            'description' => $profile->bio ?? $profile->description,
            'location' => $profile->city . ', ' . $profile->country,
            'skills' => $profile->skills ?? [],
            'hourly_rate' => $profile->hourly_rate,
            'rating' => $profile->rating,
            'availability_status' => $profile->availability_status,
            'avatar' => $profile->avatar,
            'url' => "/professionals/{$profile->id}",
            'relevance_score' => $this->calculateRelevanceScore($profile, 'professional'),
        ];
    }

    /**
     * Format ServiceOffer for search results.
     */
    private function formatServiceOffer(ServiceOffer $service): array
    {
        return [
            'id' => $service->id,
            'type' => 'service_offer',
            'title' => $service->title,
            'description' => $service->description,
            'price' => $service->price,
            'execution_time' => $service->execution_time,
            'categories' => $service->categories ?? [],
            'rating' => $service->rating,
            'views' => $service->views,
            'likes' => $service->likes,
            'image' => $service->image,
            'user_name' => $service->user ? $service->user->first_name . ' ' . $service->user->last_name : null,
            'url' => "/services/{$service->id}",
            'relevance_score' => $this->calculateRelevanceScore($service, 'service'),
        ];
    }

    /**
     * Format Achievement for search results.
     */
    private function formatAchievement(Achievement $achievement): array
    {
        return [
            'id' => $achievement->id,
            'type' => 'achievement',
            'title' => $achievement->title,
            'organization' => $achievement->organization,
            'description' => $achievement->description,
            'date_obtained' => $achievement->date_obtained?->format('Y-m-d'),
            'professional_name' => $achievement->professionalProfile ?
                $achievement->professionalProfile->first_name . ' ' . $achievement->professionalProfile->last_name : null,
            'url' => "/achievements/{$achievement->id}",
            'relevance_score' => $this->calculateRelevanceScore($achievement, 'achievement'),
        ];
    }

    /**
     * Calculate relevance score for sorting.
     */
    private function calculateRelevanceScore($model, string $type): float
    {
        $score = 1.0;

        switch ($type) {
            case 'professional':
                $score += ($model->rating ?? 0) * 0.2;
                $score += ($model->completion_percentage ?? 0) * 0.01;
                $score += ($model->years_of_experience ?? 0) * 0.1;
                break;
            case 'service':
                $score += ($model->rating ?? 0) * 0.3;
                $score += ($model->likes ?? 0) * 0.01;
                $score += ($model->views ?? 0) * 0.001;
                break;
            case 'achievement':
                $score += 0.5; // Base score for achievements
                break;
        }

        return $score;
    }

    /**
     * Get search suggestions based on query.
     */
    public function getSuggestions(string $query, int $limit = 5): array
    {
        $startTime = microtime(true);

        // Vérifier le cache d'abord
        if ($this->cacheService->isCacheEnabled()) {
            $cachedSuggestions = $this->cacheService->getSuggestions($query, $limit);
            if ($cachedSuggestions !== null) {
                $this->metricsService->recordCacheHit('suggestions', true);
                return $cachedSuggestions;
            }
            $this->metricsService->recordCacheHit('suggestions', false);
        }

        $suggestions = [];

        // Get suggestions from different models
        $professionalSuggestions = ProfessionalProfile::search($query)->take($limit)->get()
            ->pluck('title')->filter()->unique()->take(2);

        $serviceSuggestions = ServiceOffer::search($query)->take($limit)->get()
            ->pluck('title')->filter()->unique()->take(2);

        $achievementSuggestions = Achievement::search($query)->take($limit)->get()
            ->pluck('title')->filter()->unique()->take(1);

        $suggestions = $professionalSuggestions
            ->merge($serviceSuggestions)
            ->merge($achievementSuggestions)
            ->take($limit)
            ->values()
            ->toArray();

        // Calculer le temps d'exécution
        $executionTime = microtime(true) - $startTime;

        // Mettre en cache les suggestions
        if ($this->cacheService->isCacheEnabled()) {
            $this->cacheService->cacheSuggestions($query, $limit, $suggestions);
        }

        // Enregistrer les métriques
        $this->metricsService->recordSuggestion($query, $suggestions, $executionTime);

        return $suggestions;
    }
}
