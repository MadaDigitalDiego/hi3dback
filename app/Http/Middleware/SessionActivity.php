<?php

namespace App\Http\Middleware;

use App\Models\PersonalAccessSession;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SessionActivity
{
    /**
     * Handle an incoming request.
     *
     * Updates the session activity timestamp on each request.
     * This middleware should be applied after auth and expiration checks.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()) {
            $token = $request->user()->currentAccessToken();

            if ($token) {
                $session = PersonalAccessSession::where('token_id', $token->id)
                    ->where('user_id', $request->user()->id)
                    ->first();

                if ($session && $session->is_active) {
                    $session->updateActivity();
                }
            }
        }

        return $next($request);
    }
}
