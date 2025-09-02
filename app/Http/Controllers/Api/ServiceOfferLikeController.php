<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServiceOffer;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ServiceOfferLikeController extends Controller
{
    /**
     * Like a service offer.
     */
    public function like(Request $request, ServiceOffer $serviceOffer): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Vous devez être connecté pour liker un service.'
            ], 401);
        }

        try {
            // Utiliser la méthode like du trait Liker
            $like = $user->like($serviceOffer);

            return response()->json([
                'success' => true,
                'message' => 'Service ajouté aux likes et favoris avec succès.',
                'data' => [
                    'liked' => true,
                    'total_likes' => $serviceOffer->likers()->count(),
                    'is_favorite' => $user->hasFavorite($serviceOffer)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du like du service.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Unlike a service offer.
     */
    public function unlike(Request $request, ServiceOffer $serviceOffer): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Vous devez être connecté pour unliker un service.'
            ], 401);
        }

        try {
            // Utiliser la méthode unlike du trait Liker
            $result = $user->unlike($serviceOffer);

            return response()->json([
                'success' => true,
                'message' => 'Service retiré des likes et favoris avec succès.',
                'data' => [
                    'liked' => false,
                    'total_likes' => $serviceOffer->likers()->count(),
                    'is_favorite' => $user->hasFavorite($serviceOffer)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du unlike du service.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle like status for a service offer.
     */
    public function toggle(Request $request, ServiceOffer $serviceOffer): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Vous devez être connecté pour liker/unliker un service.'
            ], 401);
        }

        try {
            // Utiliser la méthode toggleLike du trait Liker
            $result = $user->toggleLike($serviceOffer);
            $isLiked = $user->hasLiked($serviceOffer);

            return response()->json([
                'success' => true,
                'message' => $isLiked ? 'Service ajouté aux likes et favoris.' : 'Service retiré des likes et favoris.',
                'data' => [
                    'liked' => $isLiked,
                    'total_likes' => $serviceOffer->likers()->count(),
                    'is_favorite' => $user->hasFavorite($serviceOffer)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du toggle like du service.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get like status for a service offer.
     */
    public function status(Request $request, ServiceOffer $serviceOffer): JsonResponse
    {
        $user = $request->user();
        $isLiked = $user ? $user->hasLiked($serviceOffer) : false;
        $isFavorite = $user ? $user->hasFavorite($serviceOffer) : false;

        return response()->json([
            'success' => true,
            'data' => [
                'liked' => $isLiked,
                'total_likes' => $serviceOffer->likers()->count(),
                'is_favorite' => $isFavorite
            ]
        ]);
    }
}
