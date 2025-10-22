<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProfessionalProfile;
// use App\Models\FreelanceProfile;
use App\Models\ServiceOffer;
use App\Models\Achievement;
use App\Models\User;
use App\Services\GlobalSearchService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ExplorerController extends Controller
{
    protected GlobalSearchService $searchService;

    public function __construct(GlobalSearchService $searchService)
    {
        $this->searchService = $searchService;
    }
    /**
     * Récupère la liste des professionnels avec leurs services et réalisations.
     * Utilise Meilisearch pour la recherche et retourne le temps d'exécution.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getProfessionals(Request $request): JsonResponse
    {
        try {
            $startTime = microtime(true);

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

            // Si une recherche est spécifiée, utiliser Meilisearch
            if ($search && strlen(trim($search)) >= 2) {
                $searchStartTime = microtime(true);

                // Préparer les filtres pour Meilisearch
                $filters = [];

                if ($city) {
                    $filters['city'] = $city;
                }

                if ($country) {
                    $filters['country'] = $country;
                }

                if ($minRate) {
                    $filters['min_hourly_rate'] = $minRate;
                }

                if ($maxRate) {
                    $filters['max_hourly_rate'] = $maxRate;
                }

                if ($availability) {
                    $filters['availability_status'] = $availability;
                }

                // Utiliser le service de recherche Meilisearch
                $searchResults = $this->searchService->searchProfessionalProfiles($search, $filters);
                $searchTime = microtime(true) - $searchStartTime;

                // Appliquer la pagination manuelle sur les résultats de recherche
                $total = $searchResults->count();
                $offset = ($page - 1) * $perPage;
                $paginatedResults = $searchResults->slice($offset, $perPage);

                // Récupérer les IDs des profils trouvés
                $profileIds = $paginatedResults->pluck('id')->toArray();

                // Récupérer les profils complets avec leurs relations
                $professionals = ProfessionalProfile::with(['user', 'achievements'])
                    ->whereIn('id', $profileIds)
                    ->where('completion_percentage', '>=', 80)
                    ->get()
                    ->keyBy('id');

                // Réorganiser selon l'ordre de Meilisearch
                $orderedProfessionals = collect();
                foreach ($profileIds as $id) {
                    if (isset($professionals[$id])) {
                        $orderedProfessionals->push($professionals[$id]);
                    }
                }

                $professionals = $orderedProfessionals;

            } else {
                // Utiliser la requête Eloquent classique si pas de recherche
                $query = ProfessionalProfile::with(['user', 'achievements'])
                    ->where('completion_percentage', '>=', 80); // Seulement les profils suffisamment complets

                // Appliquer les filtres classiques
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
                $paginatedProfessionals = $query->paginate($perPage, ['*'], 'page', $page);
                $professionals = $paginatedProfessionals->getCollection();
                $total = $paginatedProfessionals->total();
                $searchTime = null; // Pas de recherche Meilisearch utilisée
            }

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

            // Calculer le temps total d'exécution
            $totalExecutionTime = microtime(true) - $startTime;

            // Préparer les informations de pagination
            $paginationInfo = [];
            if (isset($paginatedProfessionals)) {
                // Pagination Eloquent classique
                $paginationInfo = [
                    'total' => $paginatedProfessionals->total(),
                    'per_page' => $paginatedProfessionals->perPage(),
                    'current_page' => $paginatedProfessionals->currentPage(),
                    'last_page' => $paginatedProfessionals->lastPage(),
                ];
            } else {
                // Pagination manuelle pour Meilisearch
                $lastPage = ceil($total / $perPage);
                $paginationInfo = [
                    'total' => $total,
                    'per_page' => $perPage,
                    'current_page' => $page,
                    'last_page' => $lastPage,
                ];
            }

            $response = [
                'success' => true,
                'professionals' => $formattedProfessionals,
                'pagination' => $paginationInfo,
                'performance' => [
                    'total_execution_time_ms' => round($totalExecutionTime * 1000, 2),
                    'search_method' => $search && strlen(trim($search)) >= 2 ? 'meilisearch' : 'eloquent',
                ],
            ];

            // Ajouter le temps de recherche Meilisearch si disponible
            if ($searchTime !== null) {
                $response['performance']['meilisearch_time_ms'] = round($searchTime * 1000, 2);
                $response['performance']['search_query'] = $search;
            }

            return response()->json($response);
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
     * Utilise Meilisearch pour la recherche et retourne le temps d'exécution.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getServices(Request $request): JsonResponse
    {
        try {
            $startTime = microtime(true);

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

            // Si une recherche est spécifiée, utiliser Meilisearch
            if ($search && strlen(trim($search)) >= 2) {
                $searchStartTime = microtime(true);

                // Préparer les filtres pour Meilisearch
                $filters = [];

                if ($minPrice) {
                    $filters['min_price'] = $minPrice;
                }

                if ($maxPrice) {
                    $filters['max_price'] = $maxPrice;
                }

                if ($category && $category !== 'all') {
                    $filters['categories'] = [$category];
                }

                // Utiliser le service de recherche Meilisearch
                $searchResults = $this->searchService->searchServiceOffers($search, $filters);
                $searchTime = microtime(true) - $searchStartTime;

                // Appliquer la pagination manuelle sur les résultats de recherche
                $total = $searchResults->count();
                $offset = ($page - 1) * $perPage;
                $paginatedResults = $searchResults->slice($offset, $perPage);

                // Récupérer les IDs des services trouvés
                $serviceIds = $paginatedResults->pluck('id')->toArray();

                // Récupérer les services complets avec leurs relations
                $services = ServiceOffer::with('user')
                    ->whereIn('id', $serviceIds)
                    ->get()
                    ->keyBy('id');

                // Réorganiser selon l'ordre de Meilisearch
                $orderedServices = collect();
                foreach ($serviceIds as $id) {
                    if (isset($services[$id])) {
                        $orderedServices->push($services[$id]);
                    }
                }

                $services = $orderedServices;

            } else {
                // Utiliser la requête Eloquent classique si pas de recherche
                $query = ServiceOffer::with('user')
                    ->where('is_private', false);

                // Appliquer les filtres classiques
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
                $paginatedServices = $query->paginate($perPage, ['*'], 'page', $page);
                $services = $paginatedServices->getCollection();
                $total = $paginatedServices->total();
                $searchTime = null; // Pas de recherche Meilisearch utilisée
            }

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
                    'views' => $service->getTotalViewsAttribute(), // ✅ Compteur en temps réel (renommé)
                    'likes' => $service->getTotalLikesAttribute(), // ✅ Compteur en temps réel (renommé)
                    'popularity_score' => $service->getPopularityScore(), // ✅ Score de popularité calculé
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

            // Calculer le temps total d'exécution
            $totalExecutionTime = microtime(true) - $startTime;

            // Préparer les informations de pagination
            $paginationInfo = [];
            if (isset($paginatedServices)) {
                // Pagination Eloquent classique
                $paginationInfo = [
                    'total' => $paginatedServices->total(),
                    'per_page' => $paginatedServices->perPage(),
                    'current_page' => $paginatedServices->currentPage(),
                    'last_page' => $paginatedServices->lastPage(),
                ];
            } else {
                // Pagination manuelle pour Meilisearch
                $lastPage = ceil($total / $perPage);
                $paginationInfo = [
                    'total' => $total,
                    'per_page' => $perPage,
                    'current_page' => $page,
                    'last_page' => $lastPage,
                ];
            }

            $response = [
                'success' => true,
                'services' => $formattedServices,
                'pagination' => $paginationInfo,
                'performance' => [
                    'total_execution_time_ms' => round($totalExecutionTime * 1000, 2),
                    'search_method' => $search && strlen(trim($search)) >= 2 ? 'meilisearch' : 'eloquent',
                ],
            ];

            // Ajouter le temps de recherche Meilisearch si disponible
            if ($searchTime !== null) {
                $response['performance']['meilisearch_time_ms'] = round($searchTime * 1000, 2);
                $response['performance']['search_query'] = $search;
            }

            return response()->json($response);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des services: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des services: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Récupère les statistiques de performance de recherche Meilisearch.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getSearchStats(Request $request): JsonResponse
    {
        try {
            $startTime = microtime(true);

            // Test de connectivité Meilisearch
            $meilisearchHost = config('scout.meilisearch.host');
            $meilisearchKey = config('scout.meilisearch.key');
            $scoutDriver = config('scout.driver');

            // Statistiques de base
            $stats = [
                'configuration' => [
                    'scout_driver' => $scoutDriver,
                    'meilisearch_host' => $meilisearchHost,
                    'meilisearch_configured' => !empty($meilisearchHost),
                ],
                'models' => [
                    'professional_profiles' => [
                        'total_records' => ProfessionalProfile::count(),
                        'searchable_records' => ProfessionalProfile::where('completion_percentage', '>=', 80)->count(),
                        'index_name' => (new ProfessionalProfile())->searchableAs(),
                    ],
                    'service_offers' => [
                        'total_records' => ServiceOffer::count(),
                        'searchable_records' => ServiceOffer::where('is_private', false)->count(),
                        'index_name' => (new ServiceOffer())->searchableAs(),
                    ],
                ],
                'performance' => [
                    'stats_generation_time_ms' => 0, // Sera calculé à la fin
                ],
            ];

            // Test de recherche rapide pour mesurer les performances
            if ($scoutDriver === 'meilisearch') {
                try {
                    $testSearchStart = microtime(true);
                    $testResults = ServiceOffer::search('test')->take(1)->get();
                    $testSearchTime = microtime(true) - $testSearchStart;

                    $stats['performance']['test_search_time_ms'] = round($testSearchTime * 1000, 2);
                    $stats['performance']['meilisearch_available'] = true;
                } catch (\Exception $e) {
                    $stats['performance']['meilisearch_available'] = false;
                    $stats['performance']['meilisearch_error'] = $e->getMessage();
                }
            } else {
                $stats['performance']['meilisearch_available'] = false;
                $stats['performance']['reason'] = 'Scout driver is not set to meilisearch';
            }

            // Calculer le temps total
            $totalTime = microtime(true) - $startTime;
            $stats['performance']['stats_generation_time_ms'] = round($totalTime * 1000, 2);

            return response()->json([
                'success' => true,
                'stats' => $stats,
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des statistiques de recherche: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques de recherche: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Liste toutes les données actuellement indexées sur MeiliSearch.
     * Retourne les documents de tous les index avec pagination.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function listIndexedData(Request $request): JsonResponse
    {
        try {
            $startTime = microtime(true);

            // Paramètres de pagination
            $perPage = $request->input('per_page', 20);
            $page = $request->input('page', 1);
            $indexFilter = $request->input('index'); // Filtrer par index spécifique

            // Vérifier que Meilisearch est configuré
            $scoutDriver = config('scout.driver');
            if ($scoutDriver !== 'meilisearch') {
                return response()->json([
                    'success' => false,
                    'message' => 'MeiliSearch is not configured as the search driver',
                ], 400);
            }

            $allIndexedData = collect();
            $indexStats = [];

            // Récupérer les données des profils professionnels
            if (!$indexFilter || $indexFilter === 'professional_profiles_index') {
                try {
                    $professionals = ProfessionalProfile::where('completion_percentage', '>=', 80)
                        ->get()
                        ->map(function ($prof) {
                            return [
                                'id' => $prof->id,
                                'type' => 'professional_profile',
                                'index' => 'professional_profiles_index',
                                'data' => $prof->toSearchableArray(),
                                'created_at' => $prof->created_at,
                                'updated_at' => $prof->updated_at,
                            ];
                        });

                    $allIndexedData = $allIndexedData->concat($professionals);
                    $indexStats['professional_profiles_index'] = [
                        'count' => $professionals->count(),
                        'index_name' => 'professional_profiles_index',
                    ];
                } catch (\Exception $e) {
                    Log::error('Erreur lors de la récupération des profils professionnels: ' . $e->getMessage());
                }
            }

            // Récupérer les données des offres de service
            if (!$indexFilter || $indexFilter === 'service_offers_index') {
                try {
                    $services = ServiceOffer::where('is_private', false)
                        ->get()
                        ->map(function ($service) {
                            return [
                                'id' => $service->id,
                                'type' => 'service_offer',
                                'index' => 'service_offers_index',
                                'data' => [
                                    'id' => $service->id,
                                    'title' => $service->title,
                                    'description' => $service->description,
                                    'price' => $service->price,
                                    'status' => $service->status,
                                    'is_private' => $service->is_private,
                                    'categories' => $service->categories ?? [],
                                    'rating' => $service->rating,
                                    'views' => $service->getTotalViewsAttribute(),
                                    'likes' => $service->getTotalLikesAttribute(),
                                    'user_id' => $service->user_id,
                                    'type' => 'service_offer',
                                ],
                                'created_at' => $service->created_at,
                                'updated_at' => $service->updated_at,
                            ];
                        });

                    $allIndexedData = $allIndexedData->concat($services);
                    $indexStats['service_offers_index'] = [
                        'count' => $services->count(),
                        'index_name' => 'service_offers_index',
                    ];
                } catch (\Exception $e) {
                    Log::error('Erreur lors de la récupération des offres de service: ' . $e->getMessage());
                }
            }

            // Récupérer les données des réalisations
            if (!$indexFilter || $indexFilter === 'achievements_index') {
                try {
                    $achievements = Achievement::where('status', '!=', 'draft')
                        ->get()
                        ->map(function ($achievement) {
                            return [
                                'id' => $achievement->id,
                                'type' => 'achievement',
                                'index' => 'achievements_index',
                                'data' => $achievement->toSearchableArray(),
                                'created_at' => $achievement->created_at,
                                'updated_at' => $achievement->updated_at,
                            ];
                        });

                    $allIndexedData = $allIndexedData->concat($achievements);
                    $indexStats['achievements_index'] = [
                        'count' => $achievements->count(),
                        'index_name' => 'achievements_index',
                    ];
                } catch (\Exception $e) {
                    Log::error('Erreur lors de la récupération des réalisations: ' . $e->getMessage());
                }
            }

            // Appliquer la pagination
            $total = $allIndexedData->count();
            $offset = ($page - 1) * $perPage;
            $paginatedData = $allIndexedData->slice($offset, $perPage)->values();

            // Calculer le temps total d'exécution
            $totalExecutionTime = microtime(true) - $startTime;

            return response()->json([
                'success' => true,
                'data' => $paginatedData,
                'pagination' => [
                    'total' => $total,
                    'per_page' => $perPage,
                    'current_page' => $page,
                    'last_page' => ceil($total / $perPage),
                ],
                'index_stats' => $indexStats,
                'performance' => [
                    'total_execution_time_ms' => round($totalExecutionTime * 1000, 2),
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des données indexées: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des données indexées: ' . $e->getMessage(),
            ], 500);
        }
    }
}
