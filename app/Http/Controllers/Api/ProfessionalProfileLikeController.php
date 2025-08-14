<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProfessionalProfile;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProfessionalProfileLikeController extends Controller
{
    /**
     * Like a professional profile.
     */
    public function like(Request $request, ProfessionalProfile $professionalProfile): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Vous devez être connecté pour liker un profil.'
            ], 401);
        }

        try {
            // Utiliser la méthode like du trait Liker
            $like = $user->like($professionalProfile);

            return response()->json([
                'success' => true,
                'message' => 'Profil ajouté aux likes et favoris avec succès.',
                'data' => [
                    'liked' => true,
                    'total_likes' => $professionalProfile->likers()->count(),
                    'is_favorite' => $user->hasFavorite($professionalProfile)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du like du profil.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Unlike a professional profile.
     */
    public function unlike(Request $request, ProfessionalProfile $professionalProfile): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Vous devez être connecté pour unliker un profil.'
            ], 401);
        }

        try {
            // Utiliser la méthode unlike du trait Liker
            $result = $user->unlike($professionalProfile);

            return response()->json([
                'success' => true,
                'message' => 'Profil retiré des likes et favoris avec succès.',
                'data' => [
                    'liked' => false,
                    'total_likes' => $professionalProfile->likers()->count(),
                    'is_favorite' => $user->hasFavorite($professionalProfile)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du unlike du profil.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle like status for a professional profile.
     */
    public function toggle(Request $request, ProfessionalProfile $professionalProfile): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Vous devez être connecté pour liker/unliker un profil.'
            ], 401);
        }

        try {
            // Utiliser la méthode toggleLike du trait Liker
            $result = $user->toggleLike($professionalProfile);
            $isLiked = $user->hasLiked($professionalProfile);

            return response()->json([
                'success' => true,
                'message' => $isLiked ? 'Profil ajouté aux likes et favoris.' : 'Profil retiré des likes et favoris.',
                'data' => [
                    'liked' => $isLiked,
                    'total_likes' => $professionalProfile->likers()->count(),
                    'is_favorite' => $user->hasFavorite($professionalProfile)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du toggle like du profil.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get like status for a professional profile.
     */
    public function status(Request $request, ProfessionalProfile $professionalProfile): JsonResponse
    {
        $user = $request->user();
        $isLiked = $user ? $user->hasLiked($professionalProfile) : false;
        $isFavorite = $user ? $user->hasFavorite($professionalProfile) : false;

        return response()->json([
            'success' => true,
            'data' => [
                'liked' => $isLiked,
                'total_likes' => $professionalProfile->likers()->count(),
                'is_favorite' => $isFavorite
            ]
        ]);
    }
}
