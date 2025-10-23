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
            $response = response()->json([
                'success' => false,
                'message' => "File size exceeds maximum allowed size of {$maxUploadSizeMB}MB",
                'max_size_mb' => $maxUploadSizeMB,
                'received_size_mb' => round($contentLength / 1024 / 1024, 2),
            ], 413); // 413 Payload Too Large

            // Add CORS headers to error response
            return $this->addCorsHeaders($response, $request);
        }

        // Add custom headers for client-side tracking
        $response = $next($request);

        // Add headers to response for client-side use
        $response->header('X-Max-Upload-Size', $maxUploadSizeMB . 'M');
        $response->header('X-Upload-Timeout', '600');

        return $response;
    }

    /**
     * Add CORS headers to response
     *
     * @param Response $response
     * @param Request $request
     * @return Response
     */
    private function addCorsHeaders(Response $response, Request $request): Response
    {
        $allowedHeaders = [
            'Accept',
            'Authorization',
            'Content-Type',
            'X-Requested-With',
            'X-CSRF-TOKEN',
            'X-XSRF-TOKEN',
            'Origin',
            'Cache-Control',
            'Pragma',
            'Content-Length',
            'X-Content-Length',
            'X-File-Size',
            'X-File-Name',
            'X-File-Type',
        ];

        $exposedHeaders = [
            'Content-Length',
            'X-Content-Length',
            'X-File-Size',
            'X-File-Name',
            'X-File-Type',
        ];

        return $response
            ->header('Access-Control-Allow-Origin', $request->header('Origin') ?? '*')
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
            ->header('Access-Control-Allow-Headers', implode(', ', $allowedHeaders))
            ->header('Access-Control-Expose-Headers', implode(', ', $exposedHeaders))
            ->header('Access-Control-Allow-Credentials', 'true');
    }
}

