<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NavigationController extends Controller
{
    public function show(): JsonResponse
    {
        $apiKey = env('NAV_API_KEY');

        if ($apiKey) {
            $authHeader = request()->header('Authorization');
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
        }

        $html = view('navigation.header')->render();

        return response()->json([
            'html' => $html,
        ], 200, [
            'Content-Type' => 'application/json; charset=utf-8',
            'Cache-Control' => 'public, max-age=300',
        ]);
    }
}
