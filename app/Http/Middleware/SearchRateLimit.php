<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SearchRateLimit
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $maxAttempts = '60', string $decayMinutes = '1'): Response
    {
        // Créer une clé unique basée sur l'utilisateur ou l'IP
        $key = $this->resolveRequestSignature($request);

        // Vérifier si la limite est atteinte
        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);

            return response()->json([
                'success' => false,
                'message' => 'Trop de requêtes de recherche. Veuillez réessayer dans ' . $seconds . ' secondes.',
                'retry_after' => $seconds,
            ], 429);
        }

        // Incrémenter le compteur
        RateLimiter::hit($key, $decayMinutes * 60);

        $response = $next($request);

        // Ajouter des headers informatifs
        $response->headers->set('X-RateLimit-Limit', $maxAttempts);
        $response->headers->set('X-RateLimit-Remaining', max(0, $maxAttempts - RateLimiter::attempts($key)));

        return $response;
    }

    /**
     * Resolve request signature.
     */
    protected function resolveRequestSignature(Request $request): string
    {
        if (Auth::check()) {
            // Pour les utilisateurs connectés, utiliser l'ID utilisateur
            return 'search_rate_limit:user:' . Auth::id();
        }

        // Pour les utilisateurs anonymes, utiliser l'IP
        return 'search_rate_limit:ip:' . $request->ip();
    }
}
