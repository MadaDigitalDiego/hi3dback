<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;

class NavigationController extends Controller
{
    public function show(): JsonResponse
    {
        $apiKey = env('NAV_API_KEY');

        $authUser = null;
        $isAuthenticated = false;

        $authHeader = request()->header('Authorization');
        
        if ($apiKey) {
            if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
                return response()->json([
                    'error' => 'Unauthorized',
                    'message' => 'Bearer token required'
                ], 401);
            }

            $token = substr($authHeader, 7);
            if ($token !== $apiKey) {
                return response()->json([
                    'error' => 'Unauthorized',
                    'message' => 'Invalid token'
                ], 401);
            }
        } else {
            if ($authHeader && str_starts_with($authHeader, 'Bearer ')) {
                $token = substr($authHeader, 7);
                $accessToken = PersonalAccessToken::findToken($token);
                
                if ($accessToken) {
                    $user = $accessToken->tokenable;
                    if ($user) {
                        $authUser = [
                            'id' => $user->id,
                            'name' => $user->name,
                            'email' => $user->email,
                            'avatar' => $user->avatar ?? null,
                        ];
                        $isAuthenticated = true;
                    }
                }
            }
        }

        $frontendUrl = config('app.frontend_url', config('app.url'));
        $backendUrl = config('app.backend_url', config('app.url'));
        $apiBaseUrl = config('app.api_base_url', $backendUrl);

        $context = request()->header('X-Navigation-Context', 'default');
        $blogUrlHeader = request()->header('X-Blog-Url');
        $blogUrl = !empty($blogUrlHeader) ? $blogUrlHeader : null;

        if (!$blogUrl) {
            $origin = request()->header('origin') ?: request()->header('referer');
            if ($origin && str_contains($origin, 'blog')) {
                $blogUrl = $origin;
            } elseif (!empty(config('app.blog_url'))) {
                $blogUrl = config('app.blog_url');
            } else {
                $blogUrl = $frontendUrl . '/blog';
            }
        }

        $html = view('navigation.header', [
            'isAuthenticated' => $isAuthenticated,
            'authUser' => $authUser,
            'frontendUrl' => $frontendUrl,
            'backendUrl' => $backendUrl,
            'apiBaseUrl' => $apiBaseUrl,
            'blogUrl' => $blogUrl,
            'context' => $context,
        ])->render();

        return response()->json([
            'html' => $html,
            'authenticated' => $isAuthenticated,
            'user' => $authUser,
            'frontendUrl' => $frontendUrl,
            'backendUrl' => $backendUrl,
            'apiBaseUrl' => $apiBaseUrl,
            'blogUrl' => $blogUrl,
        ], 200, [
            'Content-Type' => 'application/json; charset=utf-8',
            'Cache-Control' => 'public, max-age=300',
        ]);
    }
}
