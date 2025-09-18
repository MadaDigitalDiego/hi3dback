<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProfessionalProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class ProfessionalController extends Controller
{
    /**
     * Récupère tous les professionnels.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            // Récupérer tous les profils professionnels avec les utilisateurs associés
            $professionalProfiles = ProfessionalProfile::with(['user', 'views', 'likers'])->get();

            // Formater les données pour correspondre au format attendu par le frontend
            $professionals = $professionalProfiles->map(function ($profile) {
                // Traiter les skills qui peuvent être une chaîne JSON ou un tableau
                $skills = [];
                if ($profile->skills) {
                    if (is_array($profile->skills)) {
                        $skills = $profile->skills;
                    } elseif (is_string($profile->skills)) {
                        try {
                            $skills = json_decode($profile->skills, true);
                        } catch (\Exception $e) {
                            $skills = [$profile->skills]; // Si ce n'est pas un JSON valide, le traiter comme une chaîne simple
                        }
                    }
                }


                $achievements = $profile->achievements;
                $user =  $profile->user;
                $services =$user->serviceOffers;


                return [
                    'id' => $profile->id,
                    'user_id' => $profile->user_id,
                    'first_name' => $profile->first_name,
                    'last_name' => $profile->last_name,
                    'email' => $profile->user ? $profile->user->email : null,
                    'is_professional' => $profile->user ? $profile->user->is_professional : true,
                    'city' => $profile->city,
                    'country' => $profile->country,
                    'skills' => $skills,
                    'availability_status' => $profile->availability_status,
                    'hourly_rate' => $profile->hourly_rate,
                    'avatar' => $profile->avatar,
                    'cover_photo' => $profile->cover_photo,
                    'profile_picture_path' => $profile->avatar, // Utiliser avatar comme profile_picture_path
                    'rating' => $profile->rating,
                    'review_count' => 0, // Valeur par défaut
                    'bio' => $profile->bio,
                    'title' => $profile->title,
                    'achievements'=>$achievements,
                    'service_offer'=>$services,
                    'languages' => $profile->languages,
                    // Données de likes et views
                    'likes_count' => $profile->getTotalLikesAttribute(),
                    'views_count' => $profile->getTotalViewsAttribute(),
                    'popularity_score' => $profile->getPopularityScore(),
                ];
            });

            // Retourner les données en JSON
            return response()->json([
                'success' => true,
                'professionals' => $professionals,
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des professionnels: ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la récupération des professionnels.'], 500);
        }
    }

    /**
     * Récupère toutes les freelanceProfiles.
     *
     * @return JsonResponse
     */
    public function getAllFreelanceProfiles(): JsonResponse
    {
        try {
            // Récupérer tous les profils professionnels avec les utilisateurs associés
            $professionalProfiles = ProfessionalProfile::with('user')->get();

            // Retourner les données en JSON
            return response()->json([
                'success' => true,
                'data' => $professionalProfiles,
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération de tous les profils freelance: ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la récupération des profils freelance.'], 500);
        }
    }
    /**
     * Endpoint public pour lister les professionnels (sans authentification).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function indexPublic(): JsonResponse
    {
        try {
            $professionals = ProfessionalProfile::where('completion_percentage', '>=', 0)
                ->where('completion_percentage', 100) // Optionnel: Filtrer seulement les profils complétés à 100%
                ->get([ // Sélectionner uniquement les champs publics
                    'id',
                    'first_name',
                    'last_name',
                    'profile_picture_path',
                    'skills',
                    'city',
                    'country',
                    // Ajoutez ici d'autres champs publics que vous souhaitez afficher
                ]);

            return response()->json(['professionals' => $professionals], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération de la liste publique des professionnels: ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la récupération des professionnels.'], 500);
        }
    }

    /**
     * Endpoint protégé pour lister les professionnels (nécessite une authentification).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function indexProtected(): JsonResponse
    {
        try {
            $professionals = ProfessionalProfile::where('completion_percentage', '>=', 0)
                ->where('completion_percentage', 100) // Optionnel: Filtrer seulement les profils complétés à 100%
                ->with(['experiences', 'achievements']) // Optionnel: Charger les relations si nécessaire
                ->get(); // Récupérer tous les champs (ou sélectionnez ceux que vous voulez)

            return response()->json(['professionals' => $professionals], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération de la liste protégée des professionnels: ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la récupération des professionnels.'], 500);
        }
    }

    /**
     * Endpoint public pour lister les professionnels avec leur statut de disponibilité et délai de réponse.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function indexAvailability(): JsonResponse
    {
        try {
            $professionals = ProfessionalProfile::where('completion_percentage', '>=', 0)
                ->where('completion_percentage', 100) // Optionnel: Filtrer seulement les profils complétés
                ->get([ // Sélectionner les champs pertinents pour la disponibilité
                    'id',
                    'first_name',
                    'last_name',
                    'profile_picture_path',
                    'skills',
                    'city',
                    'country',
                    'availability_status', // Inclure le statut de disponibilité
                    'estimated_response_time', // Inclure le délai de réponse estimé
                    // Ajoutez d'autres champs publics si nécessaire
                ]);

            return response()->json(['professionals' => $professionals], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération de la liste des professionnels avec disponibilité: ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la récupération des professionnels.'], 500);
        }
    }

    /**
     * Affiche les détails d'un professionnel spécifique.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id): JsonResponse
    {
        try {
            // Récupérer le profil professionnel avec l'utilisateur associé et ses réalisations
            // $profile = ProfessionalProfile::with(['user', 'achievements'])->findOrFail($id);
            $profile = ProfessionalProfile::with(['user', 'views', 'likers'])->findOrFail($id);

            // Traiter les skills qui peuvent être une chaîne JSON ou un tableau
            $skills = [];
            if ($profile->skills) {
                if (is_array($profile->skills)) {
                    $skills = $profile->skills;
                } elseif (is_string($profile->skills)) {
                    try {
                        $skills = json_decode($profile->skills, true);
                    } catch (\Exception $e) {
                        $skills = [$profile->skills]; // Si ce n'est pas un JSON valide, le traiter comme une chaîne simple
                    }
                }
            }

            // Récupérer les réalisations du professionnel
            $achievements = [];

            try{
                $achievements = $profile->achievements()->orderBy('date_obtained', 'desc')->get();
            } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des réalisations : ' . $e->getMessage());
            return response()->json(['message' => $e->getMessage()], 404);
        }


            // Formater les données pour correspondre au format attendu par le frontend
            $professional = [
                'id' => $profile->id,
                'user_id' => $profile->user_id,
                'first_name' => $profile->first_name,
                'last_name' => $profile->last_name,
                'email' => $profile->user ? $profile->user->email : null,
                'is_professional' => $profile->user ? $profile->user->is_professional : true,
                'city' => $profile->city,
                'country' => $profile->country,
                'skills' => $skills,
                'availability_status' => $profile->availability_status,
                'hourly_rate' => $profile->hourly_rate,
                'avatar' => $profile->avatar,
                'cover_photo' => $profile->cover_photo,
                'profile_picture_path' => $profile->avatar, // Utiliser avatar comme profile_picture_path
                'rating' => $profile->rating,
                'review_count' => 0, // Valeur par défaut
                'bio' => $profile->bio,
                'title' => $profile->title,
                'phone' => $profile->phone,
                'address' => $profile->address,
                'languages' => $profile->languages,
                'services_offered' => $profile->services_offered,
                'portfolio' => $profile->portfolio,
                'achievements' => $achievements, // Ajouter les réalisations
                // Données de likes et views
                'likes_count' => $profile->getTotalLikesAttribute(),
                'views_count' => $profile->getTotalViewsAttribute(),
                'popularity_score' => $profile->getPopularityScore(),
            ];

            // Retourner les données en JSON
            return response()->json([
                'success' => true,
                'professional' => $professional,
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération du professionnel: ' . $e->getMessage());
            return response()->json(['message' => 'Professionnel non trouvé.'], 404);
        }
    }
    /**
     * Endpoint pour filtrer les professionnels avec des critères avancés.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function filter(Request $request): JsonResponse
    {
        try {
            $query = ProfessionalProfile::with(['user', 'views', 'likers']);

            // Filtrage par recherche
            if ($request->has('search') && !empty($request->input('search'))) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('title', 'like', "%{$search}%");

                    // Recherche dans les compétences (stockées en JSON)
                    $q->orWhereRaw("JSON_SEARCH(LOWER(skills), 'one', LOWER(?)) IS NOT NULL", ["%{$search}%"]);
                });
            }

            // Filtrage par disponibilité
            if ($request->has('availability') && $request->input('availability') !== 'all') {
                $query->where('availability_status', $request->input('availability'));
            }

            // Filtrage par compétences
            if ($request->has('skills') && !empty($request->input('skills'))) {
                $skills = explode(',', $request->input('skills'));
                foreach ($skills as $skill) {
                    $query->whereRaw("JSON_SEARCH(LOWER(skills), 'one', LOWER(?)) IS NOT NULL", ["%{$skill}%"]);
                }
            }

            // Filtrage par note minimale
            if ($request->has('rating') && $request->input('rating') !== 'all') {
                $query->where('rating', '>=', $request->input('rating'));
            }

            // Filtrage par localisation
            if ($request->has('location') && !empty($request->input('location'))) {
                $location = $request->input('location');
                $query->where(function ($q) use ($location) {
                    $q->where('city', 'like', "%{$location}%")
                      ->orWhere('country', 'like', "%{$location}%");
                });
            }

            // Tri des résultats
            if ($request->has('sort_by')) {
                $sortBy = $request->input('sort_by');
                switch ($sortBy) {
                    case 'newest':
                        $query->orderBy('created_at', 'desc');
                        break;
                    case 'rating':
                        $query->orderBy('rating', 'desc');
                        break;
                    default:
                        $query->orderBy('created_at', 'desc');
                        break;
                }
            } else {
                $query->orderBy('created_at', 'desc');
            }

            // Récupérer les professionnels filtrés
            $professionals = $query->get();

            // Formater les données pour correspondre au format attendu par le frontend
            $formattedProfessionals = $professionals->map(function ($profile) {
                // Traiter les skills qui peuvent être une chaîne JSON ou un tableau
                $skills = [];
                if ($profile->skills) {
                    if (is_array($profile->skills)) {
                        $skills = $profile->skills;
                    } elseif (is_string($profile->skills)) {
                        try {
                            $skills = json_decode($profile->skills, true);
                        } catch (\Exception $e) {
                            $skills = [$profile->skills]; // Si ce n'est pas un JSON valide, le traiter comme une chaîne simple
                        }
                    }
                }

                $achievements = $profile->achievements;
                $user =  $profile->user;
                $services =$user->serviceOffers;

                return [
                    'id' => $profile->id,
                    'user_id' => $profile->user_id,
                    'first_name' => $profile->first_name,
                    'last_name' => $profile->last_name,
                    'email' => $profile->user ? $profile->user->email : null,
                    'is_professional' => $profile->user ? $profile->user->is_professional : true,
                    'city' => $profile->city,
                    'country' => $profile->country,
                    'skills' => $skills,
                    'availability_status' => $profile->availability_status,
                    'hourly_rate' => $profile->hourly_rate,
                    'avatar' => $profile->avatar,
                    'profile_picture_path' => $profile->avatar, // Utiliser avatar comme profile_picture_path
                    'rating' => $profile->rating,
                    'review_count' => 0, // Valeur par défaut
                    'bio' => $profile->bio,
                    'title' => $profile->title,
                    'achievements'=>$achievements,
                    'service_offer'=>$services,
                    // Données de likes et views
                    'likes_count' => $profile->getTotalLikesAttribute(),
                    'views_count' => $profile->getTotalViewsAttribute(),
                    'popularity_score' => $profile->getPopularityScore(),
                ];
            });

            // Retourner les données en JSON
            return response()->json([
                'success' => true,
                'professionals' => $formattedProfessionals,
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors du filtrage des professionnels: ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors du filtrage des professionnels.'], 500);
        }
    }
}
