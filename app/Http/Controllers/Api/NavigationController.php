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

        $appUrl = config('app.url', request()->getSchemeAndHttpHost());
        $apiBaseUrl = config('app.api_base_url', $appUrl);

        $html = view('navigation.header', [
            'isAuthenticated' => $isAuthenticated,
            'authUser' => $authUser,
            'appUrl' => $appUrl,
            'apiBaseUrl' => $apiBaseUrl,
        ])->render();

        return response()->json([
            'html' => $html,
            'authenticated' => $isAuthenticated,
            'user' => $authUser,
            'appUrl' => $appUrl,
            'apiBaseUrl' => $apiBaseUrl,
        ], 200, [
            'Content-Type' => 'application/json; charset=utf-8',
            'Cache-Control' => 'public, max-age=300',
        ]);
    }
}
