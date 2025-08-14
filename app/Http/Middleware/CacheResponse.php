<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class CacheResponse
{
    /**
     * Durée de mise en cache par défaut en secondes (5 minutes)
     */
    protected $cacheDuration = 300;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  int|null  $duration
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, $duration = null)
    {
        // Ne pas mettre en cache les requêtes non GET
        if (!$request->isMethod('GET')) {
            return $next($request);
        }

        // Définir la durée de mise en cache
        $cacheDuration = $duration ? (int) $duration : $this->cacheDuration;

        // Générer une clé de cache unique basée sur l'URL et les paramètres de requête
        $cacheKey = 'api_response_' . md5($request->fullUrl() . '|' . json_encode($request->all()));

        // Vérifier si la réponse est déjà en cache
        if (Cache::has($cacheKey)) {
            return response()->json(
                Cache::get($cacheKey),
                200,
                ['X-Cache' => 'HIT']
            );
        }

        // Exécuter la requête
        $response = $next($request);

        // Ne mettre en cache que les réponses réussies
        if ($response->getStatusCode() === 200) {
            // Récupérer le contenu de la réponse
            $content = json_decode($response->getContent(), true);

            // Mettre en cache la réponse
            Cache::put($cacheKey, $content, $cacheDuration);

            // Ajouter un en-tête pour indiquer que c'est une réponse non mise en cache
            $response->headers->set('X-Cache', 'MISS');
        }

        return $response;
    }
}
