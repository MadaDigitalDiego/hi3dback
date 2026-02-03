<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Models\FreelanceProfile;
use App\Models\CompanyProfile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfileCompletionController extends Controller
{
    /**
     * Récupère les données de profil complétées et le statut de complétion.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function getCompletionData(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $profile = null;
            $profileType = '';

            if ($user->is_professional) {
                $profile = $user->freelanceProfile;
                $profileType = 'freelance';
            } else {
                $profile = $user->companyProfile;
                $profileType = 'company';
            }

            if (!$profile) {
                // Si le profil n'existe pas, créons-en un nouveau
                if ($user->is_professional) {
                    $profile = new FreelanceProfile();
                    $profile->user_id = $user->id;
                    $profile->first_name = $user->first_name;
                    $profile->last_name = $user->last_name;
                    $profile->save();
                    $profileType = 'freelance';
                } else {
                    $profile = new CompanyProfile();
                    $profile->user_id = $user->id;
                    $profile->first_name = $user->first_name;
                    $profile->last_name = $user->last_name;
                    $profile->save();
                    $profileType = 'company';
                }
            }

            // Calculer le pourcentage de complétion
            $completionPercentage = $this->calculateCompletionPercentage($profile, $profileType);

            // Mettre à jour le pourcentage de complétion dans le profil
            $profile->completion_percentage = $completionPercentage;
            $profile->save();

            // Ajouter l'email de l'utilisateur aux données du profil
            $profileData = $profile->toArray();
            $profileData['email'] = $user->email;

            return response()->json([
                'profile_type' => $profileType,
                'profile_data' => $profileData,
                'completion_percentage' => $completionPercentage,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des données de complétion du profil: ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la récupération des données de complétion du profil.'], 500);
        }
    }


    /**
     * Met à jour une étape spécifique du profil.
     *
     * @param  Request  $request
     * @param  string  $step  Le nom de l'étape (personal, kyc, experience, etc.)
     * @return JsonResponse
     */
    public function updateStep(Request $request, string $step): JsonResponse
    {
        try {
            $user = $request->user();
            $profile = null;

            if ($user->is_professional) {
                $profile = $user->freelanceProfile()->firstOrNew(['user_id' => $user->id]); // Créer si non existant
            } else {
                $profile = $user->companyProfile()->firstOrNew(['user_id' => $user->id]);   // Créer si non existant
            }

            if (!$profile) {
                return response()->json(['message' => 'Erreur lors de la création/récupération du profil.'], 500);
            }

            $validationRules = $this->getValidationRulesForStep($step, $user->is_professional);
            $validator = Validator::make($request->all(), $validationRules);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422); // 422 Unprocessable Entity
            }

            $validatedData = $validator->validated();

            // Traitement spécifique pour chaque étape et sauvegarde des données
            switch ($step) {
                case 'personal':
                    $profile->fill(Arr::only($validatedData, ['first_name', 'last_name', 'phone', 'address', 'city', 'country']));
                    break;
                case 'kyc':
                    // Gérer l'upload de fichiers ici (selfie, pièce d'identité, etc.)
                    // Exemple simplifié pour un champ 'identity_document'
                    if ($request->hasFile('identity_document')) {
                        $path = $request->file('identity_document')->store('kyc_documents', 'public'); // Stockage dans storage/app/public/kyc_documents
                        $profile->identity_document_path = $path;
                    }
                    $profile->fill(Arr::only($validatedData, ['identity_document_number'])); // Autres champs KYC
                    break;
                case 'experience':
                    $profile->experience = $validatedData['experience'] ?? null;
                    $profile->portfolio_url = $validatedData['portfolio_url'] ?? null;
                    break;
                case 'education':
                    $profile->education = $validatedData['education'] ?? null;
                    $profile->diplomas = $validatedData['diplomas'] ?? null;
                    break;
                case 'skills':
                    $profile->skills = $validatedData['skills'] ?? null; // Exemple pour un champ 'skills' (JSON)
                    break;
                case 'languages':
                    $profile->languages = $validatedData['languages'] ?? null; // Exemple pour un champ 'languages' (JSON)
                    break;
                case 'availability':
                    $profile->availability_status = $validatedData['availability_status'] ?? null;
                    $profile->availability_details = $validatedData['availability_details'] ?? null;
                    $profile->estimated_response_time = $validatedData['estimated_response_time'] ?? null;
                    // Enregistre les détails de disponibilité
                    break;
                case 'services':
                    $profile->services_offered = $validatedData['services_offered'] ?? null; // Exemple pour un champ 'services_offered' (JSON)
                    $profile->hourly_rate = $validatedData['hourly_rate'] ?? null;
                    break;
                default:
                    return response()->json(['message' => 'Étape invalide.'], 400); // 400 Bad Request
            }

            $profile->user_id = $user->id;
            $profile->save();

             // Recalculer et mettre à jour le pourcentage de complétion après chaque étape
            $profileType = $user->is_professional ? 'freelance' : 'company';
            $completionPercentage = $this->calculateCompletionPercentage($profile, $profileType);
            $profile->completion_percentage = $completionPercentage;
            $profile->save();


            return response()->json([
                'message' => 'Étape mise à jour avec succès.',
                'completion_percentage' => $completionPercentage
            ], 200);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise à jour de l\'étape du profil: ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la mise à jour de l\'étape du profil. Veuillez réessayer plus tard.'], 500);
        }
    }


    /**
     * Détermine les règles de validation pour chaque étape.
     *
     * @param  string  $step
     * @param  bool  $isProfessional
     * @return array
     */
    private function getValidationRulesForStep(string $step, bool $isProfessional): array
    {
        $rules = [];

        switch ($step) {
            case 'personal':
                $rules = [
                    'first_name' => 'required|string|max:255',
                    'last_name' => 'required|string|max:255',
                    'phone' => 'required|string|max:20',
                    'address' => 'required|string|max:255',
                    'city' => 'required|string|max:255',
                    'country' => 'required|string|max:255',
                ];
                break;
            case 'kyc':
                $rules = [
                    'identity_document' => 'nullable|file|mimes:pdf,jpeg,png,jpg|max:20480', // Exemple d'upload de fichier
                    'identity_document_number' => 'nullable|string|max:50', // Exemple d'autre champ KYC
                ];
                break;
            case 'experience':
                $rules = [
                    'experience' => 'nullable|integer|min:0',
                    'portfolio_url' => 'nullable|url|max:255',
                ];
                break;
            case 'education':
                $rules = [
                    'education' => 'nullable|string',
                    'diplomas' => 'nullable|string',
                ];
                break;
            case 'skills':
                $rules = [
                    'skills' => 'nullable|array', // Validation pour un tableau de compétences
                    'skills.*' => 'string|max:255', // Chaque compétence doit être une chaîne de caractères
                ];
                break;
            case 'languages':
                $rules = [
                    'languages' => 'nullable|array', // Validation pour un tableau de langues (JSON)
                    // Vous pouvez ajouter des règles plus spécifiques pour la structure du JSON si nécessaire
                ];
                break;
            case 'availability':
                $rules = [
                    'availability_status' => 'nullable|string|in:available,unavailable', // Statut de disponibilité available,unavailable
                    'availability_details' => 'nullable|json', // Détails de disponibilité au format JSON
                    'estimated_response_time'=>'nullable|date_format:Y-m-d H:i:s'
                ];
                break;
            case 'services':
                $rules = [
                    'services_offered' => 'nullable|array', // Validation pour un tableau de services (JSON)
                    'hourly_rate' => 'nullable|numeric|min:0',
                ];
                break;
        }

        // Ajouter des règles conditionnelles si nécessaire en fonction de $isProfessional
        if (!$isProfessional && $step === 'kyc') {
            // Exemple de règle conditionnelle pour les entreprises/particuliers uniquement
            $rules['company_registration_number'] = 'required|string|max:100';
        }

        return $rules;
    }

    /**
     * Calcule le pourcentage de complétion du profil.
     *
     * @param  Model  $profile
     * @param  string $profileType
     * @return int
     */
    private function calculateCompletionPercentage($profile, string $profileType): int
    {
        // Pour ProfessionalProfile (freelances), nous utilisons directement les champs du modèle
        if ($profileType === 'freelance' || $profileType === 'professional') {
            return $this->calculateProfessionalProfileCompletion($profile);
        }
        
        // Pour CompanyProfile
        return $this->calculateCompanyProfileCompletion($profile);
    }
    
    /**
     * Calcule le pourcentage de complétion pour un profil professionnel.
     * Tous les champs importants sont pris en compte.
     *
     * @param ProfessionalProfile $profile
     * @return int
     */
    private function calculateProfessionalProfileCompletion($profile): int
    {
        $fields = [
            // Informations personnelles de base
            'first_name' => 10,   // 10%
            'last_name' => 5,     // 5%
            'phone' => 5,         // 5%
            'address' => 5,       // 5%
            'city' => 5,          // 5%
            'country' => 5,       // 5%
            
            // Photo de profil
            'avatar' => 5,        // 5%
            
            // Bio et présentation
            'bio' => 5,           // 5%
            'title' => 5,         // 5%
            'description' => 5,   // 5%
            
            // Compétences
            'skills' => 5,        // 5%
            'softwares' => 5,     // 5%
            
            // Expérience professionnelle
            'years_of_experience' => 5, // 5%
            'hourly_rate' => 5,         // 5%
            
            // Services et disponibilité
            'services_offered' => 5,    // 5%
            'availability_status' => 5, // 5%
            
            // Langues
            'languages' => 5,           // 5%
            
            // Portfolio
            'portfolio' => 5,           // 5%
        ];
        
        $totalWeight = array_sum($fields);
        $filledWeight = 0;
        
        foreach ($fields as $field => $weight) {
            $value = $profile->$field ?? null;
            
            // Vérifier si le champ est remplisignificatif
            if ($this->isFieldFilled($value)) {
                $filledWeight += $weight;
            }
        }
        
        // Calculer le pourcentage avec un minimum de 0 et un maximum de 100
        $percentage = min(100, max(0, round(($filledWeight / $totalWeight) * 100)));
        
        Log::channel('profile')->info('Completion percentage calculated', [
            'profile_id' => $profile->id ?? null,
            'filled_weight' => $filledWeight,
            'total_weight' => $totalWeight,
            'percentage' => $percentage,
            'filled_fields' => array_keys(array_filter(array_map(function($field) use ($profile) {
                $value = $profile->$field ?? null;
                return $this->isFieldFilled($value) ? $field : null;
            }, array_keys($fields))))
        ]);
        
        return $percentage;
    }
    
    /**
     * Vérifie si un champ est considéré comme "rempli".
     *
     * @param mixed $value
     * @return bool
     */
    private function isFieldFilled($value): bool
    {
        if ($value === null || $value === '' || $value === []) {
            return false;
        }
        
        // Pour les tableaux JSON, vérifier s'il y a au moins un élément
        if (is_array($value)) {
            return count($value) > 0;
        }
        
        return true;
    }
    
    /**
     * Calcule le pourcentage de complétion pour un profil d'entreprise.
     *
     * @param ClientProfile $profile
     * @return int
     */
    private function calculateCompanyProfileCompletion($profile): int
    {
        $fields = [
            'company_name' => 15,
            'company_size' => 10,
            'industry' => 10,
            'phone' => 10,
            'address' => 10,
            'city' => 10,
            'country' => 10,
            'description' => 10,
            'registration_number' => 15,
        ];
        
        $totalWeight = array_sum($fields);
        $filledWeight = 0;
        
        foreach ($fields as $field => $weight) {
            $value = $profile->$field ?? null;
            if ($this->isFieldFilled($value)) {
                $filledWeight += $weight;
            }
        }
        
        return min(100, round(($filledWeight / $totalWeight) * 100));
    }


     /**
     * Récupère toutes les freelanceProfiles.
     *
     * @return JsonResponse
     */
    public function getAllFreelanceProfiles(): JsonResponse
    {
        try {
            // Récupérer toutes les freelanceProfiles avec les utilisateurs associés
            $freelanceProfiles = FreelanceProfile::with('user')->get();

            // Retourner les données en JSON
            return response()->json([
                'success' => true,
                'data' => $freelanceProfiles,
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération de tous les profils freelance: ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la récupération des profils freelance.'], 500);
        }
    }

    /**
     * Complète le profil utilisateur en une seule requête.
     * Cette méthode permet de mettre à jour toutes les informations du profil en une fois.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function completeProfile(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $profile = null;

            if ($user->is_professional) {
                $profile = $user->freelanceProfile()->firstOrNew(['user_id' => $user->id]);
            } else {
                $profile = $user->companyProfile()->firstOrNew(['user_id' => $user->id]);
            }

            if (!$profile) {
                return response()->json(['message' => 'Erreur lors de la création/récupération du profil.'], 500);
            }

            // Validation des données
            $validator = Validator::make($request->all(), [
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'phone' => 'required|string|max:20',
                'address' => 'required|string|max:255',
                'city' => 'required|string|max:255',
                'country' => 'required|string|max:255',
                'bio' => 'nullable|string',
                'skills' => 'nullable|array',
                'skills.*' => 'string|max:255',
                'languages' => 'nullable|array',
                'languages.*' => 'string|max:255',
                'services_offered' => 'nullable|array',
                'services_offered.*' => 'string|max:255',
                'hourly_rate' => 'nullable|numeric|min:0',
                'title' => 'nullable|string|max:255',
                'experience' => 'nullable|integer|min:0',
                'availability_status' => 'nullable|string|in:available,unavailable',
                'profile_picture' => 'nullable|file|mimes:jpeg,png,jpg,gif|max:20480',
                'portfolio.*' => 'nullable|file|mimes:jpeg,png,jpg,gif,pdf|max:20480',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // Mise à jour des champs de base
            $profile->first_name = $request->input('first_name');
            $profile->last_name = $request->input('last_name');
            $profile->phone = $request->input('phone');
            $profile->address = $request->input('address');
            $profile->city = $request->input('city');
            $profile->country = $request->input('country');
            $profile->bio = $request->input('bio');

            // Mise à jour des champs spécifiques au profil professionnel
            if ($user->is_professional) {
                if ($request->has('title')) {
                    $profile->title = $request->input('title');
                }
                if ($request->has('experience')) {
                    $profile->experience = $request->input('experience');
                }
                if ($request->has('hourly_rate')) {
                    $profile->hourly_rate = $request->input('hourly_rate');
                }
                if ($request->has('availability_status')) {
                    $profile->availability_status = $request->input('availability_status');
                }
            }

            // Mise à jour des compétences si fournies
            if ($request->has('skills')) {
                // Vérifier si les compétences sont envoyées sous forme de tableau ou de JSON
                $skills = $request->input('skills');
                if (is_array($skills)) {
                    $profile->skills = $skills;
                } elseif (is_string($skills) && $this->isJson($skills)) {
                    $profile->skills = json_decode($skills, true);
                }
            }

            // Mise à jour des langues si fournies
            if ($request->has('languages')) {
                // Vérifier si les langues sont envoyées sous forme de tableau ou de JSON
                $languages = $request->input('languages');
                if (is_array($languages)) {
                    $profile->languages = $languages;
                } elseif (is_string($languages) && $this->isJson($languages)) {
                    $profile->languages = json_decode($languages, true);
                }
            }

            // Mise à jour des services offerts si fournis
            if ($request->has('services_offered')) {
                // Vérifier si les services sont envoyés sous forme de tableau ou de JSON
                $services = $request->input('services_offered');
                if (is_array($services)) {
                    $profile->services_offered = $services;
                } elseif (is_string($services) && $this->isJson($services)) {
                    $profile->services_offered = json_decode($services, true);
                }
            }

            // Gestion de l'upload de la photo de profil
            if ($request->hasFile('profile_picture')) {
                $path = $request->file('profile_picture')->store('profile_pictures', 'public');
                $profile->avatar = '/storage/' . $path;
                Log::info('Avatar enregistré avec le chemin: /storage/' . $path);
            }

            // Gestion des fichiers du portfolio (pour les professionnels)
            if ($user->is_professional && $request->hasFile('portfolio')) {
                $portfolioFiles = [];
                foreach ($request->file('portfolio') as $file) {
                    $path = $file->store('portfolio', 'public');
                    $portfolioFiles[] = [
                        'path' => $path,
                        'name' => $file->getClientOriginalName(),
                        'type' => $file->getClientMimeType(),
                    ];
                }
                $profile->portfolio = $portfolioFiles;
            }

            // Sauvegarde du profil
            $profile->user_id = $user->id;
            $profile->save();

            // Calcul du pourcentage de complétion
            $profileType = $user->is_professional ? 'freelance' : 'company';
            $completionPercentage = $this->calculateCompletionPercentage($profile, $profileType);
            $profile->completion_percentage = $completionPercentage;
            $profile->save();

            // Ajouter l'email de l'utilisateur aux données du profil
            $profileData = $profile->toArray();
            $profileData['email'] = $user->email;

            return response()->json([
                'message' => 'Profil complété avec succès.',
                'profile' => $profileData,
                'completion_percentage' => $completionPercentage
            ], 200);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la complétion du profil: ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la complétion du profil: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Télécharge un fichier de portfolio.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function uploadPortfolioItem(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user->is_professional) {
                return response()->json(['message' => 'Seuls les professionnels peuvent télécharger des éléments de portfolio.'], 403);
            }

            $profile = $user->freelanceProfile()->firstOrNew(['user_id' => $user->id]);

            // Validation du fichier
            $validator = Validator::make($request->all(), [
                'file' => 'required|file|mimes:jpeg,png,jpg,gif,pdf|max:20480',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // Téléchargement du fichier
            $file = $request->file('file');
            $path = $file->store('portfolio', 'public');

            // Ajouter le fichier au portfolio existant
            $portfolio = $profile->portfolio ?? [];
            $portfolio[] = [
                'path' => $path,
                'name' => $file->getClientOriginalName(),
                'type' => $file->getClientMimeType(),
            ];

            $profile->portfolio = $portfolio;
            $profile->save();

            return response()->json([
                'message' => 'Fichier de portfolio téléchargé avec succès.',
                'file' => [
                    'path' => $path,
                    'name' => $file->getClientOriginalName(),
                    'type' => $file->getClientMimeType(),
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Erreur lors du téléchargement du fichier de portfolio: ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors du téléchargement du fichier de portfolio.'], 500);
        }
    }

    /**
     * Supprime un élément du portfolio.
     *
     * @param Request $request
     * @param string $path
     * @return JsonResponse
     */
    public function deletePortfolioItem(Request $request, string $path): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user->is_professional) {
                return response()->json(['message' => 'Seuls les professionnels peuvent supprimer des éléments de portfolio.'], 403);
            }

            $profile = $user->freelanceProfile;

            if (!$profile) {
                return response()->json(['message' => 'Profil non trouvé.'], 404);
            }

            $portfolio = $profile->portfolio ?? [];
            $newPortfolio = [];
            $found = false;

            foreach ($portfolio as $item) {
                if ($item['path'] === $path) {
                    $found = true;
                    // Supprimer le fichier du stockage
                    Storage::disk('public')->delete($path);
                } else {
                    $newPortfolio[] = $item;
                }
            }

            if (!$found) {
                return response()->json(['message' => 'Élément de portfolio non trouvé.'], 404);
            }

            $profile->portfolio = $newPortfolio;
            $profile->save();

            return response()->json([
                'message' => 'Élément de portfolio supprimé avec succès.'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la suppression de l\'\u00e9lément de portfolio: ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la suppression de l\'\u00e9lément de portfolio.'], 500);
        }
    }

    /**
     * Télécharge une photo de profil.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function uploadAvatar(Request $request): JsonResponse
    {
        try {
            Log::info('Début de la méthode uploadAvatar');
            $user = $request->user();
            Log::info('Utilisateur authentifié: ' . $user->id . ' - ' . $user->email);

            $profile = null;
            if ($user->is_professional) {
                Log::info('Utilisateur professionnel, récupération du profil freelance');
                $profile = $user->freelanceProfile()->firstOrNew(['user_id' => $user->id]);
            } else {
                Log::info('Utilisateur client, récupération du profil entreprise');
                $profile = $user->companyProfile()->firstOrNew(['user_id' => $user->id]);
            }

            Log::info('Profil récupéré: ' . ($profile->id ?? 'nouveau profil'));

            if (!$profile) {
                return response()->json(['message' => 'Erreur lors de la création/récupération du profil.'], 500);
            }

            // Vérifier si un fichier a été envoyé
            if (!$request->hasFile('profile_picture')) {
                Log::error('Aucun fichier n\'a été envoyé');
                return response()->json(['message' => 'Aucun fichier n\'a été envoyé'], 400);
            }

            Log::info('Fichier reçu: ' . $request->file('profile_picture')->getClientOriginalName());

            // Validation du fichier
            $validator = Validator::make($request->all(), [
                'profile_picture' => 'required|file|mimes:jpeg,png,jpg,gif|max:20480',
            ]);

            if ($validator->fails()) {
                Log::error('Validation du fichier échouée: ' . json_encode($validator->errors()));
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // Téléchargement du fichier
            $file = $request->file('profile_picture');
            Log::info('Détails du fichier: ' . json_encode([
                'name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'mime' => $file->getMimeType()
            ]));

            $path = $file->store('profile_pictures', 'public');
            Log::info('Fichier enregistré avec succès à: ' . $path);

            // Mise à jour de l'avatar avec le préfixe /storage/
            $avatarPath = '/storage/' . $path;
            Log::info('Chemin complet de l\'avatar: ' . $avatarPath);
            $profile->avatar = $avatarPath;
            $profile->save();

            return response()->json([
                'message' => 'Photo de profil téléchargée avec succès.',
                'avatar_path' => $avatarPath
            ], 200);

        } catch (\Exception $e) {
            Log::error('Erreur lors du téléchargement de la photo de profil: ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors du téléchargement de la photo de profil.'], 500);
        }
    }

    /**
     * Vérifie si une chaîne est au format JSON valide.
     *
     * @param string $string
     * @return bool
     */
    private function isJson($string): bool
    {
        if (!is_string($string)) {
            return false;
        }

        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
}
