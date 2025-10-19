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
                ->header('Access-Control-Allow-Origin', $request->header('Origin'))
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', implode(', ', $allowedHeaders))
                ->header('Access-Control-Expose-Headers', implode(', ', $exposedHeaders))
                ->header('Access-Control-Allow-Credentials', 'true')
                ->header('Access-Control-Max-Age', '86400'); // 24 hours
        }

        return $next($request);
    }
}
