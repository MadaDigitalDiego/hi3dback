<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProfessionalProfile;
// use App\Models\FreelanceProfile;
use App\Models\ServiceOffer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ExplorerController extends Controller
{
    /**
     * Récupère la liste des professionnels avec leurs services et réalisations.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getProfessionals(Request $request): JsonResponse
    {
        try {
            // Paramètres de pagination
            $perPage = $request->input('per_page', 10);
            $page = $request->input('page', 1);
            
            // Paramètres de filtrage
            $search = $request->input('search');
            $skills = $request->input('skills');
            $city = $request->input('city');
            $country = $request->input('country');
            $minRate = $request->input('min_rate');
            $maxRate = $request->input('max_rate');
            $availability = $request->input('availability');
            
            // Construire la requête
            $query = ProfessionalProfile::with(['user', 'achievements'])
                ->where('completion_percentage', '>=', 80); // Seulement les profils suffisamment complets
            
            // Appliquer les filtres
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('bio', 'like', "%{$search}%")
                      ->orWhere('title', 'like', "%{$search}%");
                });
            }
            
            if ($skills) {
                $skillsArray = explode(',', $skills);
                foreach ($skillsArray as $skill) {
                    $query->whereRaw("JSON_CONTAINS(skills, ?)", ['"' . trim($skill) . '"']);
                }
            }
            
            if ($city) {
                $query->where('city', 'like', "%{$city}%");
            }
            
            if ($country) {
                $query->where('country', 'like', "%{$country}%");
            }
            
            if ($minRate) {
                $query->where('hourly_rate', '>=', $minRate);
            }
            
            if ($maxRate) {
                $query->where('hourly_rate', '<=', $maxRate);
            }
            
            if ($availability) {
                $query->where('availability_status', $availability);
            }
            
            // Récupérer les professionnels paginés
            $professionals = $query->paginate($perPage, ['*'], 'page', $page);
            
            // Récupérer les services pour chaque professionnel
            $professionalIds = $professionals->pluck('user_id')->toArray();
            $services = ServiceOffer::whereIn('user_id', $professionalIds)
                ->where('is_private', false)
                ->get()
                ->groupBy('user_id');
            
            // Formater les données
            $formattedProfessionals = $professionals->map(function ($professional) use ($services) {
                $userId = $professional->user_id;

                // Traiter les compétences
                $skills = [];
                if ($professional->skills) {
                    if (is_array($professional->skills)) {
                        $skills = $professional->skills;
                    } elseif (is_string($professional->skills)) {
                        try {
                            $skills = json_decode($professional->skills, true);
                        } catch (\Exception $e) {
                            $skills = [$professional->skills];
                        }
                    }
                }

                // Formater les services avec les images
                $userServices = $services[$userId] ?? collect();
                $formattedServices = $userServices->map(function ($service) {
                    // Générer l'URL complète de l'image si elle existe
                    $imageUrl = null;
                    if ($service->image) {
                        // Si l'image est déjà une URL complète, la garder telle quelle
                        if (filter_var($service->image, FILTER_VALIDATE_URL)) {
                            $imageUrl = $service->image;
                        } else {
                            // Sinon, générer l'URL complète pour le stockage local
                            $imageUrl = asset('storage/' . $service->image);
                        }
                    }

                    return [
                        'id' => $service->id,
                        'title' => $service->title,
                        'description' => $service->description,
                        'price' => $service->price,
                        'execution_time' => $service->execution_time,
                        'concepts' => $service->concepts,
                        'revisions' => $service->revisions,
                        'categories' => $service->categories,
                        'files' => $service->files,
                        'image' => $imageUrl, // ✅ Ajout du champ image avec URL complète
                        'views' => $service->views,
                        'likes' => $service->likes,
                        'rating' => $service->rating,
                        'created_at' => $service->created_at,
                        'updated_at' => $service->updated_at,
                    ];
                });

                return [
                    'id' => $professional->id,
                    'user_id' => $userId,
                    'first_name' => $professional->first_name,
                    'last_name' => $professional->last_name,
                    'email' => $professional->user ? $professional->user->email : null,
                    'city' => $professional->city,
                    'country' => $professional->country,
                    'skills' => $skills,
                    'availability_status' => $professional->availability_status,
                    'hourly_rate' => $professional->hourly_rate,
                    'avatar' => $professional->avatar,
                    'profile_picture_path' => $professional->avatar,
                    'rating' => $professional->rating,
                    'bio' => $professional->bio,
                    'title' => $professional->title,
                    'achievements' => $professional->achievements,
                    'services' => $formattedServices,
                ];
            });
            
            return response()->json([
                'success' => true,
                'professionals' => $formattedProfessionals,
                'pagination' => [
                    'total' => $professionals->total(),
                    'per_page' => $professionals->perPage(),
                    'current_page' => $professionals->currentPage(),
                    'last_page' => $professionals->lastPage(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des professionnels: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des professionnels: ' . $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Récupère les détails d'un professionnel spécifique avec ses services et réalisations.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function getProfessionalDetails(int $id): JsonResponse
    {
        try {
            // Récupérer le profil professionnel avec l'utilisateur associé et ses réalisations
            $professional = ProfessionalProfile::with(['user', 'achievements'])->findOrFail($id);
            
            // Récupérer les services du professionnel
            $rawServices = ServiceOffer::where('user_id', $professional->user_id)
                ->where('is_private', false)
                ->get();

            // Formater les services avec les images
            $services = $rawServices->map(function ($service) {
                // Générer l'URL complète de l'image si elle existe
                $imageUrl = null;
                if ($service->image) {
                    // Si l'image est déjà une URL complète, la garder telle quelle
                    if (filter_var($service->image, FILTER_VALIDATE_URL)) {
                        $imageUrl = $service->image;
                    } else {
                        // Sinon, générer l'URL complète pour le stockage local
                        $imageUrl = asset('storage/' . $service->image);
                    }
                }

                return [
                    'id' => $service->id,
                    'title' => $service->title,
                    'description' => $service->description,
                    'price' => $service->price,
                    'execution_time' => $service->execution_time,
                    'concepts' => $service->concepts,
                    'revisions' => $service->revisions,
                    'categories' => $service->categories,
                    'files' => $service->files,
                    'image' => $imageUrl, // ✅ Ajout du champ image avec URL complète
                    'views' => $service->views,
                    'likes' => $service->likes,
                    'rating' => $service->rating,
                    'created_at' => $service->created_at,
                    'updated_at' => $service->updated_at,
                ];
            });
            
            // Traiter les compétences
            $skills = [];
            if ($professional->skills) {
                if (is_array($professional->skills)) {
                    $skills = $professional->skills;
                } elseif (is_string($professional->skills)) {
                    try {
                        $skills = json_decode($professional->skills, true);
                    } catch (\Exception $e) {
                        $skills = [$professional->skills];
                    }
                }
            }
            
            // Formater les données
            $formattedProfessional = [
                'id' => $professional->id,
                'user_id' => $professional->user_id,
                'first_name' => $professional->first_name,
                'last_name' => $professional->last_name,
                'email' => $professional->user ? $professional->user->email : null,
                'city' => $professional->city,
                'country' => $professional->country,
                'skills' => $skills,
                'availability_status' => $professional->availability_status,
                'hourly_rate' => $professional->hourly_rate,
                'avatar' => $professional->avatar,
                'profile_picture_path' => $professional->avatar,
                'rating' => $professional->rating,
                'bio' => $professional->bio,
                'title' => $professional->title,
                'phone' => $professional->phone,
                'address' => $professional->address,
                'languages' => $professional->languages,
                'services_offered' => $professional->services_offered,
                'portfolio' => $professional->portfolio,
                'achievements' => $professional->achievements,
                'services' => $services,
            ];
            
            return response()->json([
                'success' => true,
                'professional' => $formattedProfessional,
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération du professionnel: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Professionnel non trouvé ou erreur lors de la récupération.',
            ], 404);
        }
    }
    
    /**
     * Récupère la liste des services avec les informations des professionnels.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getServices(Request $request): JsonResponse
    {
        try {
            // Paramètres de pagination
            $perPage = $request->input('per_page', 10);
            $page = $request->input('page', 1);
            
            // Paramètres de filtrage
            $search = $request->input('search');
            $category = $request->input('category');
            $minPrice = $request->input('min_price');
            $maxPrice = $request->input('max_price');
            $executionTime = $request->input('execution_time');
            $sortBy = $request->input('sort_by', 'newest');
            
            // Construire la requête
            $query = ServiceOffer::with('user')
                ->where('is_private', false);
            
            // Appliquer les filtres
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }
            
            if ($category && $category !== 'all') {
                $query->whereRaw("JSON_SEARCH(categories, 'one', ?) IS NOT NULL", [$category]);
            }
            
            if ($minPrice) {
                $query->where('price', '>=', $minPrice);
            }
            
            if ($maxPrice) {
                $query->where('price', '<=', $maxPrice);
            }
            
            if ($executionTime && $executionTime !== 'all') {
                switch ($executionTime) {
                    case 'express':
                        $query->where('execution_time', 'like', "%express%")
                              ->orWhere('execution_time', 'like', "%rapide%")
                              ->orWhere('execution_time', 'like', "%1-3 jours%");
                        break;
                    case 'standard':
                        $query->where('execution_time', 'like', "%standard%")
                              ->orWhere('execution_time', 'like', "%1-2 semaines%");
                        break;
                    case 'extended':
                        $query->where('execution_time', 'like', "%extended%")
                              ->orWhere('execution_time', 'like', "%plus de 2 semaines%");
                        break;
                }
            }
            
            // Tri des résultats
            switch ($sortBy) {
                case 'newest':
                    $query->orderBy('created_at', 'desc');
                    break;
                case 'rating':
                    $query->orderBy('rating', 'desc');
                    break;
                case 'price_asc':
                    $query->orderBy('price', 'asc');
                    break;
                case 'price_desc':
                    $query->orderBy('price', 'desc');
                    break;
                default:
                    $query->orderBy('created_at', 'desc');
                    break;
            }
            
            // Récupérer les services paginés
            $services = $query->paginate($perPage, ['*'], 'page', $page);
            
            // Récupérer les professionnels pour chaque service
            $userIds = $services->pluck('user_id')->unique()->toArray();
            $professionals = ProfessionalProfile::whereIn('user_id', $userIds)
                ->with('achievements')
                ->get()
                ->keyBy('user_id');
            
            // Formater les données
            $formattedServices = $services->map(function ($service) use ($professionals) {
                $userId = $service->user_id;
                $professional = $professionals[$userId] ?? null;

                // Générer l'URL complète de l'image si elle existe
                $imageUrl = null;
                if ($service->image) {
                    // Si l'image est déjà une URL complète, la garder telle quelle
                    if (filter_var($service->image, FILTER_VALIDATE_URL)) {
                        $imageUrl = $service->image;
                    } else {
                        // Sinon, générer l'URL complète pour le stockage local
                        $imageUrl = asset('storage/' . $service->image);
                    }
                }

                return [
                    'id' => $service->id,
                    'title' => $service->title,
                    'description' => $service->description,
                    'price' => $service->price,
                    'execution_time' => $service->execution_time,
                    'concepts' => $service->concepts,
                    'revisions' => $service->revisions,
                    'categories' => $service->categories,
                    'files' => $service->files,
                    'image' => $imageUrl, // ✅ Ajout du champ image avec URL complète
                    'views' => $service->views,
                    'likes' => $service->likes,
                    'rating' => $service->rating,
                    'created_at' => $service->created_at,
                    'updated_at' => $service->updated_at,
                    'professional' => $professional ? [
                        'id' => $professional->id,
                        'user_id' => $professional->user_id,
                        'first_name' => $professional->first_name,
                        'last_name' => $professional->last_name,
                        'avatar' => $professional->avatar,
                        'title' => $professional->title,
                        'rating' => $professional->rating,
                        'achievements_count' => $professional->achievements->count(),
                    ] : null,
                ];
            });
            
            return response()->json([
                'success' => true,
                'services' => $formattedServices,
                'pagination' => [
                    'total' => $services->total(),
                    'per_page' => $services->perPage(),
                    'current_page' => $services->currentPage(),
                    'last_page' => $services->lastPage(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des services: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des services: ' . $e->getMessage(),
            ], 500);
        }
    }
}
