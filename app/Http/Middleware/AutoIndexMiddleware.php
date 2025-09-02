<?php

namespace App\Http\Middleware;

use App\Jobs\IndexSearchableModelsJob;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AutoIndexMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only trigger indexation for successful responses
        if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
            $this->checkAndTriggerIndexation($request);
        }

        return $response;
    }

    /**
     * Check if indexation should be triggered based on the request
     */
    private function checkAndTriggerIndexation(Request $request): void
    {
        // Define routes that should trigger indexation
        $indexationTriggers = [
            // Professional profiles
            'professional-profiles.store',
            'professional-profiles.update',
            'professional-profiles.destroy',
            
            // Service offers
            'service-offers.store',
            'service-offers.update',
            'service-offers.destroy',
            
            // Achievements
            'achievements.store',
            'achievements.update',
            'achievements.destroy',
        ];

        $routeName = $request->route()?->getName();

        if (in_array($routeName, $indexationTriggers)) {
            $this->scheduleIndexation($routeName, $request);
        }
    }

    /**
     * Schedule indexation with rate limiting
     */
    private function scheduleIndexation(string $routeName, Request $request): void
    {
        // Rate limiting: only allow indexation once per minute per model type
        $modelType = $this->getModelTypeFromRoute($routeName);
        $cacheKey = "auto_index_{$modelType}";

        if (Cache::has($cacheKey)) {
            Log::debug("Indexation rate limited for {$modelType}");
            return;
        }

        // Set rate limit cache (1 minute)
        Cache::put($cacheKey, true, 60);

        // Dispatch indexation job for specific model
        IndexSearchableModelsJob::dispatch($modelType, false)
            ->delay(now()->addSeconds(30)) // Delay to batch multiple changes
            ->onQueue('indexation');

        Log::info("Scheduled indexation for {$modelType}", [
            'route' => $routeName,
            'user_id' => $request->user()?->id
        ]);
    }

    /**
     * Extract model type from route name
     */
    private function getModelTypeFromRoute(string $routeName): string
    {
        if (str_contains($routeName, 'professional-profiles')) {
            return 'professional_profiles';
        }
        
        if (str_contains($routeName, 'service-offers')) {
            return 'service_offers';
        }
        
        if (str_contains($routeName, 'achievements')) {
            return 'achievements';
        }

        return 'all';
    }
}
