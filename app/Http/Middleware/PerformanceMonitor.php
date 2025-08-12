<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class PerformanceMonitor
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Enregistrer le temps de début
        $startTime = microtime(true);
        
        // Enregistrer l'utilisation de la mémoire au début
        $startMemory = memory_get_usage();
        
        // Exécuter la requête
        $response = $next($request);
        
        // Calculer le temps d'exécution
        $executionTime = microtime(true) - $startTime;
        
        // Calculer l'utilisation de la mémoire
        $memoryUsage = memory_get_usage() - $startMemory;
        
        // Formater les données de performance
        $performanceData = [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'execution_time' => round($executionTime * 1000, 2) . ' ms', // Convertir en millisecondes
            'memory_usage' => round($memoryUsage / 1024 / 1024, 2) . ' MB', // Convertir en mégaoctets
            'status_code' => $response->getStatusCode(),
            'user_id' => $request->user() ? $request->user()->id : 'guest',
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ];
        
        // Ajouter les données de performance aux en-têtes de réponse
        $response->headers->set('X-Execution-Time', $performanceData['execution_time']);
        $response->headers->set('X-Memory-Usage', $performanceData['memory_usage']);
        
        // Enregistrer les données de performance dans les logs
        if ($executionTime > 1) { // Enregistrer seulement les requêtes qui prennent plus d'une seconde
            Log::channel('performance')->info('Requête lente détectée', $performanceData);
        }
        
        // Enregistrer toutes les données de performance dans un fichier de log dédié
        Log::channel('performance')->debug('Performance', $performanceData);
        
        return $response;
    }
}
