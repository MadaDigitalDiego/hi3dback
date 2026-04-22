<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreServiceOfferRequest;
use App\Http\Requests\UpdateServiceOfferRequest;
use App\Http\Resources\ServiceOfferResource;
use App\Models\ServiceOffer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use App\Services\ImageStorageService;

class ServiceOfferController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        try {
            $serviceOffers = ServiceOffer::with(['user.freelanceProfile'])->latest()->paginate(10);
            return response()->json(ServiceOfferResource::collection($serviceOffers));
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération de la liste des offres de service: ' . $e->getMessage());
            return response()->json(['message' => 'Error while retrieving service offers.'], 500);
        }
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreServiceOfferRequest $request): JsonResponse
    {
        try {
            $validatedData = $request->validated();

            if (array_key_exists('youtube_links', $validatedData)) {
                $youtubeLinks = $validatedData['youtube_links'];
                if (!is_array($youtubeLinks)) {
                    $youtubeLinks = [];
                }
                $youtubeLinks = array_values(array_filter($youtubeLinks, function ($value) {
                    return is_string($value) && trim($value) !== '';
                }));
                $validatedData['youtube_links'] = $youtubeLinks;

                if (empty($validatedData['youtube_link'] ?? null) && !empty($youtubeLinks)) {
                    $validatedData['youtube_link'] = $youtubeLinks[0];
                }
            } elseif (!empty($validatedData['youtube_link'] ?? null)) {
                $validatedData['youtube_links'] = [$validatedData['youtube_link']];
            }

            // Gestion de l'upload des fichiers si présents
            $filePaths = [];
            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    try {
                        $path = app(ImageStorageService::class)->storeAsWebp($file, 'service_offer_files', 'public');
                        $filePaths[] = [
                            'path' => $path,
                            'original_name' => $file->getClientOriginalName(),
                            'mime_type' => $file->getMimeType(),
                            'size' => $file->getSize()
                        ];
                    } catch (\Exception $e) {
                        Log::error('Erreur lors de l\'upload du fichier: ' . $e->getMessage());
                        return response()->json(['message' => 'Error while uploading a file.'], 500);
                    }
                }
                $validatedData['files'] = $filePaths;
            }

            // Convertir les catégories en JSON si nécessaire
            if (isset($validatedData['categories']) && is_array($validatedData['categories'])) {
                $validatedData['categories'] = $validatedData['categories'];
            }

            $serviceOffer = ServiceOffer::create($validatedData + ['user_id' => auth()->id()]);

            return response()->json(new ServiceOfferResource($serviceOffer), 201);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la création de l\'offre de service: ' . $e->getMessage());
            return response()->json(['message' => 'Error while creating the service offer.'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ServiceOffer $serviceOffer): JsonResponse
    {
        try {
            return response()->json(new ServiceOfferResource($serviceOffer));
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'affichage de l\'offre de service ID ' . $serviceOffer->id . ': ' . $e->getMessage());
            return response()->json(['message' => 'Error while retrieving the service offer.'], 500);
        }
    }

    /**
     * Display the public details of a service offer.
     *
     * @param int $id Service offer ID
     * @return JsonResponse
     */
    public function showPublic(int $id): JsonResponse
    {
        try {
            $serviceOffer = ServiceOffer::with('user')->findOrFail($id);

            // Increment view count if the column exists
            if (Schema::hasColumn('service_offers', 'views')) {
                $serviceOffer->increment('views');
            }

            // Get reviews for this service
            $reviews = [];

            // Get similar services (same category)
            $similarServices = [];

            // Simplify the response for now to debug the issue
            return response()->json(new ServiceOfferResource($serviceOffer));
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'affichage public de l\'offre de service ID ' . $id . ': ' . $e->getMessage());
            return response()->json(['message' => 'Error while retrieving the service offer: ' . $e->getMessage()], 500);
        }
    }



    /**
     * Update the resource in storage.
     */
    public function update(UpdateServiceOfferRequest $request, $id): JsonResponse
    {
        try {
            // Récupérer l'offre de service
            $serviceOffer = ServiceOffer::findOrFail($id);

            // Vérification d'autorisation
            if ($serviceOffer->user_id != auth()->id()) {
                return response()->json(['message' => 'Not authorized to update this service offer.'], 403);
            }

            $validatedData = $request->validated();

            if (array_key_exists('youtube_links', $validatedData)) {
                $youtubeLinks = $validatedData['youtube_links'];
                if (!is_array($youtubeLinks)) {
                    $youtubeLinks = [];
                }
                $youtubeLinks = array_values(array_filter($youtubeLinks, function ($value) {
                    return is_string($value) && trim($value) !== '';
                }));
                $validatedData['youtube_links'] = $youtubeLinks;

                if (empty($validatedData['youtube_link'] ?? null)) {
                    $validatedData['youtube_link'] = !empty($youtubeLinks) ? $youtubeLinks[0] : null;
                }
            } elseif (array_key_exists('youtube_link', $validatedData)) {
                if (!empty($validatedData['youtube_link'])) {
                    $validatedData['youtube_links'] = [$validatedData['youtube_link']];
                } else {
                    $validatedData['youtube_links'] = [];
                }
            }

            // Gestion des fichiers
            if ($request->hasFile('files')) {
                $filePaths = [];

                // Suppression des anciens fichiers
                if ($serviceOffer->files && is_array($serviceOffer->files)) {
                    foreach ($serviceOffer->files as $file) {
                        if (isset($file['path'])) {
                            Storage::disk('public')->delete($file['path']);
                        }
                    }
                }

                // Upload des nouveaux fichiers
                foreach ($request->file('files') as $file) {
                    try {
                        $path = app(ImageStorageService::class)->storeAsWebp($file, 'service_offer_files', 'public');
                        $filePaths[] = [
                            'path' => $path,
                            'original_name' => $file->getClientOriginalName(),
                            'mime_type' => $file->getMimeType(),
                            'size' => $file->getSize()
                        ];
                    } catch (\Exception $e) {
                        Log::error('Erreur lors de l\'upload du fichier (mise à jour): ' . $e->getMessage());
                        return response()->json(['message' => 'Error while uploading a file during update.'], 500);
                    }
                }
                $validatedData['files'] = $filePaths;
            }

            // Conversion des catégories
            if (isset($validatedData['categories']) && is_array($validatedData['categories'])) {
                $validatedData['categories'] = $validatedData['categories'];
            }

            // Mise à jour
            $serviceOffer->update($validatedData);

            return response()->json(new ServiceOfferResource($serviceOffer), 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Service offer not found.'], 404);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise à jour de l\'offre de service ID ' . $id . ': ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return response()->json([
                'message' => 'Error while updating the service offer.',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    /**
     * Remove the resource from storage.
     */
    public function destroy($id): JsonResponse
    {
        try {
            // Récupérer l'offre de service par son ID
            $serviceOffer = ServiceOffer::findOrFail($id);

            // Vérifier si l'utilisateur est autorisé à supprimer cette offre
            if ($serviceOffer->user_id != auth()->id()) {
                return response()->json(['message' => 'Not authorized to delete this service offer.'], 403);
            }

            // Supprimer les fichiers associés s'ils existent
            if ($serviceOffer->files && is_array($serviceOffer->files)) {
                foreach ($serviceOffer->files as $file) {
                    if (isset($file['path'])) {
                        Storage::disk('public')->delete($file['path']);
                    }
                }
            }

            // Supprimer l'offre de service
            $serviceOffer->delete();

            return response()->json(['message' => 'Service offer deleted successfully.'], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Service offer not found.'], 404);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la suppression de l\'offre de service ID ' . $id . ': ' . $e->getMessage());
            return response()->json(['message' => 'Error while deleting the service offer.'], 500);
        }
    }


    /**
     * Search for service offers based on a query.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $query = $request->input('query');
            $serviceOffers = ServiceOffer::search($query)->paginate(10);

            return response()->json(ServiceOfferResource::collection($serviceOffers));
        } catch (\Exception $e) {
            Log::error('Erreur lors de la recherche des offres de service avec la requête "' . $request->input('query') . '": ' . $e->getMessage());
            return response()->json(['message' => 'Error while searching service offers.'], 500);
        }
    }


    /**
     * Download the file associated with the service offer.
     *
     * @param ServiceOffer $serviceOffer
     * @return \Symfony\Component\HttpFoundation\StreamedResponse|JsonResponse
     */
    public function downloadFile(ServiceOffer $serviceOffer, Request $request)
    {

        try {
            $fileIndex = $request->query('file_index', 0);

            if (!$serviceOffer->files || !is_array($serviceOffer->files) || !isset($serviceOffer->files[$fileIndex])) {
                return response()->json(['message' => 'File not found for this service offer.'], 404);
            }

            $file = $serviceOffer->files[$fileIndex];

            if (!isset($file['path']) || !Storage::disk('public')->exists($file['path'])) {
                return response()->json(['message' => 'The requested file does not exist or has been deleted.'], 404);
            }

            $fileContent = Storage::disk('public')->get($file['path']);
            $fileName = $file['original_name'] ?? basename($file['path']);
            $mimeType = $file['mime_type'] ?? 'application/octet-stream';

            return response($fileContent)
                ->header('Content-Type', $mimeType)
                ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
        } catch (\Exception $e) {
            Log::error('Erreur lors du téléchargement du fichier pour l\'offre de service ID ' . $serviceOffer->id . ': ' . $e->getMessage());
            return response()->json(['message' => 'Error while downloading the file.'], 500);
        }
    }

    /**
     * Get service offers by professional ID.
     *
     * @param int $id Professional user ID
     * @return JsonResponse
     */
    public function getServiceOffersByProfessional(int $id): JsonResponse
    {
        try {
            // Vérifier si l'utilisateur existe et est un professionnel
            $professional = \App\Models\User::where('id', $id)
                ->where('is_professional', true)
                ->first();

            if (!$professional) {
                // Vérifier si l'utilisateur existe mais n'est pas marqué comme professionnel
                $user = \App\Models\User::find($id);
                if ($user) {
                    // Si l'utilisateur existe, considérer qu'il est un professionnel
                    // (pour la compatibilité avec les données existantes)
                    Log::info('Utilisateur trouvé mais non marqué comme professionnel: ' . $id);
                } else {
                    return response()->json(['message' => 'Professional not found.'], 404);
                }
            }

            // Récupérer les services du professionnel
            $serviceOffers = ServiceOffer::where('user_id', $id)
                ->with('user')
                ->latest()
                ->get();

            // Log pour le débogage
            Log::info('Services récupérés pour le professionnel ID ' . $id . ': ' . $serviceOffers->count());

            return response()->json(ServiceOfferResource::collection($serviceOffers));
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des services du professionnel ID ' . $id . ': ' . $e->getMessage());
            return response()->json(['message' => 'Error while retrieving professional services.'], 500);
        }
    }
    /**
     * Filter service offers based on various criteria.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function filter(Request $request): JsonResponse
    {
        try {
            $query = ServiceOffer::with('user');

            // Filtrage par recherche
            if ($request->has('search') && !empty($request->input('search'))) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            }

            // Filtrage par catégorie
            if ($request->has('category') && $request->input('category') !== 'all') {
                $category = $request->input('category');
                $query->whereRaw("JSON_SEARCH(categories, 'one', ?) IS NOT NULL", [$category]);
            }

            // Filtrage par fourchette de prix
            if ($request->has('price_min') && $request->has('price_max')) {
                $minPrice = $request->input('price_min');
                $maxPrice = $request->input('price_max');

                if ($minPrice > 0) {
                    $query->where('price', '>=', $minPrice);
                }

                if ($maxPrice > 0 && $maxPrice > $minPrice) {
                    $query->where('price', '<=', $maxPrice);
                }
            }

            // Filtrage par temps d'exécution
            if ($request->has('execution_time') && $request->input('execution_time') !== 'all') {
                $executionTime = $request->input('execution_time');

                switch ($executionTime) {
                    case 'express':
                        $query->where('delivery_time', '<=', 3); // Moins de 3 jours
                        break;
                    case 'standard':
                        $query->whereBetween('delivery_time', [4, 14]); // 1-2 semaines
                        break;
                    case 'extended':
                        $query->where('delivery_time', '>', 14); // Plus de 2 semaines
                        break;
                }
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
            } else {
                $query->orderBy('created_at', 'desc');
            }

            // Récupérer les services filtrés
            $serviceOffers = $query->get();

            return response()->json(ServiceOfferResource::collection($serviceOffers));
        } catch (\Exception $e) {
            Log::error('Erreur lors du filtrage des offres de service: ' . $e->getMessage());
            return response()->json(['message' => 'Error while filtering service offers.'], 500);
        }
    }
}
