<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ClientProfileRequest;
use App\Http\Requests\ProfessionalProfileRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Requests\UpdateClientProfileRequest;
use App\Mail\ProfileUpdateNotification;
use App\Models\ClientProfile;
use App\Models\ProfessionalProfile;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\UpdateAvailabilityRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * @OA\Info(
 *     title="API de Profil",
 *     version="1.0.0",
 *     description="API pour la gestion des profils utilisateurs",
 *     @OA\Contact(
 *         email="contact@example.com",
 *         name="Support API"
 *     )
 * )
 *
 * @OA\Server(
 *     url="/api",
 *     description="Serveur API"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */

class ProfileController extends Controller
{
    public function createProfessionalProfile(ProfessionalProfileRequest $request): JsonResponse
    {
        try {
            $profile = ProfessionalProfile::create(array_merge($request->validated(), ['user_id' => auth()->id()]));
            return response()->json(['message' => 'Profil professionnel créé avec succès.', 'profile' => $profile], 201);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la création du profil professionnel: ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la création du profil professionnel.'], 500);
        }
    }

    public function getClientProfile($user_id): JsonResponse
    {
        try {
            $profile = ClientProfile::where('user_id', $user_id)->first();

            if (!$profile) {
                return response()->json(['message' => 'Profil client introuvable.'], 404);
            }

            return response()->json(['profile' => $profile], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération du profil client pour l\'utilisateur ID ' . $user_id . ': ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la récupération du profil client.'], 500);
        }
    }

    /**
     * Récupérer le profil client de l'utilisateur authentifié
     *
     * @return JsonResponse
     */
    public function getAuthenticatedClientProfile(): JsonResponse
    {
        try {
            $user = auth()->user();

            if ($user->is_professional) {
                return response()->json(['message' => 'L\'utilisateur authentifié n\'est pas un client.'], 400);
            }

            $profile = ClientProfile::where('user_id', $user->id)->first();

            if (!$profile) {
                // Créer automatiquement un profil client de base
                $profile = ClientProfile::create([
                    'user_id' => $user->id,
                    'first_name' => $user->first_name ?? '',
                    'last_name' => $user->last_name ?? '',
                    'email' => $user->email,
                    'completion_percentage' => 20,
                ]);
            }

            // Ajouter les informations de l'utilisateur au profil
            $profileData = $profile->toArray();
            $profileData['user'] = [
                'id' => $user->id,
                'first_name' => $user->first_name ?? '',
                'last_name' => $user->last_name ?? '',
                'email' => $user->email,
                'is_professional' => $user->is_professional,
                'created_at' => $user->created_at,
            ];

            return response()->json(['profile' => $profileData], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération du profil client authentifié: ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la récupération du profil client.'], 500);
        }
    }

    /**
     * Récupérer le profil client de l'utilisateur authentifié
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAuthClientProfile(): JsonResponse
    {
        try {
            $user = auth()->user();
            $profile = ClientProfile::where('user_id', $user->id)->first();

            // Si le profil n'existe pas, le créer automatiquement
            if (!$profile) {
                $profile = ClientProfile::create([
                    'user_id' => $user->id,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'email' => $user->email,
                    'completion_percentage' => 20, // Pourcentage initial de complétion
                ]);
            }

            // Ajouter les informations de l'utilisateur au profil
            $profileData = $profile->toArray();
            $profileData['user'] = [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'created_at' => $user->created_at,
            ];

            return response()->json(['profile' => $profileData], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération du profil client authentifié: ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la récupération du profil client.'], 500);
        }
    }


    public function createClientProfile(ClientProfileRequest $request): JsonResponse
    {
        try {
            // Récupérer l'ID de l'utilisateur authentifié
            $userId = auth()->id();
            Log::info('Début de la création/mise à jour du profil client via POST');
            Log::info('Données reçues:', $request->all());

            // Traiter l'upload de l'avatar si présent
            if ($request->hasFile('avatar')) {
                Log::info('Avatar présent dans la requête');
                $avatar = $request->file('avatar');
                $filename = time() . '_' . $avatar->getClientOriginalName();
                $path = $avatar->storeAs('avatars', $filename, 'public');
                $request->merge(['avatar' => '/storage/' . $path]);
                Log::info('Avatar enregistré: ' . $path);
            } else {
                Log::info('Aucun avatar dans la requête');
            }

            // Traiter les liens sociaux
            $socialLinks = [];
            $profile = ClientProfile::where('user_id', $userId)->first();
            $existingSocialLinks = $profile ? ($profile->social_links ?? []) : [];

            // Vérifier si nous avons des données social_links directes
            if ($request->has('social_links')) {
                Log::info('Liens sociaux présents dans la requête');
                $socialLinksData = $request->input('social_links');

                // Si les liens sociaux sont une chaîne JSON, les décoder
                if (is_string($socialLinksData)) {
                    $socialLinksData = json_decode($socialLinksData, true);
                    Log::info('Liens sociaux décodés depuis JSON:', $socialLinksData ?: []);
                }

                if (is_array($socialLinksData)) {
                    foreach ($socialLinksData as $key => $value) {
                        $socialLinks[$key] = $value;
                    }
                } else {
                    Log::warning('Les liens sociaux ne sont pas un tableau valide');
                }
            }

            // Vérifier les entrées individuelles social_links[network]
            $inputAll = $request->all();
            foreach ($inputAll as $key => $value) {
                if (preg_match('/^social_links\[(.*)\]$/', $key, $matches)) {
                    $network = $matches[1];
                    $socialLinks[$network] = $value;
                    Log::info("Lien social individuel trouvé: {$network} => {$value}");
                }
            }

            // Fusionner avec les liens existants
            $mergedSocialLinks = array_merge($existingSocialLinks, $socialLinks);
            $request->merge(['social_links' => $mergedSocialLinks]);
            Log::info('Liens sociaux fusionnés:', $mergedSocialLinks);

            // Préparer les données validées
            $validatedData = $request->validated();
            Log::info('Données validées:', $validatedData);

            // Utiliser updateOrCreate pour créer ou mettre à jour le profil client
            $profile = ClientProfile::updateOrCreate(
                ['user_id' => $userId], // Condition de recherche
                array_merge($validatedData, ['user_id' => $userId]) // Données à mettre à jour ou à créer
            );

            // Calculer le pourcentage de complétion du profil
            $completionPercentage = $this->calculateProfileCompletionPercentage($profile, $request->all());
            $profile->completion_percentage = $completionPercentage;
            $profile->save();
            Log::info('Pourcentage de complétion calculé: ' . $completionPercentage);

            Log::info('Profil client créé/mis à jour avec succès, ID: ' . $profile->id);

            // Retourner une réponse JSON
            return response()->json([
                'message' => $profile->wasRecentlyCreated ? 'Profil client créé avec succès.' : 'Profil client mis à jour avec succès.',
                'profile' => $profile,
            ], 200);

        } catch (\Exception $e) {
            // Gérer les erreurs
            Log::error('Erreur lors de la création ou mise à jour du profil client: ' . $e->getMessage());
            Log::error('Trace: ' . $e->getTraceAsString());
            return response()->json([
                'message' => 'Une erreur est survenue lors de la création ou de la mise à jour du profil client.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateClientProfile(UpdateClientProfileRequest $request): JsonResponse
    {
        try {
            Log::info('Début de la mise à jour du profil client');
            Log::info('Données reçues:', $request->all());

            // Récupérer le profil client de l'utilisateur authentifié
            $profile = ClientProfile::where('user_id', auth()->id())->first();
            Log::info('Utilisateur authentifié ID: ' . auth()->id());

            // Si aucun profil n'est trouvé, créer un nouveau profil client
            if (!$profile) {
                Log::info('Aucun profil client trouvé, création d\'un nouveau profil');
                $user = auth()->user();
                $profile = ClientProfile::create([
                    'user_id' => $user->id,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'email' => $user->email,
                    'completion_percentage' => 20, // Pourcentage initial de complétion
                ]);
                Log::info('Nouveau profil client créé avec ID: ' . $profile->id);
            } else {
                Log::info('Profil client existant trouvé avec ID: ' . $profile->id);
            }

            // Traiter l'upload de l'avatar si présent
            if ($request->hasFile('avatar')) {
                Log::info('Avatar présent dans la requête');
                $avatar = $request->file('avatar');
                $filename = time() . '_' . $avatar->getClientOriginalName();
                $path = $avatar->storeAs('avatars', $filename, 'public');
                $request->merge(['avatar' => '/storage/' . $path]);
                Log::info('Avatar enregistré: ' . $path);
            } else {
                Log::info('Aucun avatar dans la requête');
            }

            // Traiter les liens sociaux
            $socialLinks = [];
            $existingSocialLinks = $profile->social_links ?? [];

            // Vérifier si nous avons des données social_links directes
            if ($request->has('social_links')) {
                Log::info('Liens sociaux présents dans la requête');
                $socialLinksData = $request->input('social_links');

                // Si les liens sociaux sont une chaîne JSON, les décoder
                if (is_string($socialLinksData)) {
                    $socialLinksData = json_decode($socialLinksData, true);
                    Log::info('Liens sociaux décodés depuis JSON:', $socialLinksData ?: []);
                }

                if (is_array($socialLinksData)) {
                    foreach ($socialLinksData as $key => $value) {
                        $socialLinks[$key] = $value;
                    }
                } else {
                    Log::warning('Les liens sociaux ne sont pas un tableau valide');
                }
            }

            // Vérifier les entrées individuelles social_links[network]
            $inputAll = $request->all();
            foreach ($inputAll as $key => $value) {
                if (preg_match('/^social_links\[(.*)\]$/', $key, $matches)) {
                    $network = $matches[1];
                    $socialLinks[$network] = $value;
                    Log::info("Lien social individuel trouvé: {$network} => {$value}");
                }
            }

            // Fusionner avec les liens existants
            $mergedSocialLinks = array_merge($existingSocialLinks, $socialLinks);
            $request->merge(['social_links' => $mergedSocialLinks]);
            Log::info('Liens sociaux fusionnés:', $mergedSocialLinks);

            // Calculer le pourcentage de complétion du profil
            $completionPercentage = $this->calculateProfileCompletionPercentage($profile, $request->all());
            $request->merge(['completion_percentage' => $completionPercentage]);
            Log::info('Pourcentage de complétion calculé: ' . $completionPercentage);

            // Mettre à jour le profil avec les données validées
            $dataToUpdate = $request->except(['_method', '_token']);
            Log::info('Données à mettre à jour:', $dataToUpdate);
            $profile->update($dataToUpdate);
            Log::info('Profil client mis à jour avec succès');

            // Retourner une réponse de succès
            return response()->json([
                'message' => 'Profil client mis à jour avec succès.',
                'profile' => $profile
            ], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise à jour du profil client: ' . $e->getMessage());
            Log::error('Trace: ' . $e->getTraceAsString());
            return response()->json([
                'message' => 'Une erreur est survenue lors de la mise à jour du profil client.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Compléter le profil client (première connexion)
     *
     * @param UpdateClientProfileRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function completeClientProfile(UpdateClientProfileRequest $request): JsonResponse
    {
        try {
            // Récupérer le profil client de l'utilisateur authentifié
            $profile = ClientProfile::where('user_id', auth()->id())->first();

            // Si aucun profil n'est trouvé, retourner une erreur 404
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
            if ($request->has('social_links') && is_array($request->social_links)) {
                $socialLinks = $profile->social_links ?? [];
                foreach ($request->social_links as $key => $value) {
                    $socialLinks[$key] = $value;
                }
                $request->merge(['social_links' => $socialLinks]);
            }

            // Calculer le pourcentage de complétion du profil
            $completionPercentage = $this->calculateProfileCompletionPercentage($profile, $request->all());
            $request->merge(['completion_percentage' => $completionPercentage]);

            // Mettre à jour le profil avec les données validées
            $profile->update($request->except(['_method', '_token', 'is_completion']));

            // Marquer que le profil a été complété
            $user = auth()->user();
            if ($user) {
                $user->profile_completed = true;
                $user->save();
                Log::info('Profil marqué comme complété');
            } else {
                Log::warning('Impossible de marquer le profil comme complété: utilisateur non trouvé');
            }

            // Retourner une réponse de succès
            return response()->json([
                'message' => 'Profil client complété avec succès.',
                'profile' => $profile
            ], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la complétion du profil client: ' . $e->getMessage());
            return response()->json([
                'message' => 'Une erreur est survenue lors de la complétion du profil client.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer le statut de complétion du profil client
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProfileCompletionStatus(): JsonResponse
    {
        try {
            $user = auth()->user();
            $profile = ClientProfile::where('user_id', $user->id)->first();

            if (!$profile) {
                return response()->json([
                    'message' => 'Profil client non trouvé.',
                    'completion_percentage' => 0
                ], 404);
            }

            // Calculer le pourcentage de complétion du profil
            $completionPercentage = $this->calculateProfileCompletionPercentage($profile, []);

            return response()->json([
                'completion_percentage' => $completionPercentage,
                'is_completed' => $completionPercentage >= 70
            ], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération du statut de complétion du profil: ' . $e->getMessage());
            return response()->json([
                'message' => 'Une erreur est survenue lors de la récupération du statut de complétion du profil.',
                'error' => $e->getMessage(),
                'completion_percentage' => 0
            ], 500);
        }
    }

    /**
     * Récupérer le profil de l'utilisateur authentifié
     *
     * @return JsonResponse
     */
    public function getProfile(): JsonResponse
    {
        try {
            $user = auth()->user();

            if ($user->is_professional) {
                $profile = ProfessionalProfile::where('user_id', $user->id)->first();

                if (!$profile) {
                    // Créer automatiquement un profil professionnel de base
                    $profile = ProfessionalProfile::create([
                        'user_id' => $user->id,
                        'first_name' => $user->first_name ?? '',
                        'last_name' => $user->last_name ?? '',
                        'email' => $user->email,
                        'completion_percentage' => 20,
                    ]);
                }
            } else {
                $profile = ClientProfile::where('user_id', $user->id)->first();

                if (!$profile) {
                    // Créer automatiquement un profil client de base
                    $profile = ClientProfile::create([
                        'user_id' => $user->id,
                        'first_name' => $user->first_name ?? '',
                        'last_name' => $user->last_name ?? '',
                        'email' => $user->email,
                        'completion_percentage' => 20,
                    ]);
                }
            }

            // Ajouter les informations de l'utilisateur au profil
            $profileData = $profile->toArray();
            $profileData['user'] = [
                'id' => $user->id,
                'first_name' => $user->first_name ?? '',
                'last_name' => $user->last_name ?? '',
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
     * Récupérer le statut de complétion du profil (compatible avec les routes standardisées)
     *
     * @return JsonResponse
     */
    public function getCompletionStatus(): JsonResponse
    {
        try {
            $user = auth()->user();

            if ($user->is_professional) {
                $profile = ProfessionalProfile::where('user_id', $user->id)->first();

                if (!$profile) {
                    return response()->json([
                        'message' => 'Profil professionnel non trouvé.',
                        'completion_percentage' => 0
                    ], 404);
                }

                $completionPercentage = $this->calculateProfileCompletionPercentage($profile, []);
            } else {
                $profile = ClientProfile::where('user_id', $user->id)->first();

                if (!$profile) {
                    return response()->json([
                        'message' => 'Profil client non trouvé.',
                        'completion_percentage' => 0
                    ], 404);
                }

                $completionPercentage = $this->calculateProfileCompletionPercentage($profile, []);
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
     * Calculer le pourcentage de complétion du profil
     *
     * @param mixed $profile
     * @param array $data
     * @return int
     */
    private function calculateProfileCompletionPercentage($profile, array $data): int
    {
        // Fusionner les données existantes avec les nouvelles données
        $mergedData = array_merge($profile->toArray(), $data);

        // Déterminer le type de profil et définir les champs requis
        if ($profile instanceof ClientProfile) {
            // Définir les champs requis pour un profil client complet
            $requiredFields = [
                'first_name', 'last_name', 'email', 'phone', 'address', 'city', 'country',
                'bio', 'avatar', 'company_name', 'industry', 'position'
            ];
        } else if ($profile instanceof ProfessionalProfile) {
            // Définir les champs requis pour un profil professionnel complet
            $requiredFields = [
                'first_name', 'last_name', 'email', 'phone', 'address', 'city', 'country',
                'bio', 'avatar', 'title', 'skills', 'hourly_rate', 'experience', 'education'
            ];
        } else {
            // Type de profil inconnu, utiliser des champs de base
            $requiredFields = [
                'first_name', 'last_name', 'email', 'phone', 'address', 'city', 'country'
            ];
        }

        // Compter les champs remplis
        $filledFields = 0;
        foreach ($requiredFields as $field) {
            if (isset($mergedData[$field]) && !empty($mergedData[$field])) {
                $filledFields++;
            }
        }

        // Calculer le pourcentage
        return (int) round(($filledFields / count($requiredFields)) * 100);
    }

    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        try {
            $user = auth()->user();

            if ($user->is_professional) {
                Log::info('updateProfile (pro) - données reçues', [
                    'user_id' => $user->id,
                    'payload' => $request->all(),
                ]);

                $profile = $user->professionalProfile ?? new ProfessionalProfile();

                // Remplir les champs simples
                $profile->fill($request->only([
                    'title', 'hourly_rate', 'bio', 'phone',
                    'address', 'city', 'country', 'profession'
                ]));

                // Gérer les champs de type tableau
	                $arrayFields = ['skills', 'softwares', 'languages', 'services_offered', 'social_links'];

	                Log::info('updateProfile (pro) - champs tableau bruts', [
	                    'skills' => $request->input('skills'),
	                    'softwares' => $request->input('softwares'),
	                    'languages' => $request->input('languages'),
	                    'services_offered' => $request->input('services_offered'),
	                    'social_links' => $request->input('social_links'),
	                ]);

                foreach ($arrayFields as $field) {
                    if ($request->has($field)) {
                        $value = $request->input($field);

                        if (is_string($value)) {
                            try {
                                $decoded = json_decode($value, true);
                                $profile->$field = (json_last_error() === JSON_ERROR_NONE) ? $decoded : [];
                            } catch (\Exception $e) {
                                $profile->$field = [];
                            }
                        } elseif (is_array($value)) {
                            $profile->$field = $value;
                        } else {
                            $profile->$field = [];
                        }
                    }
                }

                // Sauvegarder le profil
                $profile->save();

                // Mettre à jour les infos utilisateur de base
                $user->update($request->only(['first_name', 'last_name', 'email']));

                return response()->json([
                    'message' => 'Profil mis à jour avec succès',
                    'profile' => $profile->fresh()
                ]);

            } else {
                // Gestion du profil client (similaire mais sans les champs professionnels)
                $profile = $user->clientProfile ?? new ClientProfile();
                $profile->fill($request->validated());
                $profile->save();

                return response()->json([
                    'message' => 'Profil client mis à jour',
                    'profile' => $profile
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Erreur mise à jour profil', [
                'message' => $e->getMessage(),
                'user_id' => optional(auth()->user())->id,
                'payload' => $request->all(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Erreur serveur',
                'error' => $e->getMessage(),
                'exception' => get_class($e),
                'trace' => $e->getTrace(),
            ], 500);
        }
    }

    public function deleteProfile(): JsonResponse
    {
        try {
            $user = auth()->user();
            if ($user && method_exists($user, 'delete')) {
                $user->delete(); // La suppression en cascade est gérée par la contrainte foreign key dans les migrations
                return response()->json(['message' => 'Compte supprimé avec succès.']);
            } else {
                Log::warning('Impossible de supprimer le compte: utilisateur non trouvé ou méthode delete non disponible');
                return response()->json(['message' => 'Impossible de supprimer le compte.'], 400);
            }
        } catch (\Exception $e) {
            Log::error('Erreur lors de la suppression du compte utilisateur ID ' . auth()->id() . ': ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la suppression du compte.'], 500);
        }
    }

    /**
     * Mettre à jour le profil client avec des données JSON
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateClientProfileJSON(Request $request): JsonResponse
    {
        try {
            Log::info('Début de la mise à jour du profil client avec JSON');
            Log::info('Données JSON reçues:', $request->all());

            $profile = ClientProfile::where('user_id', auth()->id())->firstOrFail();

            // Préparer les données
            $data = $request->all();

            // Gérer la date de naissance
            if (array_key_exists('birth_date', $data)) {
                $data['birth_date'] = !empty($data['birth_date'])
                    ? Carbon::parse($data['birth_date'])->format('Y-m-d')
                    : null;
            }

            // Calcul du pourcentage
            $data['completion_percentage'] = $this->calculateProfileCompletionPercentage($profile, $data);

            Log::info('Données à mettre à jour:', $data);
            $profile->update($data);

            return response()->json([
                'message' => 'Profil client mis à jour avec succès.',
                'profile' => $profile
            ], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise à jour: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erreur lors de la mise à jour du profil.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mettre à jour le profil client avec avatar
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateClientProfileWithAvatar(Request $request): JsonResponse
{
    try {
        Log::info('Début de la mise à jour du profil client avec avatar');
        Log::info('Données reçues:', $request->except('avatar'));

        // Validation des données
        $request->validate([
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg|max:10240',
            'profile_data' => 'nullable|string'
        ]);

        // Récupérer ou créer le profil client
        $profile = ClientProfile::firstOrNew(['user_id' => auth()->id()]);

        if (!$profile->exists) {
            Log::info('Aucun profil client trouvé, création d\'un nouveau profil');
            $user = auth()->user();
            $profile->fill([
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'completion_percentage' => 20,
            ])->save();
            Log::info('Nouveau profil client créé avec ID: ' . $profile->id);
        } else {
            Log::info('Profil client existant trouvé avec ID: ' . $profile->id);
        }

        // Traiter l'upload de l'avatar
        $avatarPath = null;
        if ($request->hasFile('avatar')) {
            Log::info('Avatar présent dans la requête');
            $avatar = $request->file('avatar');
            $filename = time() . '_' . $avatar->getClientOriginalName();
            $path = $avatar->storeAs('avatars', $filename, 'public');
            $avatarPath = '/storage/' . $path;
            Log::info('Avatar enregistré: ' . $avatarPath);
        }

        // Traiter les données du profil
        $updateData = [];

        if ($avatarPath) {
            $updateData['avatar'] = $avatarPath;
        }

        if ($request->has('profile_data')) {
            $profileData = json_decode($request->input('profile_data'), true) ?? [];
            Log::info('Données du profil décodées depuis JSON:', $profileData);

            // Nettoyer les données avant la mise à jour
            foreach ($profileData as $key => $value) {
                // Gestion spéciale pour birth_date
                if ($key === 'birth_date' && empty($value)) {
                    $profileData[$key] = null;
                }

                // Gestion des champs vides
                if (is_string($value) && trim($value) === '') {
                    $profileData[$key] = null;
                }
            }

            $updateData = array_merge($updateData, $profileData);

            // Calcul du pourcentage de complétion
            $updateData['completion_percentage'] = $this->calculateProfileCompletionPercentage($profile, $profileData);
            Log::info('Pourcentage de complétion calculé: ' . $updateData['completion_percentage']);
        }

        if (!empty($updateData)) {
            Log::info('Données à mettre à jour:', $updateData);
            $profile->update($updateData);
            Log::info('Profil client mis à jour avec succès');
        } else {
            Log::warning('Aucune donnée de profil ni avatar n\'a été envoyé');
        }

        return response()->json([
            'message' => 'Profil client mis à jour avec succès.',
            'profile' => $profile->fresh()
        ], 200);
    } catch (\Exception $e) {
        Log::error('Erreur lors de la mise à jour du profil client: ' . $e->getMessage());
        Log::error('Trace: ' . $e->getTraceAsString());
        return response()->json([
            'message' => 'Une erreur est survenue lors de la mise à jour du profil client.',
            'error' => $e->getMessage()
        ], 500);
    }
}
    /**
     * Mettre à jour la disponibilité et le délai de réponse du profil du professionnel.
     *
     * @param  \App\Http\Requests\UpdateAvailabilityRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateAvailability(UpdateAvailabilityRequest $request): JsonResponse
    {
        // try {
            $user = $request->user();
            $profile = $user->freelanceProfile;

            if (!$profile) {
                return response()->json(['message' => 'Profil freelance non trouvé.'], 404);
            }

            $profile->availability_status = $request->availability_status;
            $profile->estimated_response_time = $request->estimated_response_time; // Sera null si availability_status est 'available'
            $profile->save();

            return response()->json([
                'message' => 'Disponibilité mise à jour avec succès.',
                'availability_status' => $profile->availability_status,
                'estimated_response_time' => $profile->estimated_response_time,
            ], 200);

        // } catch (\Exception $e) {
        //     Log::error('Erreur lors de la mise à jour de la disponibilité du profil: ' . $e->getMessage());
        //     return response()->json(['message' => 'Erreur serveur lors de la mise à jour de la disponibilité.'], 500);
        // }
    }

    /**
     * Compléter le profil utilisateur (client ou professionnel)
     *
     * @param Request $request
     * @return JsonResponse
     */
    /**
     * Upload an avatar for the authenticated user's profile
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function uploadAvatar(Request $request): JsonResponse
    {

        try {
            Log::info('Starting avatar upload process');

            // Validate the request
            $request->validate([
                'avatar' => 'required|file|mimes:jpeg,png,jpg,gif|max:10240',
            ]);

            $user = auth()->user();
            Log::info('Authenticated user ID: ' . $user->id . ', Type: ' . ($user->is_professional ? 'Professional' : 'Client'));

            // Get the appropriate profile
            if ($user->is_professional) {
                $profile = $user->professionalProfile;
                if (!$profile) {
                    return response()->json(['message' => 'Professional profile not found.'], 404);
                }
            } else {
                $profile = $user->clientProfile;
                if (!$profile) {
                    return response()->json(['message' => 'Client profile not found.'], 404);
                }
            }

            // Delete old avatar if exists
            if ($profile->avatar && strpos($profile->avatar, '/storage/') === 0) {
                $oldPath = str_replace('/storage/', '', $profile->avatar);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                    Log::info('Old avatar deleted: ' . $oldPath);
                }
            }

            // Upload new avatar
            $avatar = $request->file('avatar');
            $filename = time() . '_' . $avatar->getClientOriginalName();
            $path = $avatar->storeAs('avatars', $filename, 'public');
            $avatarPath = '/storage/' . $path;
            Log::info('New avatar saved: ' . $avatarPath);

            // Update profile
            $profile->avatar = $avatarPath;
            $profile->save();
            Log::info('Profile updated with new avatar');

            return response()->json([
                'message' => 'Avatar uploaded successfully.',
                'avatar_url' => $avatarPath
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error uploading avatar: ' . $e->getMessage());
            return response()->json(['message' => 'Error uploading avatar: ' . $e->getMessage()], 500);
        }
    }


    public function uploadCover(Request $request): JsonResponse
    {

        try {
            Log::info('Starting cover upload process');

            // Validate the request
            $request->validate([
                'cover_photo' => 'required|file|mimes:jpeg,png,jpg,gif|max:10240',
            ]);

            $user = auth()->user();
            Log::info('Authenticated user ID: ' . $user->id . ', Type: ' . ($user->is_professional ? 'Professional' : 'Client'));

            // Get the appropriate profile
            if ($user->is_professional) {
                $profile = $user->professionalProfile;
                if (!$profile) {
                    return response()->json(['message' => 'Professional profile not found.'], 404);
                }
            } else {
                $profile = $user->clientProfile;
                if (!$profile) {
                    return response()->json(['message' => 'Client profile not found.'], 404);
                }
            }

            // Delete old avatar if exists
            if ($profile->cover_photo && strpos($profile->cover_photo, '/storage/') === 0) {
                $oldPath = str_replace('/storage/', '', $profile->cover_photo);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                    Log::info('Old avatar deleted: ' . $oldPath);
                }
            }

            // Upload new avatar
            $cover_photo = $request->file('cover_photo');
            $filename = time() . '_' . $cover_photo->getClientOriginalName();
            $path = $cover_photo->storeAs('cover_photo', $filename, 'public');
            $cover_photoPath = '/storage/' . $path;
            Log::info('New cover photo saved: ' . $cover_photoPath);

            // Update profile
            $profile->cover_photo = $cover_photoPath;
            $profile->save();
            Log::info('Profile updated with new cover photo');

            return response()->json([
                'message' => 'Cover photo uploaded successfully.',
                'cover_url' => $cover_photoPath
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error uploading cover: ' . $e->getMessage());
            return response()->json(['message' => 'Error uploading cover: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Delete the avatar of the authenticated user's profile
     *
     * @return JsonResponse
     */
    public function deleteAvatar(): JsonResponse
    {
        try {
            Log::info('Starting avatar deletion process');

            $user = auth()->user();
            Log::info('Authenticated user ID: ' . $user->id . ', Type: ' . ($user->is_professional ? 'Professional' : 'Client'));

            // Get the appropriate profile
            if ($user->is_professional) {
                $profile = $user->professionalProfile;
                if (!$profile) {
                    return response()->json(['message' => 'Professional profile not found.'], 404);
                }
            } else {
                $profile = $user->clientProfile;
                if (!$profile) {
                    return response()->json(['message' => 'Client profile not found.'], 404);
                }
            }

            // Delete avatar file if exists
            if ($profile->avatar && strpos($profile->avatar, '/storage/') === 0) {
                $oldPath = str_replace('/storage/', '', $profile->avatar);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                    Log::info('Avatar deleted from storage: ' . $oldPath);
                }
            }

            // Update profile to remove avatar reference
            $profile->avatar = null;
            $profile->save();
            Log::info('Avatar reference removed from profile');

            return response()->json([
                'message' => 'Avatar deleted successfully.'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error deleting avatar: ' . $e->getMessage());
            return response()->json(['message' => 'Error deleting avatar: ' . $e->getMessage()], 500);
        }
    }

    public function completeProfile(Request $request): JsonResponse
    {
        try {
            Log::info('Début de la complétion du profil utilisateur');
            Log::info('Données reçues:', $request->except(['avatar', 'portfolio_items']));

            $user = auth()->user();
            Log::info('Utilisateur authentifié ID: ' . $user->id . ', Type: ' . ($user->is_professional ? 'Professionnel' : 'Client'));

            // Déterminer le type de profil à mettre à jour
            if ($user->is_professional) {
                $profile = ProfessionalProfile::where('user_id', $user->id)->first();

                // Si aucun profil n'est trouvé, créer un nouveau profil professionnel
                if (!$profile) {
                    Log::info('Aucun profil professionnel trouvé, création d\'un nouveau profil');
                    $profile = ProfessionalProfile::create([
                        'user_id' => $user->id,
                        'first_name' => $user->first_name ?? $request->input('first_name', ''),
                        'last_name' => $user->last_name ?? $request->input('last_name', ''),
                        'email' => $user->email,
                        'completion_percentage' => 20, // Pourcentage initial de complétion
                    ]);
                    Log::info('Nouveau profil professionnel créé avec ID: ' . $profile->id);
                } else {
                    Log::info('Profil professionnel existant trouvé avec ID: ' . $profile->id);
                }
            } else {
                $profile = ClientProfile::where('user_id', $user->id)->first();

                // Si aucun profil n'est trouvé, créer un nouveau profil client
                if (!$profile) {
                    Log::info('Aucun profil client trouvé, création d\'un nouveau profil');
                    $profile = ClientProfile::create([
                        'user_id' => $user->id,
                        'first_name' => $user->first_name ?? $request->input('first_name', ''),
                        'last_name' => $user->last_name ?? $request->input('last_name', ''),
                        'email' => $user->email,
                        'completion_percentage' => 20, // Pourcentage initial de complétion
                    ]);
                    Log::info('Nouveau profil client créé avec ID: ' . $profile->id);
                } else {
                    Log::info('Profil client existant trouvé avec ID: ' . $profile->id);
                }
            }

            // Traiter l'upload de l'avatar si présent
            if ($request->hasFile('avatar')) {
                Log::info('Avatar présent dans la requête');
                $avatar = $request->file('avatar');
                $filename = time() . '_' . $avatar->getClientOriginalName();
                $path = $avatar->storeAs('avatars', $filename, 'public');
                $avatarPath = '/storage/' . $path;
                $request->merge(['avatar' => $avatarPath]);
                Log::info('Avatar enregistré: ' . $avatarPath);
            }

	            // Traiter les compétences si présentes
	            if ($request->has('skills')) {
	                $skills = $request->input('skills');
	                if (is_string($skills)) {
	                    try {
	                        $skills = json_decode($skills, true);
	                        $request->merge(['skills' => $skills]);
	                        Log::info('Compétences décodées depuis JSON:', $skills ?: []);
	                    } catch (\Exception $e) {
	                        Log::warning('Erreur lors du décodage des compétences: ' . $e->getMessage());
	                    }
	                }
	            }

	            // Traiter les logiciels si présents
	            if ($request->has('softwares')) {
	                $softwares = $request->input('softwares');
	                if (is_string($softwares)) {
	                    try {
	                        $softwares = json_decode($softwares, true);
	                        $request->merge(['softwares' => $softwares]);
	                        Log::info('Logiciels décodés depuis JSON:', $softwares ?: []);
	                    } catch (\Exception $e) {
	                        Log::warning('Erreur lors du décodage des logiciels: ' . $e->getMessage());
	                    }
	                }
	            }

	            // Traiter les liens sociaux si présents
            if ($request->has('social_links')) {
                $socialLinks = $request->input('social_links');
                if (is_string($socialLinks)) {
                    try {
                        $socialLinks = json_decode($socialLinks, true);
                        $request->merge(['social_links' => $socialLinks]);
                        Log::info('Liens sociaux décodés depuis JSON:', $socialLinks ?: []);
                    } catch (\Exception $e) {
                        Log::warning('Erreur lors du décodage des liens sociaux: ' . $e->getMessage());
                    }
                }
            }

            // Traiter les éléments du portfolio si présents
            if ($request->hasFile('portfolio_items')) {
                $portfolioItems = $request->file('portfolio_items');
                $portfolioData = [];

                foreach ($portfolioItems as $index => $file) {
                    $filename = time() . '_' . $file->getClientOriginalName();
                    $path = $file->storeAs('portfolio', $filename, 'public');
                    $portfolioData[] = [
                        'id' => uniqid(),
                        'path' => '/storage/' . $path,
                        'name' => $file->getClientOriginalName(),
                        'type' => $file->getMimeType(),
                        'created_at' => now()->toDateTimeString(),
                    ];
                }

                // Fusionner avec le portfolio existant
                $existingPortfolio = $profile->portfolio ?? [];
                if (is_string($existingPortfolio)) {
                    $existingPortfolio = json_decode($existingPortfolio, true) ?? [];
                }

                $mergedPortfolio = array_merge($existingPortfolio, $portfolioData);
                $request->merge(['portfolio' => $mergedPortfolio]);
                Log::info('Portfolio mis à jour avec ' . count($portfolioData) . ' nouveaux éléments');
            }

            // Préparer les données à mettre à jour
            $dataToUpdate = $request->except(['_method', '_token', 'is_completion', 'portfolio_items']);

            // Calculer le pourcentage de complétion du profil
            $completionPercentage = $this->calculateProfileCompletionPercentage($profile, $dataToUpdate);
            $dataToUpdate['completion_percentage'] = $completionPercentage;
            Log::info('Pourcentage de complétion calculé: ' . $completionPercentage);

            // Mettre à jour le profil
            Log::info('Données à mettre à jour:', $dataToUpdate);
            $profile->update($dataToUpdate);

            // Mettre à jour les informations de base de l'utilisateur si fournies
            if ($request->has('first_name') || $request->has('last_name') || $request->has('email')) {
                $user->update($request->only(['first_name', 'last_name', 'email']));
                Log::info('Informations de base de l\'utilisateur mises à jour');
            }

            // Marquer que le profil a été complété
            $user->profile_completed = true;
            $user->save();
            Log::info('Profil marqué comme complété');

            // Retourner une réponse de succès
            return response()->json([
                'message' => 'Profil complété avec succès.',
                'profile' => $profile,
                'completion_percentage' => $completionPercentage
            ], 200);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la complétion du profil: ' . $e->getMessage());
            Log::error('Trace: ' . $e->getTraceAsString());
            return response()->json([
                'message' => 'Une erreur est survenue lors de la complétion du profil.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
