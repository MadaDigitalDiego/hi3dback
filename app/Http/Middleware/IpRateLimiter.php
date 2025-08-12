<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class IpRateLimiter
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  int  $maxAttempts
     * @param  int  $decayMinutes
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, $maxAttempts = 60, $decayMinutes = 1): Response
    {
        // Récupérer l'adresse IP du client
        $ip = $request->ip();
        
        // Créer une clé unique pour cette IP et cette route
        $key = 'ip_rate_limit:' . $ip . ':' . $request->route()->getName() ?? $request->path();
        
        // Vérifier si l'IP est dans la liste blanche
        if ($this->isWhitelisted($ip)) {
            return $next($request);
        }
        
        // Vérifier si l'IP est dans la liste noire
        if ($this->isBlacklisted($ip)) {
            Log::warning("Tentative d'accès depuis une IP blacklistée: {$ip}");
            return response()->json([
                'message' => 'Accès refusé.',
            ], 403);
        }
        
        // Appliquer la limitation de taux
        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);
            
            Log::warning("Limitation de taux dépassée pour l'IP: {$ip}");
            
            return response()->json([
                'message' => "Trop de requêtes. Veuillez réessayer dans {$seconds} secondes.",
                'retry_after' => $seconds,
            ], 429)->header('Retry-After', $seconds);
        }
        
        // Incrémenter le compteur de tentatives
        RateLimiter::hit($key, $decayMinutes * 60);
        
        // Ajouter les en-têtes de limitation de taux à la réponse
        $response = $next($request);
        $response->headers->add([
            'X-RateLimit-Limit' => $maxAttempts,
            'X-RateLimit-Remaining' => $maxAttempts - RateLimiter::attempts($key),
        ]);
        
        return $response;
    }
    
    /**
     * Vérifier si une adresse IP est dans la liste blanche.
     *
     * @param  string  $ip
     * @return bool
     */
    private function isWhitelisted(string $ip): bool
    {
        $whitelist = config('security.ip_whitelist', []);
        
        // Ajouter localhost à la liste blanche par défaut
        $whitelist = array_merge($whitelist, ['127.0.0.1', '::1']);
        
        return in_array($ip, $whitelist);
    }
    
    /**
     * Vérifier si une adresse IP est dans la liste noire.
     *
     * @param  string  $ip
     * @return bool
     */
    private function isBlacklisted(string $ip): bool
    {
        $blacklist = config('security.ip_blacklist', []);
        
        return in_array($ip, $blacklist);
    }
}
