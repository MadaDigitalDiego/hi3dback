<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * Pour les API, on retourne une réponse JSON 401 Unauthorized
     * au lieu de tenter une redirection vers une page de login.
     */
    protected function redirectTo(Request $request): ?string
    {
        // Si la requête attend une réponse JSON (API), on ne redirige pas
        // On laisse le Middleware parent gérer la réponse 401
        if ($request->expectsJson()) {
            return null;
        }

        // Pour les requêtes web, tentons de trouver une route de login
        // Si elle n'existe pas, on retourne null pour éviter l'erreur
        try {
            return route('login', [], false);
        } catch (\Exception $e) {
            // Si la route 'login' n'existe pas, retourner null
            // Ce qui produira une réponse 401 au lieu d'une erreur de route
            return null;
        }
    }

    /**
     * Handle an incoming request.
     *
     * Cette méthode surchargée assure que les API reçoivent toujours
     * une réponse JSON 401 Unauthorized, jamais une redirection.
     */
    public function handle($request, ...$args)
    {
        if (!$this->authenticator->guard($request)->check()) {
            // Pour les API, retourner JSON 401
            if ($request->expectsJson() || $request->is('api/*')) {
                return new JsonResponse([
                    'message' => 'Unauthorized. Please login to access this resource.',
                    'error' => 'unauthenticated',
                ], 401);
            }
        }

        return parent::handle($request, ...$args);
    }
}
