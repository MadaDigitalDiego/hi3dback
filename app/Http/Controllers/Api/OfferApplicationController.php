<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OfferApplication;
use App\Models\OpenOffer;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class OfferApplicationController extends Controller
{
    /**
     * Get received offers for the authenticated professional user
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function received(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user->is_professional) {
                return response()->json(['message' => 'Seuls les professionnels peuvent voir les offres reçues.'], 403);
            }

            // Vérifier si l'utilisateur a un profil professionnel
            $profile = $user->freelanceProfile;

            if (!$profile) {
                return response()->json(['message' => 'Profil professionnel non trouvé.'], 404);
            }

            // Récupérer les candidatures de l'utilisateur
            $applications = OfferApplication::where('professional_profile_id', $profile->id)
                ->with(['openOffer', 'openOffer.user'])
                ->orderBy('id', 'desc')
                ->get();

            // Formater les offres pour le frontend
            $offers = $applications->map(function($application) {
                $offer = $application->openOffer;
                $client = $offer->user;

                return [
                    'id' => $application->id,
                    'id_offer' => $offer->id,
                    'title' => $offer->title,
                    'description' => $offer->description,
                    'budget' => $offer->budget,
                    'deadline' => $offer->deadline,
                    'created_at' => $application->created_at,
                    'is_invited' => $application->status === 'invited',
                    'status' => $application->status,
                    'status_offer' => $offer->status,
                    'client' => [
                        'id' => $client->id,
                        'name' => $client->first_name . ' ' . $client->last_name,
                        'avatar' => $this->getUserAvatar($client),
                    ],
                ];
            });

            return response()->json(['offers' => $offers]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des offres reçues: ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la récupération des offres reçues.'], 500);
        }
    }

    /**
     * Accept an offer application
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function accept(Request $request, $id): JsonResponse
    {
        // try {
            $user = $request->user();

            if (!$user->is_professional) {
                return response()->json(['message' => 'Seuls les professionnels peuvent accepter des offres.'], 403);
            }

            $application = OfferApplication::findOrFail($id);

            // Vérifier que l'application appartient à l'utilisateur
            if ($application->freelanceProfile->user_id !== $user->id) {
                return response()->json(['message' => 'Non autorisé à accepter cette offre.'], 403);
            }

            // Mettre à jour le statut de l'application
            $application->status = 'accepted';
            $application->save();

            return response()->json(['message' => 'Offre acceptée avec succès.']);
        // } catch (\Exception $e) {
        //     Log::error('Erreur lors de l\'acceptation de l\'offre: ' . $e->getMessage());
        //     // return response()->json(['message' => 'Erreur lors de l\'acceptation de l\'offre.'], 500);
        //     return response()->json(['message' => 'Erreur lors de l\'acceptation de l\'offre.'.$e->getMessage()], 500);
        // }
    }

    /**
     * Decline an offer application
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function decline(Request $request, $id): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user->is_professional) {
                return response()->json(['message' => 'Seuls les professionnels peuvent refuser des offres.'], 403);
            }

            $application = OfferApplication::findOrFail($id);

            // Vérifier que l'application appartient à l'utilisateur
            if ($application->freelanceProfile->user_id !== $user->id) {
                return response()->json(['message' => 'Non autorisé à refuser cette offre.'], 403);
            }

            // Mettre à jour le statut de l'application
            $application->status = 'rejected';
            $application->save();

            return response()->json(['message' => 'Offre refusée avec succès.']);
        } catch (\Exception $e) {
            Log::error('Erreur lors du refus de l\'offre: ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors du refus de l\'offre.'], 500);
        }
    }

    /**
     * Get user avatar from either new or old profile structure
     *
     * @param User $user
     * @return string|null
     */
    private function getUserAvatar($user): ?string
    {
        // Utiliser directement l'ancienne structure
        if ($user->is_professional && $user->freelanceProfile && $user->freelanceProfile->avatar) {
            return $user->freelanceProfile->avatar;
        }

        // Essayer ensuite l'ancienne structure pour un client
        if (!$user->is_professional && $user->companyProfile && $user->companyProfile->avatar) {
            return $user->companyProfile->avatar;
        }

        return null;
    }
}
