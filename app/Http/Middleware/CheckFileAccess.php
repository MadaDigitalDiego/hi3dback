<?php

namespace App\Http\Middleware;

use App\Models\File;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CheckFileAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get the file from route parameters
        $file = $request->route('file');

        if (!$file instanceof File) {
            return $next($request);
        }

        $user = $request->user();

        // If not authenticated, deny access
        if (!$user) {
            Log::warning('Unauthenticated file access attempt', [
                'file_id' => $file->id,
                'ip' => $request->ip(),
                'path' => $request->path(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

        // Check if user can access the file
        if (!$file->canBeAccessedBy($user)) {
            Log::warning('Unauthorized file access attempt', [
                'file_id' => $file->id,
                'user_id' => $user->id,
                'file_owner_id' => $file->user_id,
                'file_receiver_id' => $file->receiver_id,
                'ip' => $request->ip(),
                'path' => $request->path(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Access denied'
            ], 403);
        }

        // Log successful access
        Log::info('File accessed', [
            'file_id' => $file->id,
            'user_id' => $user->id,
            'action' => $request->route()->getName(),
        ]);

        return $next($request);
    }
}

