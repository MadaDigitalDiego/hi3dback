<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\GmailAuthService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use App\Models\GmailConfiguration;
use Laravel\Socialite\Facades\Socialite;

/**
 * @group Gmail Authentication
 *
 * API endpoints pour l'authentification via Gmail OAuth
 */
class GmailAuthController extends Controller
{
    protected GmailAuthService $gmailAuthService;

    public function __construct(GmailAuthService $gmailAuthService)
    {
        $this->gmailAuthService = $gmailAuthService;
    }

    /**
     * Obtenir l'URL de redirection Gmail
     *
     * @response 200 {
     *   "success": true,
     *   "redirect_url": "https://accounts.google.com/oauth/authorize?...",
     *   "message": "URL de redirection Gmail générée avec succès"
     * }
     *
     * @response 500 {
     *   "success": false,
     *   "message": "Configuration Gmail non trouvée ou incomplète"
     * }
     */
    public function redirect(): JsonResponse
    {
        try {
            Log::info('Demande de redirection Gmail');

            $redirectUrl = $this->gmailAuthService->getRedirectUrl();

            return response()->json([
                'success' => true,
                'redirect_url' => $redirectUrl,
                'message' => 'URL de redirection Gmail générée avec succès'
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la génération de l\'URL de redirection Gmail', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Traiter le callback de Google OAuth
     *
     * Cette route est appelée par Google après l'authentification
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Connexion réussie via Gmail",
     *   "token": "1|abc123...",
     *   "user": {...},
     *   "is_new_user": false
     * }
     *
     * @response 500 {
     *   "success": false,
     *   "message": "Erreur lors de l'authentification Gmail"
     * }
     */
    public function callback(Request $request): JsonResponse
    {
        try {
            Log::info('Callback Gmail reçu', [
                'query_params' => $request->query()
            ]);

            $result = $this->gmailAuthService->handleCallback();

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('Erreur lors du callback Gmail', [
                'error' => $e->getMessage(),
                'query_params' => $request->query()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Endpoint spécialisé pour l'authentification Google depuis le frontend
     *
     * Cette route gère l'authentification Google avec la logique métier requise.
     * NOTE: La connexion Google est autorisée même si le profil est incomplet.
     * Le frontend utilisera le flag profile_completed pour afficher le ProfileWizard si nécessaire.
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Connexion réussie via Gmail",
     *   "token": "1|abc123...",
     *   "user": {...},
     *   "profile_completed": false,
     *   "redirect_to": "dashboard"
     * }
     *
     * @response 401 {
     *   "success": false,
     *   "message": "Aucun compte n'existe avec cette adresse email",
     *   "error_type": "user_not_found",
     *   "redirect_to": "login"
     * }
     */
    public function frontendCallback(Request $request): JsonResponse
    {
        try {
            Log::info('Callback Gmail frontend reçu', [
                'query_params' => $request->query()
            ]);

            $result = $this->gmailAuthService->handleCallback();

            // Adapter la réponse pour le frontend
            if ($result['success']) {
                // Déterminer la redirection en fonction du profil
                $redirectTo = isset($result['profile_completed']) && $result['profile_completed'] ? 'dashboard' : 'dashboard';
                
                return response()->json([
                    'success' => true,
                    'message' => $result['message'],
                    'token' => $result['token'],
                    'user' => $result['user'],
                    'profile_completed' => $result['profile_completed'] ?? false,
                    'redirect_to' => $redirectTo
                ]);
            } else {
                // Gestion des erreurs spécifiques
                $statusCode = 401; // Par défaut

                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                    'error_type' => $result['error_type'] ?? 'unknown',
                    'redirect_to' => 'login',
                    'user_exists' => $result['user_exists'] ?? false,
                    'profile_completed' => $result['profile_completed'] ?? false
                ], $statusCode);
            }

        } catch (\Exception $e) {
            Log::error('Erreur lors du callback Gmail frontend', [
                'error' => $e->getMessage(),
                'query_params' => $request->query()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'authentification Gmail: ' . $e->getMessage(),
                'error_type' => 'server_error',
                'redirect_to' => 'login'
            ], 500);
        }
    }

    /**
     * Vérifier le statut de la configuration Gmail
     *
     * @response 200 {
     *   "configured": true,
     *   "configuration": {
     *     "name": "Gmail OAuth Configuration",
     *     "is_complete": true,
     *     "scopes": ["openid", "profile", "email"],
     *     "redirect_uri": "https://example.com/api/auth/gmail/callback"
     *   }
     * }
     */
    public function status(): JsonResponse
    {
        try {
            $isConfigured = GmailAuthService::isConfigured();
            $configInfo = GmailAuthService::getConfigurationInfo();

            return response()->json([
                'configured' => $isConfigured,
                'configuration' => $configInfo
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la vérification du statut Gmail', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'configured' => false,
                'configuration' => null,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Redirection Gmail pour les routes web (avec sessions)
     */
    public function webRedirect(): RedirectResponse|JsonResponse
    {
        try {
            Log::info('Demande de redirection Gmail (web)');

            $config = GmailConfiguration::getActiveConfiguration();

            if (!$config || !$config->isComplete()) {
                throw new \Exception('Configuration Gmail non trouvée ou incomplète. Veuillez configurer Gmail OAuth dans l\'administration.');
            }

            // Configurer dynamiquement Socialite avec notre configuration
            config([
                'services.google.client_id' => $config->client_id,
                'services.google.client_secret' => $config->client_secret,
                'services.google.redirect' => $config->redirect_uri,
            ]);

            // Utiliser Socialite directement dans le contrôleur pour avoir accès aux sessions
            return Socialite::driver('google')
                ->scopes($config->scopes)
                ->redirect();

        } catch (\Exception $e) {
            Log::error('Erreur lors de la redirection Gmail (web)', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Callback Gmail pour les routes web (avec sessions)
     */
    public function webCallback(Request $request): RedirectResponse|JsonResponse
    {
        try {
            Log::info('Callback Gmail reçu (web)', [
                'query_params' => $request->query()
            ]);

            $config = GmailConfiguration::getActiveConfiguration();

            if (!$config || !$config->isComplete()) {
                throw new \Exception('Configuration Gmail non trouvée ou incomplète.');
            }

            // Configurer dynamiquement Socialite avec notre configuration
            config([
                'services.google.client_id' => $config->client_id,
                'services.google.client_secret' => $config->client_secret,
                'services.google.redirect' => $config->redirect_uri,
            ]);

            // Utiliser Socialite directement pour récupérer l'utilisateur
            $googleUser = Socialite::driver('google')->user();

            Log::info('Utilisateur Google récupéré', [
                'email' => $googleUser->getEmail(),
                'name' => $googleUser->getName(),
                'id' => $googleUser->getId()
            ]);

            $result = $this->gmailAuthService->processGoogleUser($googleUser);

            // Pour les routes web, on redirige vers la page de test avec les résultats
            $queryParams = http_build_query([
                'success' => $result['success'] ? '1' : '0',
                'message' => $result['message'],
                'is_new_user' => $result['is_new_user'] ? '1' : '0',
                'user_email' => $result['user']->email ?? '',
                'token' => substr($result['token'] ?? '', 0, 20) . '...' // Tronquer le token pour la sécurité
            ]);

            return redirect('/test-gmail?' . $queryParams);

        } catch (\Exception $e) {
            Log::error('Erreur lors du callback Gmail (web)', [
                'error' => $e->getMessage()
            ]);

            $queryParams = http_build_query([
                'success' => '0',
                'error' => $e->getMessage()
            ]);

            return redirect('/test-gmail?' . $queryParams);
        }
    }

    /**
     * Redirection Gmail pour le frontend (avec sessions)
     */
    public function frontendRedirect(Request $request): RedirectResponse|JsonResponse
    {
        try {
            Log::info('Demande de redirection Gmail frontend (web)');

            $frontendUrl = $this->resolveFrontendUrl($request);
            session(['oauth_frontend_url' => $frontendUrl]);

            $config = GmailConfiguration::getActiveConfiguration();

            if (!$config || !$config->isComplete()) {
                throw new \Exception('Configuration Gmail non trouvée ou incomplète. Veuillez configurer Gmail OAuth dans l\'administration.');
            }

            // Configurer dynamiquement Socialite avec notre configuration
            config([
                'services.google.client_id' => $config->client_id,
                'services.google.client_secret' => $config->client_secret,
                'services.google.redirect' => $config->redirect_uri, // Utiliser l'URI configurée dans Google Console
            ]);

            // Utiliser Socialite directement dans le contrôleur pour avoir accès aux sessions
            return Socialite::driver('google')
                ->scopes($config->scopes)
                ->redirect();

        } catch (\Exception $e) {
            Log::error('Erreur lors de la redirection Gmail frontend (web)', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Callback Gmail pour le frontend (avec sessions)
     * NOTE: La connexion Google est autorisée même si le profil est incomplet.
     */
    public function frontendWebCallback(Request $request): RedirectResponse
    {
        try {
            Log::info('Callback Gmail frontend reçu (web)', [
                'query_params' => $request->query()
            ]);

            $config = GmailConfiguration::getActiveConfiguration();

            if (!$config || !$config->isComplete()) {
                throw new \Exception('Configuration Gmail non trouvée ou incomplète.');
            }

            // Configurer dynamiquement Socialite avec notre configuration
            config([
                'services.google.client_id' => $config->client_id,
                'services.google.client_secret' => $config->client_secret,
                'services.google.redirect' => $config->redirect_uri, // Utiliser l'URI configurée dans Google Console
            ]);

            // Utiliser Socialite directement pour récupérer l'utilisateur
            $googleUser = Socialite::driver('google')->user();

            Log::info('Utilisateur Google récupéré (frontend)', [
                'email' => $googleUser->getEmail(),
                'name' => $googleUser->getName(),
                'id' => $googleUser->getId()
            ]);

            $result = $this->gmailAuthService->processGoogleUser($googleUser);

            // Construire l'URL de redirection vers le frontend
            $frontendUrl = session('oauth_frontend_url');
            if (!$this->isAllowedFrontendUrl($frontendUrl)) {
                $frontendUrl = $this->resolveFrontendUrl($request);
            }

            session()->forget('oauth_frontend_url');

            if ($result['success']) {
                // Succès - rediriger vers le frontend avec les données
                // Inclure profile_completed pour que le frontend sache s'il doit afficher le ProfileWizard
                $queryParams = http_build_query([
                    'google_auth' => 'success',
                    'token' => $result['token'],
                    'user' => $this->toBase64Url(json_encode($result['user'])),
                    'message' => $result['message'],
                    'profile_completed' => isset($result['profile_completed']) && $result['profile_completed'] ? 'true' : 'false'
                ]);

                return redirect($frontendUrl . '/login?' . $queryParams);
            } else {
                // Erreur - rediriger vers le frontend avec l'erreur
                $queryParams = http_build_query([
                    'google_auth' => 'error',
                    'error_type' => $result['error_type'] ?? 'unknown',
                    'message' => $result['message'],
                    'user_exists' => $result['user_exists'] ?? false,
                    'profile_completed' => $result['profile_completed'] ?? false
                ]);

                return redirect($frontendUrl . '/login?' . $queryParams);
            }

        } catch (\Exception $e) {
            Log::error('Erreur lors du callback Gmail frontend (web)', [
                'error' => $e->getMessage()
            ]);

            $frontendUrl = session('oauth_frontend_url');
            if (!$this->isAllowedFrontendUrl($frontendUrl)) {
                $frontendUrl = $this->resolveFrontendUrl($request);
            }

            session()->forget('oauth_frontend_url');

            $queryParams = http_build_query([
                'google_auth' => 'error',
                'error_type' => 'server_error',
                'message' => 'Erreur lors de l\'authentification Gmail: ' . $e->getMessage()
            ]);

            return redirect($frontendUrl . '/login?' . $queryParams);
        }
    }

    /**
     * Encode une chaîne en base64 URL-safe (sans +, /, =).
     */
    private function toBase64Url(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    /**
     * Résout l'URL frontend cible avec priorité: query -> origin/referer -> config.
     */
    private function resolveFrontendUrl(Request $request): string
    {
        $candidates = [
            $request->query('frontend_url'),
            $request->headers->get('origin'),
            $this->extractOriginFromReferer($request->headers->get('referer')),
            rtrim((string) env('FRONTEND_URL', 'https://dev2.hi-3d.com'), '/'),
        ];

        foreach ($candidates as $candidate) {
            if ($this->isAllowedFrontendUrl($candidate)) {
                return rtrim((string) $candidate, '/');
            }
        }

        return 'https://dev2.hi-3d.com';
    }

    /**
     * Vérifie qu'une URL frontend est autorisée pour éviter les open redirects.
     */
    private function isAllowedFrontendUrl(?string $url): bool
    {
        if (!$url) {
            return false;
        }

        $parts = parse_url($url);
        if (!$parts || !isset($parts['scheme'], $parts['host'])) {
            return false;
        }

        if (!in_array($parts['scheme'], ['http', 'https'], true)) {
            return false;
        }

        $normalized = $parts['scheme'] . '://' . $parts['host'] . (isset($parts['port']) ? ':' . $parts['port'] : '');

        $envAllowed = array_values(array_filter(array_map(
            static fn (string $origin) => rtrim(trim($origin), '/'),
            explode(',', (string) env('FRONTEND_ALLOWED_ORIGINS', ''))
        )));

        $defaults = [
            rtrim((string) env('FRONTEND_URL', 'https://dev2.hi-3d.com'), '/'),
            'https://dev2.hi-3d.com',
            'https://dev.hi-3d.com',
            'http://localhost:3000',
        ];

        $allowedOrigins = array_unique(array_merge($defaults, $envAllowed));

        return in_array($normalized, $allowedOrigins, true);
    }

    /**
     * Extrait l'origine (scheme+host+port) depuis un referer complet.
     */
    private function extractOriginFromReferer(?string $referer): ?string
    {
        if (!$referer) {
            return null;
        }

        $parts = parse_url($referer);
        if (!$parts || !isset($parts['scheme'], $parts['host'])) {
            return null;
        }

        return $parts['scheme'] . '://' . $parts['host'] . (isset($parts['port']) ? ':' . $parts['port'] : '');
    }
}
