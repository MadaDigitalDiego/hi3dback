<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Vérifier si l'utilisateur est connecté
        if (!Auth::check()) {
            return redirect()->route('filament.admin.auth.login');
        }

        // Vérifier si l'utilisateur a un rôle admin
        $user = Auth::user();

        // Vérifier que l'utilisateur a la méthode isAdmin et un rôle défini
        if (!method_exists($user, 'isAdmin') || !$user->role || !$user->isAdmin()) {
            abort(403, 'Accès non autorisé. Vous devez être administrateur pour accéder à cette section.');
        }

        return $next($request);
    }
}
