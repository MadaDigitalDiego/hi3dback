<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HandleCorsOptions
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Handle preflight OPTIONS requests
        if ($request->getMethod() === 'OPTIONS') {
            $origin = $request->header('Origin');

            // Validate origin against CORS configuration
            if (!$this->isOriginAllowed($origin)) {
                // Return 403 if origin is not allowed
                return response('Forbidden', 403);
            }

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

            return response('', 200)
                ->header('Access-Control-Allow-Origin', $origin)
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', implode(', ', $allowedHeaders))
                ->header('Access-Control-Expose-Headers', implode(', ', $exposedHeaders))
                ->header('Access-Control-Allow-Credentials', 'true')
                ->header('Access-Control-Max-Age', '86400'); // 24 hours
        }

        return $next($request);
    }

    /**
     * Check if the given origin is allowed according to CORS configuration
     *
     * @param string|null $origin
     * @return bool
     */
    private function isOriginAllowed(?string $origin): bool
    {
        if (!$origin) {
            return false;
        }

        $corsConfig = config('cors');

        // Check against allowed_origins list
        $allowedOrigins = $corsConfig['allowed_origins'] ?? [];
        if (in_array($origin, $allowedOrigins, true)) {
            return true;
        }

        // Check against allowed_origins_patterns
        $patterns = $corsConfig['allowed_origins_patterns'] ?? [];
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $origin)) {
                return true;
            }
        }

        return false;
    }
}
