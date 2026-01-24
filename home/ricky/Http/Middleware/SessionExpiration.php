<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\PersonalAccessSession;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class SessionExpiration
{
    /**
     * Handle an incoming request.
     *
     * Checks if the session has expired based on inactivity timeout.
     * Returns a 401 JSON response with session_expired flag if expired.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get session timeout from config, default to 30 minutes
        $timeoutMinutes = config('session.timeout', 30);

        if ($request->user()) {
            $token = $request->user()->currentAccessToken();

            if ($token) {
                // Find the session for this token
                $session = PersonalAccessSession::where('token_id', $token->id)
                    ->where('user_id', $request->user()->id)
                    ->first();

                // If session not found or inactive, force logout
                if (!$session || !$session->is_active) {
                    Log::info('Session not found or inactive for user', [
                        'user_id' => $request->user()->id,
                        'token_id' => $token->id,
                        'session_exists' => $session !== null,
                        'session_active' => $session?->is_active,
                    ]);

                    // Delete the token
                    $request->user()->tokens()->where('id', $token->id)->delete();

                    return response()->json([
                        'message' => 'Session expirée. Veuillez vous reconnecter.',
                        'session_expired' => true,
                        'redirect_to' => '/login',
                    ], 401);
                }

                // Check if session has expired due to inactivity
                if ($session->isExpired($timeoutMinutes)) {
                    Log::info('Session expired due to inactivity', [
                        'user_id' => $request->user()->id,
                        'token_id' => $token->id,
                        'last_activity' => $session->last_activity_at,
                        'timeout_minutes' => $timeoutMinutes,
                    ]);

                    // Deactivate the session
                    $session->deactivate();

                    // Delete the token
                    $request->user()->tokens()->where('id', $token->id)->delete();

                    return response()->json([
                        'message' => 'Session expirée par inactivité. Vous avez été automatiquement déconnecté.',
                        'session_expired' => true,
                        'expired_at' => $session->last_activity_at->addMinutes($timeoutMinutes)->toIso8601String(),
                        'timeout_minutes' => $timeoutMinutes,
                        'redirect_to' => '/login',
                    ], 401);
                }
            }
        }

        return $next($request);
    }
}

