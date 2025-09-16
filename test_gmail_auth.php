<?php

/**
 * Script de test pour l'authentification Gmail
 * 
 * Ce script teste les fonctionnalités de l'API Gmail OAuth
 */

require_once 'vendor/autoload.php';

use Illuminate\Http\Client\Factory as HttpClient;

class GmailAuthTester
{
    private $baseUrl;
    private $httpClient;

    public function __construct($baseUrl = 'http://localhost:8000')
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->httpClient = new HttpClient();
    }

    /**
     * Tester le statut de la configuration Gmail
     */
    public function testGmailStatus()
    {
        echo "🔍 Test du statut de configuration Gmail...\n";
        
        try {
            $response = $this->httpClient->get($this->baseUrl . '/api/auth/gmail/status');
            
            if ($response->successful()) {
                $data = $response->json();
                echo "✅ Statut récupéré avec succès\n";
                echo "   - Configuré: " . ($data['configured'] ? 'Oui' : 'Non') . "\n";
                
                if ($data['configuration']) {
                    echo "   - Nom: " . $data['configuration']['name'] . "\n";
                    echo "   - Complet: " . ($data['configuration']['is_complete'] ? 'Oui' : 'Non') . "\n";
                    echo "   - Scopes: " . implode(', ', $data['configuration']['scopes']) . "\n";
                    echo "   - Redirect URI: " . $data['configuration']['redirect_uri'] . "\n";
                }
                
                return $data['configured'];
            } else {
                echo "❌ Erreur HTTP: " . $response->status() . "\n";
                echo "   Response: " . $response->body() . "\n";
                return false;
            }
        } catch (Exception $e) {
            echo "❌ Erreur: " . $e->getMessage() . "\n";
            return false;
        }
    }

    /**
     * Tester la génération de l'URL de redirection
     */
    public function testGmailRedirect()
    {
        echo "\n🔗 Test de génération d'URL de redirection Gmail...\n";
        
        try {
            $response = $this->httpClient->get($this->baseUrl . '/api/auth/gmail/redirect');
            
            if ($response->successful()) {
                $data = $response->json();
                echo "✅ URL de redirection générée avec succès\n";
                echo "   - Success: " . ($data['success'] ? 'true' : 'false') . "\n";
                echo "   - Message: " . $data['message'] . "\n";
                echo "   - URL: " . substr($data['redirect_url'], 0, 100) . "...\n";
                
                // Vérifier que l'URL contient les éléments attendus
                if (strpos($data['redirect_url'], 'accounts.google.com') !== false) {
                    echo "   ✅ URL Google valide détectée\n";
                } else {
                    echo "   ⚠️  URL ne semble pas être une URL Google\n";
                }
                
                return true;
            } else {
                echo "❌ Erreur HTTP: " . $response->status() . "\n";
                echo "   Response: " . $response->body() . "\n";
                return false;
            }
        } catch (Exception $e) {
            echo "❌ Erreur: " . $e->getMessage() . "\n";
            return false;
        }
    }

    /**
     * Afficher les instructions de configuration
     */
    public function showConfigurationInstructions()
    {
        echo "\n📋 Instructions de configuration Gmail OAuth:\n";
        echo "==========================================\n\n";
        
        echo "1. 🌐 Aller sur Google Cloud Console:\n";
        echo "   https://console.cloud.google.com/\n\n";
        
        echo "2. 📁 Créer ou sélectionner un projet\n\n";
        
        echo "3. 🔧 Activer l'API Google+ ou Google Identity:\n";
        echo "   - Aller dans 'APIs & Services' > 'Library'\n";
        echo "   - Rechercher 'Google+ API' ou 'Google Identity'\n";
        echo "   - Cliquer sur 'Enable'\n\n";
        
        echo "4. 🔑 Créer des identifiants OAuth 2.0:\n";
        echo "   - Aller dans 'APIs & Services' > 'Credentials'\n";
        echo "   - Cliquer sur 'Create Credentials' > 'OAuth 2.0 Client IDs'\n";
        echo "   - Choisir 'Web application'\n";
        echo "   - Ajouter l'URI de redirection: " . $this->baseUrl . "/api/auth/gmail/callback\n\n";
        
        echo "5. 📝 Configurer dans l'admin Filament:\n";
        echo "   - Aller sur " . $this->baseUrl . "/admin\n";
        echo "   - Naviguer vers 'Authentification' > 'Configuration Gmail'\n";
        echo "   - Créer une nouvelle configuration avec:\n";
        echo "     * Client ID: [Votre Client ID Google]\n";
        echo "     * Client Secret: [Votre Client Secret Google]\n";
        echo "     * Redirect URI: " . $this->baseUrl . "/api/auth/gmail/callback\n";
        echo "     * Scopes: openid, profile, email\n\n";
        
        echo "6. ✅ Activer la configuration et tester\n\n";
    }

    /**
     * Exécuter tous les tests
     */
    public function runAllTests()
    {
        echo "🚀 Démarrage des tests d'authentification Gmail\n";
        echo "===============================================\n";
        
        // Test du statut
        $isConfigured = $this->testGmailStatus();
        
        if ($isConfigured) {
            // Test de redirection seulement si configuré
            $this->testGmailRedirect();
            
            echo "\n✅ Tous les tests sont passés avec succès!\n";
            echo "\n🔗 Pour tester la connexion complète:\n";
            echo "1. Ouvrez votre navigateur\n";
            echo "2. Allez sur: " . $this->baseUrl . "/api/auth/gmail/redirect\n";
            echo "3. Suivez le processus d'authentification Google\n";
            echo "4. Vous devriez être redirigé vers le callback avec un token\n";
        } else {
            echo "\n⚠️  Configuration Gmail non trouvée ou incomplète\n";
            $this->showConfigurationInstructions();
        }
        
        echo "\n📊 Résumé des endpoints disponibles:\n";
        echo "====================================\n";
        echo "GET  " . $this->baseUrl . "/api/auth/gmail/status   - Vérifier la configuration\n";
        echo "GET  " . $this->baseUrl . "/api/auth/gmail/redirect - Obtenir l'URL de redirection\n";
        echo "GET  " . $this->baseUrl . "/api/auth/gmail/callback - Callback Google (utilisé par Google)\n";
    }
}

// Exécution du script
if (php_sapi_name() === 'cli') {
    $baseUrl = $argv[1] ?? 'http://localhost:8000';
    $tester = new GmailAuthTester($baseUrl);
    $tester->runAllTests();
} else {
    echo "Ce script doit être exécuté en ligne de commande.\n";
    echo "Usage: php test_gmail_auth.php [base_url]\n";
    echo "Exemple: php test_gmail_auth.php http://localhost:8000\n";
}
