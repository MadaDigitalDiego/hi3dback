<?php

namespace App\Services;

use App\Models\User;
use App\Models\GmailConfiguration;
use App\Models\ClientProfile;
use App\Models\ProfessionalProfile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\GoogleProvider;
use Illuminate\Support\Str;

class GmailAuthService
{
    /**
     * Obtenir l'URL de redirection pour l'authentification Gmail
     */
    public function getRedirectUrl(): string
    {
        $config = GmailConfiguration::getActiveConfiguration();
        
        if (!$config || !$config->isComplete()) {
            throw new \Exception('Configuration Gmail non trouvée ou incomplète. Veuillez configurer Gmail OAuth dans l\'administration.');
        }

        // Configurer dynamiquement Socialite avec notre configuration
        $this->configureSocialite($config);

        return Socialite::driver('google')
            ->scopes($config->scopes)
            ->redirect()
            ->getTargetUrl();
    }

    /**
     * Traiter le callback de Google et connecter/créer l'utilisateur
     */
    public function handleCallback(): array
    {
        $config = GmailConfiguration::getActiveConfiguration();
        
        if (!$config || !$config->isComplete()) {
            throw new \Exception('Configuration Gmail non trouvée ou incomplète.');
        }

        // Configurer dynamiquement Socialite
        $this->configureSocialite($config);

        try {
            $googleUser = Socialite::driver('google')->user();
            
            Log::info('Utilisateur Google récupéré', [
                'email' => $googleUser->getEmail(),
                'name' => $googleUser->getName(),
                'id' => $googleUser->getId()
            ]);

            // Chercher un utilisateur existant
            $user = User::where('email', $googleUser->getEmail())->first();

            if ($user) {
                // Utilisateur existant - connexion
                return $this->loginExistingUser($user);
            } else {
                // Nouvel utilisateur - création
                return $this->createNewUser($googleUser);
            }

        } catch (\Exception $e) {
            Log::error('Erreur lors du callback Gmail', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new \Exception('Erreur lors de l\'authentification Gmail: ' . $e->getMessage());
        }
    }

    /**
     * Configurer dynamiquement Socialite avec notre configuration
     */
    private function configureSocialite(GmailConfiguration $config): void
    {
        config([
            'services.google.client_id' => $config->client_id,
            'services.google.client_secret' => $config->client_secret,
            'services.google.redirect' => $config->redirect_uri,
        ]);
    }

    /**
     * Connecter un utilisateur existant
     */
    private function loginExistingUser(User $user): array
    {
        // Marquer l'email comme vérifié si ce n'est pas déjà fait
        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        // Créer un token d'authentification
        $token = $user->createToken('gmail-auth')->plainTextToken;

        Log::info('Connexion réussie via Gmail', ['user_id' => $user->id, 'email' => $user->email]);

        return [
            'success' => true,
            'message' => 'Connexion réussie via Gmail',
            'token' => $token,
            'user' => $user,
            'is_new_user' => false
        ];
    }

    /**
     * Créer un nouvel utilisateur à partir des données Google
     */
    private function createNewUser($googleUser): array
    {
        try {
            // Extraire le prénom et nom
            $fullName = $googleUser->getName();
            $nameParts = explode(' ', $fullName, 2);
            $firstName = $nameParts[0] ?? '';
            $lastName = $nameParts[1] ?? '';

            // Créer l'utilisateur
            $user = User::create([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $googleUser->getEmail(),
                'password' => Hash::make(Str::random(32)), // Mot de passe aléatoire
                'email_verified_at' => now(), // Email déjà vérifié par Google
                'is_professional' => false, // Par défaut client
                'profile_completed' => false,
            ]);

            // Créer le profil client par défaut
            ClientProfile::create([
                'user_id' => $user->id,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $googleUser->getEmail(),
                'completion_percentage' => 30, // Un peu plus élevé car on a déjà quelques infos
            ]);

            // Créer un token d'authentification
            $token = $user->createToken('gmail-auth')->plainTextToken;

            Log::info('Nouvel utilisateur créé via Gmail', [
                'user_id' => $user->id, 
                'email' => $user->email
            ]);

            return [
                'success' => true,
                'message' => 'Compte créé et connexion réussie via Gmail',
                'token' => $token,
                'user' => $user,
                'is_new_user' => true
            ];

        } catch (\Exception $e) {
            Log::error('Erreur lors de la création d\'utilisateur Gmail', [
                'error' => $e->getMessage(),
                'email' => $googleUser->getEmail()
            ]);
            throw new \Exception('Erreur lors de la création du compte: ' . $e->getMessage());
        }
    }

    /**
     * Traiter un utilisateur Google (pour les routes web)
     */
    public function processGoogleUser($googleUser): array
    {
        try {
            Log::info('Traitement utilisateur Google', [
                'email' => $googleUser->getEmail(),
                'name' => $googleUser->getName(),
                'id' => $googleUser->getId()
            ]);

            // Chercher un utilisateur existant
            $user = User::where('email', $googleUser->getEmail())->first();

            if ($user) {
                // Utilisateur existant - connexion
                return $this->loginExistingUser($user);
            } else {
                // Nouvel utilisateur - création
                return $this->createNewUser($googleUser);
            }

        } catch (\Exception $e) {
            Log::error('Erreur lors du traitement de l\'utilisateur Google', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new \Exception('Erreur lors de l\'authentification Gmail: ' . $e->getMessage());
        }
    }

    /**
     * Vérifier si la configuration Gmail est disponible
     */
    public static function isConfigured(): bool
    {
        $config = GmailConfiguration::getActiveConfiguration();
        return $config && $config->isComplete();
    }

    /**
     * Obtenir les informations de configuration (sans les secrets)
     */
    public static function getConfigurationInfo(): ?array
    {
        $config = GmailConfiguration::getActiveConfiguration();
        
        if (!$config) {
            return null;
        }

        return [
            'name' => $config->name,
            'is_complete' => $config->isComplete(),
            'scopes' => $config->scopes,
            'redirect_uri' => $config->redirect_uri,
        ];
    }
}
