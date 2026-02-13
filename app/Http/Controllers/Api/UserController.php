<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\PersonalAccessSession;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\ClientProfile;
use App\Services\EmailService;
use App\Mail\ResetPasswordMail;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\LoginRequest;
use App\Models\ProfessionalProfile;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\RegisterRequest;
use Illuminate\Auth\Events\Registered;
use App\Http\Requests\ResetPasswordRequest;

use App\Http\Requests\ForgotPasswordRequest;


class UserController extends Controller
{


    /**
     * Lister tous les utilisateurs.
     *
     * @return JsonResponse
     *
     *public function index(): JsonResponse
     *{
     *   $users = User::all(); // Récupère tous les utilisateurs de la base de données
     *   return response()->json(['users' => $users]);
     * }
     ****/

    public function index(): JsonResponse
    {
        try {
            $users = User::where('is_professional', true)->get(); // Récupère uniquement les utilisateurs professionnels
            return response()->json(['users' => $users]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des utilisateurs professionnels: ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la récupération des utilisateurs.'], 500);
        }
    }

    /**
     * Afficher les détails d'un utilisateur spécifique.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $user = User::find($id); // Recherche un utilisateur par son ID

            if (!$user) {
                return response()->json(['message' => 'Utilisateur non trouvé.'], 404); // Retourne une erreur 404 si l'utilisateur n'est pas trouvé
            }

            $profile = null;
            $profileType = '';

            if ($user->is_professional) {
                $profile = $user->freelanceProfile;
                $profileType = 'freelance';
            } else {
                $profile = $user->companyProfile;
                $profileType = 'company';
            }

            return response()->json([
                'user' => $user,
                'profile_type' => $profileType,
                'profile_data' => $profile,
            ], 200);

            // return response()->json(['user' => $user]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'affichage des détails de l\'utilisateur ID ' . $id . ': ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la récupération des détails de l\'utilisateur.'], 500);
        }
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            // Vérifier si l'email existe déjà
            // $existingUser = User::where('email', $request->email)->first();
            // if ($existingUser) {
            //     return response()->json([
            //         'message' => 'Cette adresse email est déjà utilisée.',
            //         'errors' => ['email' => ['Cette adresse email est déjà utilisée.']]
            //     ], 422);
            // }

            // Créer l'utilisateur
            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'is_professional' => $request->is_professional,
                'profile_completed' => false, // Par défaut, le profil n'est pas complété
            ]);

            // Créer automatiquement un profil client ou professionnel
            if ($request->is_professional) {
                // Créer un profil professionnel avec des valeurs par défaut pour tous les champs obligatoires
                ProfessionalProfile::create([
                    'user_id' => $user->id,
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                    'email' => $request->email,
                    'profession' => 'Non spécifié',
                    'years_of_experience' => 0,
                    'hourly_rate' => 0.00,
                    'availability_status' => 'available',
                    'rating' => 0.0,
                    'completion_percentage' => 20,
                    'skills' => json_encode([]),
                    'languages' => json_encode([]),
                    'services_offered' => json_encode([]),
                    'social_links' => json_encode([]),
                ]);
            } else {
                // Créer un profil client
                ClientProfile::create([
                    'user_id' => $user->id,
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                    'email' => $request->email,
                    'completion_percentage' => 20, // Pourcentage initial de complétion
                ]);
            }

            // Utiliser le service d'e-mail pour envoyer l'e-mail de vérification
            $emailSent = EmailService::sendVerificationEmail($user);

            if (!$emailSent) {
                Log::warning('Impossible d\'envoyer l\'e-mail de vérification à ' . $user->email . '. L\'inscription continue quand même.');
            }

            return response()->json(['message' => 'Inscription réussie. Veuillez vérifier votre e-mail pour confirmer votre compte.'], 201);
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'enregistrement de l\'utilisateur ' . $request->email . ': ' . $e->getMessage());
            Log::error('Trace: ' . $e->getTraceAsString());

            // En mode debug, renvoyer plus de détails sur l'erreur
            if (env('APP_DEBUG', false)) {
                return response()->json([
                    'message' => 'Erreur lors de l\'inscription de l\'utilisateur.',
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ], 500);
            }

            return response()->json(['message' => 'Erreur lors de l\'inscription de l\'utilisateur.'], 500);
        }
    }

    public function login(LoginRequest $request): JsonResponse
    {
        try {
            // Log de début de tentative de connexion
            Log::info('Tentative de connexion pour l\'utilisateur: ' . $request->email);

            // Récupération de l'utilisateur
            $user = User::where('email', $request->email)->first();

            // Vérification des identifiants
            if (!$user) {
                Log::warning('Tentative de connexion avec un email inexistant: ' . $request->email);
                return response()->json(['message' => 'Utilisateur n\'est pas encore inscrit.'], 401);
            }

            if (!Hash::check($request->password, $user->password)) {
                Log::warning('Tentative de connexion avec un mot de passe incorrect pour: ' . $request->email);
                return response()->json(['message' => 'Le mot de passe est incorrect.'], 401);
            }

            // Vérification de l'email
            if (!$user->hasVerifiedEmail()) {
                Log::info('Tentative de connexion avec un email non vérifié: ' . $request->email);
                return response()->json([
                    'message' => 'Votre e-mail n\'est pas vérifié. Veuillez vérifier votre boîte de réception ou demander un nouveau lien de vérification.',
                    'email_not_verified' => true,
                    'email' => $user->email,
                ], 403);
            }

            // Création du token
            try {
                // Supprimer les tokens existants pour cet utilisateur (session unique)
                $user->tokens()->delete();
                
                $accessToken = $user->createToken('api-token');
                $token = $accessToken->plainTextToken;
                $tokenId = $accessToken->accessToken->id;

                // Créer un enregistrement de session
                PersonalAccessSession::create([
                    'user_id' => $user->id,
                    'token_id' => $tokenId,
                    'last_activity_at' => now(),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'is_active' => true,
                ]);

                // Récupération des informations d'abonnement de l'utilisateur
                $subscription = $user->currentSubscription();
                $subscriptionData = $subscription ? $subscription->load('plan', 'coupon') : null;

                Log::info('Connexion réussie pour l\'utilisateur: ' . $request->email);

                return response()->json([
                    'token' => $token,
                    'user' => $user,
                    // Informations d'abonnement de l'utilisateur (null si aucun abonnement actif)
                    'subscription' => $subscriptionData,
                    // Informations de session
                    'session' => [
                        'timeout_minutes' => config('session.timeout', 10),
                        'expires_at' => now()->addMinutes(config('session.timeout', 10))->toIso8601String(),
                    ],
                ]);
            } catch (\Exception $tokenException) {
                Log::error('Erreur lors de la création du token pour l\'utilisateur ' . $request->email . ': ' . $tokenException->getMessage());
                throw $tokenException;
            }
        } catch (\Exception $e) {
            Log::error('Erreur lors de la connexion de l\'utilisateur ' . $request->email . ': ' . $e->getMessage());
            Log::error('Trace: ' . $e->getTraceAsString());
            return response()->json([
                'message' => 'Erreur lors de la connexion.',
                'error' => $e->getMessage(),
                'status' => 500
            ], 500);
        }
    }

    public function logout(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $tokenId = $request->user()->currentAccessToken()->id;
            
            // Supprimer l'enregistrement de session
            PersonalAccessSession::where('token_id', $tokenId)->delete();
            
            // Supprimer le token
            $request->user()->tokens()->delete();
            
            Log::info('Déconnexion réussie pour l\'utilisateur: ' . $user->email);
            
            return response()->json(['message' => 'Déconnexion réussie.']);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la déconnexion de l\'utilisateur ID ' . $request->user()->id . ': ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la déconnexion.'], 500);
        }
    }

    /**
     * Obtenir les informations de la session courante.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function sessionInfo(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $token = $user->currentAccessToken();
            
            if (!$token) {
                return response()->json(['message' => 'Token non trouvé.'], 404);
            }

            $session = PersonalAccessSession::where('token_id', $token->id)
                ->where('user_id', $user->id)
                ->first();

            if (!$session) {
                return response()->json([
                    'message' => 'Session non trouvée.',
                    'session_expired' => true,
                ], 404);
            }

            $timeoutMinutes = config('session.timeout', 10);
            $expiresAt = $session->last_activity_at->addMinutes($timeoutMinutes);
            $isExpired = $session->isExpired($timeoutMinutes);

            return response()->json([
                'session' => [
                    'is_active' => $session->is_active,
                    'is_expired' => $isExpired,
                    'last_activity_at' => $session->last_activity_at->toIso8601String(),
                    'expires_at' => $expiresAt->toIso8601String(),
                    'timeout_minutes' => $timeoutMinutes,
                    'remaining_seconds' => $isExpired ? 0 : $expiresAt->diffInSeconds(now()),
                    'ip_address' => $session->ip_address,
                    'user_agent' => $session->user_agent,
                ],
                'user' => $user,
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des informations de session pour l\'utilisateur ID ' . $request->user()->id . ': ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la récupération des informations de session.'], 500);
        }
    }

    /**
     * Renouveler la session (prolonger le timeout).
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function refreshSession(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $token = $user->currentAccessToken();
            
            if (!$token) {
                return response()->json(['message' => 'Token non trouvé.'], 404);
            }

            $session = PersonalAccessSession::where('token_id', $token->id)
                ->where('user_id', $user->id)
                ->first();

            if (!$session) {
                return response()->json([
                    'message' => 'Session non trouvée.',
                    'session_expired' => true,
                    'redirect_to' => '/login',
                ], 401);
            }

            // Vérifier si la session est inactive
            if (!$session->is_active) {
                // Supprimer le token
                $user->tokens()->delete();
                
                return response()->json([
                    'message' => 'Session inactive. Veuillez vous reconnecter.',
                    'session_expired' => true,
                    'redirect_to' => '/login',
                ], 401);
            }

            // Vérifier si la session a expiré
            $timeoutMinutes = config('session.timeout', 10);
            if ($session->isExpired($timeoutMinutes)) {
                // Désactiver la session et supprimer le token
                $session->deactivate();
                $user->tokens()->delete();
                
                return response()->json([
                    'message' => 'Session expirée par inactivité.',
                    'session_expired' => true,
                    'redirect_to' => '/login',
                ], 401);
            }

            // Renouveler la session (mettre à jour last_activity_at)
            $session->updateActivity();

            Log::info('Session renouvelée pour l\'utilisateur: ' . $user->email);

            $timeoutMinutes = config('session.timeout', 10);

            return response()->json([
                'message' => 'Session renouvelée avec succès.',
                'session' => [
                    'last_activity_at' => $session->last_activity_at->toIso8601String(),
                    'expires_at' => $session->last_activity_at->addMinutes($timeoutMinutes)->toIso8601String(),
                    'timeout_minutes' => $timeoutMinutes,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors du renouvellement de session pour l\'utilisateur ID ' . $request->user()->id . ': ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors du renouvellement de session.'], 500);
        }
    }

    /**
     * Obtenir toutes les sessions actives de l'utilisateur.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function sessions(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $currentTokenId = $request->user()->currentAccessToken()->id;

            $sessions = PersonalAccessSession::getActiveSessionsForUser($user->id);

            $sessionsData = $sessions->map(function ($session) use ($currentTokenId, $user) {
                $timeoutMinutes = config('session.timeout', 10);
                $isCurrent = $session->token_id === $currentTokenId;
                $expiresAt = $session->last_activity_at->addMinutes($timeoutMinutes);

                return [
                    'id' => $session->id,
                    'is_current' => $isCurrent,
                    'is_active' => $session->is_active,
                    'is_expired' => $session->isExpired($timeoutMinutes),
                    'last_activity_at' => $session->last_activity_at->toIso8601String(),
                    'expires_at' => $expiresAt->toIso8601String(),
                    'ip_address' => $session->ip_address,
                    'user_agent' => $session->user_agent,
                    'device' => $this->parseUserAgent($session->user_agent),
                ];
            });

            return response()->json([
                'sessions' => $sessionsData,
                'current_session_id' => $currentTokenId,
                'timeout_minutes' => config('session.timeout', 10),
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des sessions pour l\'utilisateur ID ' . $request->user()->id . ': ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la récupération des sessions.'], 500);
        }
    }

    /**
     * Supprimer une session spécifique (déconnexion d'un autre appareil).
     *
     * @param Request $request
     * @param int $sessionId
     * @return JsonResponse
     */
    public function destroySession(Request $request, int $sessionId): JsonResponse
    {
        try {
            $user = $request->user();
            $currentTokenId = $request->user()->currentAccessToken()->id;

            $session = PersonalAccessSession::where('id', $sessionId)
                ->where('user_id', $user->id)
                ->first();

            if (!$session) {
                return response()->json(['message' => 'Session non trouvée.'], 404);
            }

            // Ne pas permettre de supprimer la session courante via cette route
            if ($session->token_id === $currentTokenId) {
                return response()->json(['message' => 'Impossible de supprimer la session courante. Utilisez logout pour vous déconnecter.'], 400);
            }

            // Supprimer le token associé à la session
            $user->tokens()->where('id', $session->token_id)->delete();

            // Supprimer la session
            $session->delete();

            Log::info('Session supprimée pour l\'utilisateur: ' . $user->email, ['session_id' => $sessionId]);

            return response()->json(['message' => 'Session supprimée avec succès.']);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la suppression de la session pour l\'utilisateur ID ' . $request->user()->id . ': ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la suppression de la session.'], 500);
        }
    }

    /**
     * Supprimer toutes les sessions sauf la courante.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function destroyOtherSessions(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $currentTokenId = $request->user()->currentAccessToken()->id;

            // Récupérer toutes les sessions actives sauf la courante
            $otherSessions = PersonalAccessSession::where('user_id', $user->id)
                ->where('is_active', true)
                ->where('token_id', '!=', $currentTokenId)
                ->get();

            // Supprimer les tokens associés
            $tokenIds = $otherSessions->pluck('token_id')->toArray();
            if (!empty($tokenIds)) {
                $user->tokens()->whereIn('id', $tokenIds)->delete();
            }

            // Supprimer les sessions
            $otherSessions->each->delete();

            Log::info('Toutes les autres sessions supprimées pour l\'utilisateur: ' . $user->email);

            return response()->json([
                'message' => 'Toutes les autres sessions ont été supprimées.',
                'sessions_removed' => $otherSessions->count(),
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la suppression des autres sessions pour l\'utilisateur ID ' . $request->user()->id . ': ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la suppression des sessions.'], 500);
        }
    }

    /**
     * Parser le user agent pour obtenir des informations sur l'appareil.
     *
     * @param string|null $userAgent
     * @return array
     */
    private function parseUserAgent(?string $userAgent): array
    {
        if (!$userAgent) {
            return [
                'browser' => 'Unknown',
                'os' => 'Unknown',
                'device' => 'Unknown',
            ];
        }

        // Simple user agent parsing (could be enhanced with a library like jenssegers/agent)
        $browser = 'Unknown';
        $os = 'Unknown';
        $device = 'Desktop';

        // Detect browser
        if (preg_match('/(Firefox|Chrome|Safari|Edge|Opera|MSIE|Trident)/i', $userAgent, $matches)) {
            $browser = $matches[1];
            if ($browser === 'Trident') {
                $browser = 'Internet Explorer';
            }
        }

        // Detect OS
        if (preg_match('/(Windows NT|Mac OS|Linux|Android|iOS|iPhone|iPad)/i', $userAgent, $matches)) {
            $os = $matches[1];
            if ($os === 'Windows NT') {
                $os = 'Windows';
            } elseif (preg_match('/(\d+)/', $userAgent, $versionMatch)) {
                $os .= ' ' . $versionMatch[1];
            }
        }

        // Detect device type
        if (preg_match('/(Mobile|Tablet)/i', $userAgent)) {
            $device = preg_match('/(Tablet|iPad)/i', $userAgent) ? 'Tablet' : 'Mobile';
        }

        return [
            'browser' => $browser,
            'os' => $os,
            'device' => $device,
        ];
    }

    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        try {
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json(['message' => 'Aucun utilisateur trouvé avec cette adresse e-mail.'], 404);
            }

            // Générer un token de réinitialisation unique
            $token = Str::random(60);
            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $request->email],
                ['token' => Hash::make($token), 'created_at' => now()]
            );

            // Générer l'URL de réinitialisation pour l'application React
            $resetUrl = sprintf(
                '%s/reset-password?token=%s&email=%s',
                env('FRONTEND_URL', 'http://127.0.0.1:3000'), // URL de votre application React
                $token,
                urlencode($request->email)
            );

            // Envoyer l'e-mail de réinitialisation (queued)
            Mail::to($user->email)->queue(new ResetPasswordMail($resetUrl));

            return response()->json(['message' => 'Un lien de réinitialisation de mot de passe a été envoyé à votre adresse e-mail.'], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la demande de mot de passe oublié pour l\'email ' . $request->email . ': ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la demande de réinitialisation du mot de passe.'], 500);
        }
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        try {
            $passwordReset = DB::table('password_reset_tokens')
                ->where('email', $request->email)
                ->first();

            if (!$passwordReset || !Hash::check($request->token, $passwordReset->token)) {
                return response()->json(['message' => 'Token de réinitialisation invalide.'], 400);
            }

            $user = User::where('email', $request->email)->first();
            $user->password = Hash::make($request->password);
            $user->save();

            DB::table('password_reset_tokens')->where('email', $request->email)->delete();

            return response()->json(['message' => 'Mot de passe réinitialisé avec succès.'], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la réinitialisation du mot de passe pour l\'email ' . $request->email . ': ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la réinitialisation du mot de passe.'], 500);
        }
    }

    public function changePassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'current_password.required' => 'L\'ancien mot de passe est requis.',
            'password.required' => 'Le nouveau mot de passe est requis.',
            'password.min' => 'Le nouveau mot de passe doit contenir au moins 8 caractères.',
            'password.confirmed' => 'La confirmation du nouveau mot de passe ne correspond pas.',
        ]);

        try {
            $user = $request->user();

            // Vérifier si l'ancien mot de passe est correct
            if (!Hash::check($validated['current_password'], $user->password)) {
                return response()->json([
                    'message' => 'L\'ancien mot de passe est incorrect.',
                    'errors' => [
                        'current_password' => ['L\'ancien mot de passe est incorrect.']
                    ]
                ], 422);
            }

            $user->password = Hash::make($validated['password']);
            $user->save();

            return response()->json(['message' => 'Mot de passe mis à jour avec succès.'], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise à jour du mot de passe pour l\'utilisateur ID ' . $request->user()->id . ': ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la mise à jour du mot de passe.'], 500);
        }
    }

    public function switchAccountType(Request $request): JsonResponse
    {
        $data = $request->validate([
            'is_professional' => 'required|boolean',
        ]);

        try {
            /** @var User|null $user */
            $user = $request->user();

            if (!$user) {
                return response()->json(['message' => 'Utilisateur non authentifié.'], 401);
            }

            $targetIsProfessional = (bool) $data['is_professional'];

            // Si l'utilisateur devient professionnel, s'assurer qu'un profil professionnel existe
            if ($targetIsProfessional) {
                if (!$user->professionalProfile) {
                    ProfessionalProfile::create([
                        'user_id' => $user->id,
                        'first_name' => $user->first_name,
                        'last_name' => $user->last_name,
                        'email' => $user->email,
                        'profession' => 'Non spécifié',
                        'years_of_experience' => 0,
                        'hourly_rate' => 0.00,
                        'availability_status' => 'available',
                        'rating' => 0.0,
                        'completion_percentage' => 20,
                        'skills' => json_encode([]),
                        'languages' => json_encode([]),
                        'services_offered' => json_encode([]),
                        'social_links' => json_encode([]),
                    ]);
                }
            } else {
                // Si l'utilisateur devient client, s'assurer qu'un profil client existe
                if (!$user->clientProfile) {
                    ClientProfile::create([
                        'user_id' => $user->id,
                        'first_name' => $user->first_name,
                        'last_name' => $user->last_name,
                        'email' => $user->email,
                        'completion_percentage' => 20,
                    ]);
                }
            }

            // Mettre à jour le type de compte sur l'utilisateur
            $user->is_professional = $targetIsProfessional;
            $user->save();

            // Rafraîchir les relations
            $user->refresh();

            $profileType = $user->is_professional ? 'professional' : 'client';
            $profileData = $user->is_professional ? $user->professionalProfile : $user->clientProfile;

            return response()->json([
                'message' => 'Type de compte mis à jour avec succès.',
                'user' => $user,
                'profile_type' => $profileType,
                'profile_data' => $profileData,
            ], 200);
        } catch (\Exception $e) {
            Log::error("Erreur lors du changement de type de compte pour l'utilisateur ID " . optional($request->user())->id . ': ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors du changement de type de compte.'], 500);
        }
    }

    public function user(Request $request): JsonResponse
    {
        try {
            return response()->json($request->user());
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des informations de l\'utilisateur authentifié ID ' . $request->user()->id . ': ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la récupération des informations de l\'utilisateur.'], 500);
        }
    }

    public function verifyEmail(Request $request): RedirectResponse
    {
        try {
            $user = User::find($request->route('id'));

            if (!$user || !hash_equals((string) $request->route('hash'), sha1($user->email))) {
                return redirect(env('FRONTEND_URL', 'http://localhost:3000') . '/error?message=Lien de vérification invalide.');
            }

            if ($user->hasVerifiedEmail()) {
                return redirect(env('FRONTEND_URL', 'http://localhost:3000') . '/error?message=Adresse e-mail déjà vérifiée.');
            }

            $user->markEmailAsVerified();
            event(new Registered($user));

            // Récupérer l'URL de redirection depuis le paramètre `redirect`
            $redirectUrl = urldecode($request->query('redirect', env('FRONTEND_URL', 'http://localhost:3000') . '/login?verified=true'));
            return redirect($redirectUrl);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la vérification de l\'email pour l\'utilisateur ID ' . $request->route('id') . ': ' . $e->getMessage());
            return redirect(env('FRONTEND_URL', 'http://localhost:3000') . '/error?message=Erreur lors de la vérification de l\'email.'); // Redirection avec message d'erreur générique
        }
    }

    public function resendVerificationEmail(Request $request): JsonResponse
    {
        try {
            if ($request->user()->hasVerifiedEmail()) {
                return response()->json(['message' => 'Adresse e-mail déjà vérifiée.'], 400);
            }

            // Utiliser le service d'e-mail pour envoyer l'e-mail de vérification
            $emailSent = EmailService::sendVerificationEmail($request->user());

            if (!$emailSent) {
                Log::warning('Impossible d\'envoyer l\'e-mail de vérification à ' . $request->user()->email . '.');
                return response()->json(['message' => 'Erreur lors de l\'envoi de l\'e-mail de vérification. Veuillez réessayer plus tard.'], 500);
            }

            return response()->json(['message' => 'Un nouveau lien de vérification a été envoyé à votre adresse e-mail.'], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors du renvoi de l\'email de vérification pour l\'utilisateur ID ' . $request->user()->id . ': ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors du renvoi de l\'email de vérification.'], 500);
        }
    }

    public function resendVerificationEmailPublic(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        try {
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json(['message' => 'Aucun utilisateur trouvé avec cette adresse e-mail.'], 404);
            }

            if ($user->hasVerifiedEmail()) {
                return response()->json(['message' => 'Adresse e-mail déjà vérifiée.'], 400);
            }

            // Utiliser le service d'e-mail pour envoyer l'e-mail de vérification
            $emailSent = EmailService::sendVerificationEmail($user);

            if (!$emailSent) {
                Log::warning('Impossible d\'envoyer l\'e-mail de vérification à ' . $user->email . '.');
                return response()->json(['message' => 'Erreur lors de l\'envoi de l\'e-mail de vérification. Veuillez réessayer plus tard.'], 500);
            }

            return response()->json(['message' => 'Un nouveau lien de vérification a été envoyé à votre adresse e-mail.'], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors du renvoi public de l\'email de vérification pour ' . $request->email . ': ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors du renvoi de l\'email de vérification.'], 500);
        }
    }
}
