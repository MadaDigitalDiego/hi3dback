<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\ClientProfile;
use Illuminate\Http\JsonResponse;
use App\Models\ProfessionalProfile;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use App\Services\ProfileCacheService;
use App\Mail\ProfileUpdateNotification;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\UpdateAvailabilityRequest;

class ProfileController extends Controller
{
    /**
     * Le service de cache pour les profils
     *
     * @var ProfileCacheService
     */
    protected $profileCacheService;

    /**
     * Constructeur
     *
     * @param ProfileCacheService $profileCacheService
     */
    public function __construct(ProfileCacheService $profileCacheService)
    {
        $this->profileCacheService = $profileCacheService;
    }
    /**
     * Récupérer le profil de l'utilisateur authentifié (client ou professionnel)
     *
     * @OA\Get(
     *     path="/profile",
     *     summary="Récupère le profil de l'utilisateur authentifié",
     *     tags={"Profile"},
     *     security={"bearerAuth": {}},
     *     @OA\Response(
     *         response=200,
     *         description="Profil récupéré avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="profile", type="object", ref="#/components/schemas/ProfileData")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Profil non trouvé"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur"
     *     )
     * )
     *
     * @return JsonResponse
     */
    public function getProfile(): JsonResponse
    {
        try {
            $user = auth()->user();

            if ($user->is_professional) {
                // Récupérer le profil professionnel depuis le cache
                $profile = $this->profileCacheService->getProfessionalProfile($user->id);

                if (!$profile) {
                    return response()->json(['message' => 'Profil professionnel non trouvé.'], 404);
                }
            } else {
                // Récupérer le profil client depuis le cache
                $profile = $this->profileCacheService->getClientProfile($user->id);

                if (!$profile) {
                    // Créer automatiquement un profil client de base
                    $profile = ClientProfile::create([
                        'user_id' => $user->id,
                        'first_name' => $user->first_name,
                        'last_name' => $user->last_name,
                        'email' => $user->email,
                        'completion_percentage' => 20,
                    ]);

                    // Mettre à jour le cache
                    $this->profileCacheService->updateClientProfileCache($profile);
                }
            }

            // Ajouter les informations de l'utilisateur au profil
            $profileData = $profile->toArray();
            $profileData['user'] = [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'is_professional' => $user->is_professional,
                'created_at' => $user->created_at,
            ];

            return response()->json(['profile' => $profileData], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération du profil: ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la récupération du profil.'], 500);
        }
    }

    /**
     * Mettre à jour le profil de l'utilisateur authentifié
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateProfile(Request $request): JsonResponse
    {
        try {
            $user = auth()->user();

            if ($user->is_professional) {
                $profile = $user->professionalProfile;

                if (!$profile) {
                    return response()->json(['message' => 'Profil professionnel non trouvé.'], 404);
                }

                // Traiter l'upload de l'avatar si présent
                if ($request->hasFile('avatar')) {
                    $avatar = $request->file('avatar');
                    $filename = time() . '_' . $avatar->getClientOriginalName();
                    $path = $avatar->storeAs('avatars', $filename, 'public');
                    $request->merge(['avatar' => '/storage/' . $path]);
                }

                // Traiter l'upload de la photo de couverture si présente
                if ($request->hasFile('cover_photo')) {
                    $coverPhoto = $request->file('cover_photo');
                    $filename = time() . '_' . $coverPhoto->getClientOriginalName();
                    $path = $coverPhoto->storeAs('cover_photos', $filename, 'public');
                        $request->merge(['cover_photo' => '/storage/' . $path]);
                    }

                // Traiter les liens sociaux
                if ($request->has('social_links')) {
                    $socialLinksData = $request->input('social_links');

                    if (is_string($socialLinksData)) {
                        $socialLinksData = json_decode($socialLinksData, true);
                    }

                    if (is_array($socialLinksData)) {
                        $socialLinks = $profile->social_links ?? [];
                        foreach ($socialLinksData as $key => $value) {
                            $socialLinks[$key] = $value;
                        }
                        $request->merge(['social_links' => $socialLinks]);
                    }
                }

                // Calculer le pourcentage de complétion
                $completionPercentage = $this->calculateProfessionalProfileCompletionPercentage($profile, $request->all());
                $request->merge(['completion_percentage' => $completionPercentage]);

                // Mettre à jour le profil
                $profile->update($request->except(['_method', '_token']));

                // Mettre à jour le cache
                $this->profileCacheService->updateProfessionalProfileCache($profile);
            } else {
                $profile = $this->profileCacheService->getClientProfile($user->id);

                if (!$profile) {
                    return response()->json(['message' => 'Profil client non trouvé.'], 404);
                }

                // Traiter l'upload de l'avatar si présent
                if ($request->hasFile('avatar')) {
                    $avatar = $request->file('avatar');
                    $filename = time() . '_' . $avatar->getClientOriginalName();
                    $path = $avatar->storeAs('avatars', $filename, 'public');
                    $request->merge(['avatar' => '/storage/' . $path]);
                }

                // Traiter les liens sociaux
                if ($request->has('social_links')) {
                    $socialLinksData = $request->input('social_links');

                    if (is_string($socialLinksData)) {
                        $socialLinksData = json_decode($socialLinksData, true);
                    }

                    if (is_array($socialLinksData)) {
                        $socialLinks = $profile->social_links ?? [];
                        foreach ($socialLinksData as $key => $value) {
                            $socialLinks[$key] = $value;
                        }
                        $request->merge(['social_links' => $socialLinks]);
                    }
                }

                // Calculer le pourcentage de complétion
                $completionPercentage = $this->calculateClientProfileCompletionPercentage($profile, $request->all());
                $request->merge(['completion_percentage' => $completionPercentage]);

                // Mettre à jour le profil
                $profile->update($request->except(['_method', '_token']));

                // Mettre à jour le cache
                $this->profileCacheService->updateClientProfileCache($profile);
            }

            // Mettre à jour les informations de base de l'utilisateur si fournies
            $userModel = User::find($user->id);
            if ($userModel) {
                $userModel->update($request->only(['first_name', 'last_name', 'email']));

                // Envoyer un email de notification
                Mail::to($userModel->email)->send(new ProfileUpdateNotification());
            }

            return response()->json([
                'message' => 'Profil mis à jour avec succès.',
                'profile' => $user->is_professional ? $user->professionalProfile : $user->clientProfile
            ], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise à jour du profil: ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la mise à jour du profil.'], 500);
        }
    }

    /**
     * Compléter le profil (première connexion)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function completeProfile(Request $request): JsonResponse
    {
        try {
            $user = auth()->user();

            if ($user->is_professional) {
                // Validation des données pour un profil professionnel
                $request->validate([
                    'first_name' => 'required|string|max:255',
                    'last_name' => 'required|string|max:255',
                    'phone' => 'required|string|max:20',
                    'address' => 'required|string|max:255',
                    'city' => 'required|string|max:255',
                    'country' => 'required|string|max:255',
                    'bio' => 'required|string',
                    'title' => 'required|string|max:255',
                    'skills' => 'required|array',
                    'hourly_rate' => 'required|numeric|min:0',
                ]);

                $profile = $user->professionalProfile;

                if (!$profile) {
                    $profile = ProfessionalProfile::create(array_merge($request->all(), ['user_id' => $user->id]));
                } else {
                    $profile->update($request->all());
                }

                // Calculer le pourcentage de complétion
                $completionPercentage = $this->calculateProfessionalProfileCompletionPercentage($profile, []);
                $profile->completion_percentage = $completionPercentage;
                $profile->save();
            } else {
                // Validation des données pour un profil client
                $request->validate([
                    'first_name' => 'required|string|max:255',
                    'last_name' => 'required|string|max:255',
                    'phone' => 'required|string|max:20',
                    'address' => 'required|string|max:255',
                    'city' => 'required|string|max:255',
                    'country' => 'required|string|max:255',
                    'type' => 'required|in:particulier,entreprise',
                ]);

                // Validation supplémentaire pour les entreprises
                if ($request->input('type') === 'entreprise') {
                    $request->validate([
                        'company_name' => 'required|string|max:255',
                        'industry' => 'required|string|max:255',
                    ]);
                }

                $profile = $user->clientProfile;

                if (!$profile) {
                    $profile = ClientProfile::create(array_merge($request->all(), ['user_id' => $user->id]));
                } else {
                    $profile->update($request->all());
                }

                // Calculer le pourcentage de complétion
                $completionPercentage = $this->calculateClientProfileCompletionPercentage($profile, []);
                $profile->completion_percentage = $completionPercentage;
                $profile->save();
            }

            // Marquer que le profil a été complété
            $userModel = User::find($user->id);
            if ($userModel) {
                $userModel->profile_completed = true;
                $userModel->save();
            }

            return response()->json([
                'message' => 'Profil complété avec succès.',
                'profile' => $profile
            ], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la complétion du profil: ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la complétion du profil.'], 500);
        }
    }

    /**
     * Récupérer le statut de complétion du profil
     *
     * @return JsonResponse
     */
    public function getCompletionStatus(): JsonResponse
    {
        try {
            $user = auth()->user();

            if ($user->is_professional) {
                $profile = $user->professionalProfile;

                if (!$profile) {
                    return response()->json([
                        'message' => 'Profil professionnel non trouvé.',
                        'completion_percentage' => 0
                    ], 404);
                }

                $completionPercentage = $this->calculateProfessionalProfileCompletionPercentage($profile, []);
            } else {
                $profile = $user->clientProfile;

                if (!$profile) {
                    return response()->json([
                        'message' => 'Profil client non trouvé.',
                        'completion_percentage' => 0
                    ], 404);
                }

                $completionPercentage = $this->calculateClientProfileCompletionPercentage($profile, []);
            }

            return response()->json([
                'completion_percentage' => $completionPercentage,
                'is_completed' => $completionPercentage >= 70
            ], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération du statut de complétion du profil: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erreur lors de la récupération du statut de complétion du profil.',
                'error' => $e->getMessage(),
                'completion_percentage' => 0
            ], 500);
        }
    }

