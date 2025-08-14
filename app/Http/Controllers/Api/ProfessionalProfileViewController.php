<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProfessionalProfile;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProfessionalProfileViewController extends Controller
{
    /**
     * Record a view for a professional profile.
     */
    public function recordView(Request $request, ProfessionalProfile $professionalProfile): JsonResponse
    {
        try {
            $user = $request->user();
            $sessionId = $request->hasSession() ? $request->session()->getId() : 'no-session-' . uniqid();
            $ipAddress = $request->ip();
            $userAgent = $request->userAgent();

            // Enregistrer la vue (évite automatiquement les doublons)
            $view = $professionalProfile->recordView(
                $user ? $user->id : null,
                $sessionId,
                $ipAddress,
                $userAgent
            );

            $message = $view ? 'Vue enregistrée avec succès.' : 'Vue déjà enregistrée pour cette session.';

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'total_views' => $professionalProfile->getTotalViewsAttribute(),
                    'view_recorded' => $view !== null
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'enregistrement de la vue.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get view statistics for a professional profile.
     */
    public function getStats(Request $request, ProfessionalProfile $professionalProfile): JsonResponse
    {
        try {
            $totalViews = $professionalProfile->getTotalViewsAttribute();
            $uniqueUsers = $professionalProfile->views()->whereNotNull('user_id')->distinct('user_id')->count();
            $anonymousViews = $professionalProfile->views()->whereNull('user_id')->count();

            // Vues par jour (derniers 30 jours)
            $viewsPerDay = $professionalProfile->views()
                ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->where('created_at', '>=', now()->subDays(30))
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'total_views' => $totalViews,
                    'unique_users' => $uniqueUsers,
                    'anonymous_views' => $anonymousViews,
                    'views_per_day' => $viewsPerDay
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if current user/session has viewed the profile.
     */
    public function hasViewed(Request $request, ProfessionalProfile $professionalProfile): JsonResponse
    {
        try {
            $user = $request->user();
            $sessionId = $request->hasSession() ? $request->session()->getId() : 'no-session-' . uniqid();

            $hasViewed = $professionalProfile->isViewedBy(
                $user ? $user->id : null,
                $sessionId
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'has_viewed' => $hasViewed,
                    'total_views' => $professionalProfile->getTotalViewsAttribute()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la vérification de la vue.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
