<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateLargeFileUpload
{
    /**
     * Handle an incoming request for large file uploads.
     * This middleware validates request headers and sizes before processing.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only validate file upload routes
        if (!$request->is('api/files/upload')) {
            return $next($request);
        }

        // Get the maximum upload size from config (in MB)
        $maxUploadSizeMB = config('filesystems.file_management.max_upload_size', 500);
        $maxUploadSizeBytes = $maxUploadSizeMB * 1024 * 1024;

        // Check Content-Length header
        $contentLength = $request->header('Content-Length');
        if ($contentLength && $contentLength > $maxUploadSizeBytes) {
            return response()->json([
                'success' => false,
                'message' => "File size exceeds maximum allowed size of {$maxUploadSizeMB}MB",
                'max_size_mb' => $maxUploadSizeMB,
                'received_size_mb' => round($contentLength / 1024 / 1024, 2),
            ], 413); // 413 Payload Too Large
        }

        // Add custom headers for client-side tracking
        $response = $next($request);

        // Add headers to response for client-side use
        $response->header('X-Max-Upload-Size', $maxUploadSizeMB . 'M');
        $response->header('X-Upload-Timeout', '600');

        return $response;
    }
}