    /**
     * Uploader un avatar
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function uploadAvatar(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'avatar' => 'required|file|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            $user = auth()->user();

            if ($user->is_professional) {
                $profile = $user->professionalProfile;

                if (!$profile) {
                    return response()->json(['message' => 'Profil professionnel non trouvé.'], 404);
                }
            } else {
                $profile = $user->clientProfile;

                if (!$profile) {
                    return response()->json(['message' => 'Profil client non trouvé.'], 404);
                }
            }

            // Supprimer l'ancien avatar s'il existe
            if ($profile->avatar && strpos($profile->avatar, '/storage/') === 0) {
                $oldPath = str_replace('/storage/', '', $profile->avatar);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }

            // Uploader le nouvel avatar
            $avatar = $request->file('avatar');
            $filename = time() . '_' . $avatar->getClientOriginalName();
            $path = $avatar->storeAs('avatars', $filename, 'public');

            // Mettre à jour le profil
            $profile->avatar = '/storage/' . $path;
            $profile->save();

            return response()->json([
                'message' => 'Avatar uploadé avec succès.',
                'avatar_url' => $profile->avatar
            ], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'upload de l\'avatar: ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de l\'upload de l\'avatar.'], 500);
        }
    }

    /**
     * Uploader un élément de portfolio
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function uploadPortfolioItem(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'file' => 'required|file|mimes:jpeg,png,jpg,gif,pdf,doc,docx,zip|max:10240',
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
            ]);

            $user = auth()->user();

            if (!$user->is_professional) {
                return response()->json(['message' => 'Seuls les professionnels peuvent avoir un portfolio.'], 403);
            }

            $profile = $user->professionalProfile;

            if (!$profile) {
                return response()->json(['message' => 'Profil professionnel non trouvé.'], 404);
            }

            // Uploader le fichier
            $file = $request->file('file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('portfolio', $filename, 'public');

            // Ajouter l'élément au portfolio
            $portfolio = $profile->portfolio ?? [];
            $portfolio[] = [
                'id' => uniqid(),
                'path' => '/storage/' . $path,
                'name' => $request->input('name'),
                'description' => $request->input('description'),
                'type' => $file->getClientMimeType(),
                'created_at' => now()->toDateTimeString(),
            ];

            // Mettre à jour le profil
            $profile->portfolio = $portfolio;
            $profile->save();

            return response()->json([
                'message' => 'Élément de portfolio ajouté avec succès.',
                'portfolio_item' => end($portfolio)
            ], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'upload d\'un élément de portfolio: ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de l\'upload d\'un élément de portfolio.'], 500);
        }
    }

    /**
     * Supprimer un élément de portfolio
     *
     * @param string $id
     * @return JsonResponse
     */
    public function deletePortfolioItem(string $id): JsonResponse
    {
        try {
            $user = auth()->user();

            if (!$user->is_professional) {
                return response()->json(['message' => 'Seuls les professionnels peuvent avoir un portfolio.'], 403);
            }

            $profile = $user->professionalProfile;

            if (!$profile) {
                return response()->json(['message' => 'Profil professionnel non trouvé.'], 404);
            }

            $portfolio = $profile->portfolio ?? [];
            $itemIndex = null;
            $itemToDelete = null;

            // Trouver l'élément à supprimer
            foreach ($portfolio as $index => $item) {
                if (isset($item['id']) && $item['id'] === $id) {
                    $itemIndex = $index;
                    $itemToDelete = $item;
                    break;
                }
            }

            if ($itemIndex === null) {
                return response()->json(['message' => 'Élément de portfolio non trouvé.'], 404);
            }

            // Supprimer le fichier
            if (isset($itemToDelete['path']) && strpos($itemToDelete['path'], '/storage/') === 0) {
                $filePath = str_replace('/storage/', '', $itemToDelete['path']);
                if (Storage::disk('public')->exists($filePath)) {
                    Storage::disk('public')->delete($filePath);
                }
            }

            // Supprimer l'élément du portfolio
            array_splice($portfolio, $itemIndex, 1);

            // Mettre à jour le profil
            $profile->portfolio = $portfolio;
            $profile->save();

            return response()->json([
                'message' => 'Élément de portfolio supprimé avec succès.'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la suppression d\'un élément de portfolio: ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la suppression d\'un élément de portfolio.'], 500);
        }
    }

