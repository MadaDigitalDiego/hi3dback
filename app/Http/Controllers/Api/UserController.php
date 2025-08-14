<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
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
                return response()->json(['message' => 'Votre e-mail n\'est pas vérifié. Veuillez vérifier votre boîte de réception.'], 403);
            }

            // Création du token
            try {
                $token = $user->createToken('api-token')->plainTextToken;
                Log::info('Connexion réussie pour l\'utilisateur: ' . $request->email);
                return response()->json(['token' => $token, 'user' => $user]);
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
            $request->user()->tokens()->delete();
            return response()->json(['message' => 'Déconnexion réussie.']);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la déconnexion de l\'utilisateur ID ' . $request->user()->id . ': ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la déconnexion.'], 500);
        }
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

            // Envoyer l'e-mail de réinitialisation
            Mail::to($user->email)->send(new ResetPasswordMail($resetUrl));

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
}
