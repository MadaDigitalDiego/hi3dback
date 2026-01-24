<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\PersonalAccessSession;
use Symfony\Component\HttpFoundation\Response;

class SessionActivity
{
    /**
     * Handle an incoming request.
     *
     * Updates the session activity timestamp on each request.
     * This middleware should be applied AFTER auth:sanctum middleware.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only track activity for authenticated users
        if ($request->user()) {
            $token = $request->user()->currentAccessToken();

            if ($token) {
                // Find the session for this token
                $session = PersonalAccessSession::where('token_id', $token->id)
                    ->where('user_id', $request->user()->id)
                    ->first();

                if ($session && $session->is_active) {
                    // Update the last activity timestamp
                    $session->updateActivity();
                }
            }
        }

        return $next($request);
    }
}