    /**
     * Mettre à jour la disponibilité
     *
     * @param UpdateAvailabilityRequest $request
     * @return JsonResponse
     */
    public function updateAvailability(UpdateAvailabilityRequest $request): JsonResponse
    {
        try {
            $user = auth()->user();

            if (!$user->is_professional) {
                return response()->json(['message' => 'Seuls les professionnels peuvent mettre à jour leur disponibilité.'], 403);
            }

            $profile = $user->professionalProfile;

            if (!$profile) {
                return response()->json(['message' => 'Profil professionnel non trouvé.'], 404);
            }

            $profile->availability_status = $request->availability_status;
            $profile->save();

            return response()->json([
                'message' => 'Disponibilité mise à jour avec succès.',
                'availability_status' => $profile->availability_status
            ], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise à jour de la disponibilité: ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la mise à jour de la disponibilité.'], 500);
        }
    }

    /**
     * Calculer le pourcentage de complétion d'un profil professionnel
     *
     * @param ProfessionalProfile $profile
     * @param array $data
     * @return int
     */
    private function calculateProfessionalProfileCompletionPercentage(ProfessionalProfile $profile, array $data): int
    {
        // Fusionner les données existantes avec les nouvelles données
        $mergedData = array_merge($profile->toArray(), $data);

        // Définir les champs requis pour un profil complet
        $requiredFields = [
            'first_name', 'last_name', 'email', 'phone', 'address', 'city', 'country',
            'bio', 'avatar', 'title', 'profession', 'expertise', 'skills', 'hourly_rate'
        ];

        // Compter les champs remplis
        $filledFields = 0;
        foreach ($requiredFields as $field) {
            if (!empty($mergedData[$field])) {
                $filledFields++;
            }
        }

        // Calculer le pourcentage
        return (int) round(($filledFields / count($requiredFields)) * 100);
    }

    /**
     * Calculer le pourcentage de complétion d'un profil client
     *
     * @param ClientProfile $profile
     * @param array $data
     * @return int
     */
    private function calculateClientProfileCompletionPercentage(ClientProfile $profile, array $data): int
    {
        // Fusionner les données existantes avec les nouvelles données
        $mergedData = array_merge($profile->toArray(), $data);

        // Définir les champs requis pour un profil complet
        $requiredFields = [
            'first_name', 'last_name', 'email', 'phone', 'address', 'city', 'country',
            'bio', 'avatar'
        ];

        // Ajouter des champs supplémentaires pour les entreprises
        if (isset($mergedData['type']) && $mergedData['type'] === 'entreprise') {
            $requiredFields = array_merge($requiredFields, ['company_name', 'industry', 'position']);
        }

        // Compter les champs remplis
        $filledFields = 0;
        foreach ($requiredFields as $field) {
            if (!empty($mergedData[$field])) {
                $filledFields++;
            }
        }

        // Calculer le pourcentage
        return (int) round(($filledFields / count($requiredFields)) * 100);
    }
}
