<?php

namespace App\Http\Middleware;

use App\Models\Message;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CheckMessageAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get the message ID from route parameters
        $messageId = $request->route('messageId');

        if (!$messageId) {
            return $next($request);
        }

        $user = $request->user();

        // If not authenticated, deny access
        if (!$user) {
            Log::warning('Unauthenticated message access attempt', [
                'message_id' => $messageId,
                'ip' => $request->ip(),
                'path' => $request->path(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

        // Get the message
        $message = Message::find($messageId);

        if (!$message) {
            return response()->json([
                'success' => false,
                'message' => 'Message not found'
            ], 404);
        }

        // Check if user is sender or receiver of the message
        $isParticipant = $message->sender_id === $user->id || $message->receiver_id === $user->id;

        if (!$isParticipant && !$user->isAdmin() && !$user->isSuperAdmin()) {
            Log::warning('Unauthorized message access attempt', [
                'message_id' => $message->id,
                'user_id' => $user->id,
                'sender_id' => $message->sender_id,
                'receiver_id' => $message->receiver_id,
                'ip' => $request->ip(),
                'path' => $request->path(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Access denied'
            ], 403);
        }

        // Log successful access
        Log::info('Message accessed', [
            'message_id' => $message->id,
            'user_id' => $user->id,
            'action' => $request->route()->getName(),
        ]);

        return $next($request);
    }
}

