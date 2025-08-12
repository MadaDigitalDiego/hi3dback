<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ValidateJsonPayload
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
        // Vérifier si la requête contient du contenu JSON
        if ($request->isJson() && $request->getContent()) {
            try {
                // Tenter de décoder le JSON
                $json = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
                
                // Vérifier si le JSON est un tableau ou un objet
                if (!is_array($json)) {
                    return response()->json([
                        'message' => 'Le contenu JSON doit être un objet ou un tableau.',
                    ], 400);
                }
                
                // Vérifier la taille maximale du JSON (10 Mo)
                if (strlen($request->getContent()) > 10 * 1024 * 1024) {
                    return response()->json([
                        'message' => 'Le contenu JSON est trop volumineux. La taille maximale est de 10 Mo.',
                    ], 413);
                }
                
                // Vérifier la profondeur maximale du JSON (20 niveaux)
                if ($this->getJsonDepth($json) > 20) {
                    return response()->json([
                        'message' => 'Le contenu JSON est trop profond. La profondeur maximale est de 20 niveaux.',
                    ], 400);
                }
                
                // Remplacer le contenu de la requête par le JSON décodé
                $request->replace($json);
            } catch (\JsonException $e) {
                Log::warning('JSON invalide reçu: ' . $e->getMessage());
                return response()->json([
                    'message' => 'Le contenu JSON est invalide: ' . $e->getMessage(),
                ], 400);
            }
        }
        
        return $next($request);
    }
    
    /**
     * Calculer la profondeur d'un tableau ou d'un objet JSON.
     *
     * @param  array  $array
     * @param  int  $depth
     * @return int
     */
    private function getJsonDepth(array $array, int $depth = 1): int
    {
        $maxDepth = $depth;
        
        foreach ($array as $value) {
            if (is_array($value)) {
                $maxDepth = max($maxDepth, $this->getJsonDepth($value, $depth + 1));
            }
        }
        
        return $maxDepth;
    }
}
