<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\GmailAuthService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use App\Models\GmailConfiguration;

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

            $redirectUrl = $this->gmailAuthService->getRedirectUrl();

            // Pour les routes web, on redirige directement
            return redirect($redirectUrl);

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

            $result = $this->gmailAuthService->handleCallback();

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
}
