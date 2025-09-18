<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use App\Models\GmailConfiguration;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔧 Configuration Google OAuth\n";
echo "=" . str_repeat("=", 50) . "\n\n";

// Vérifier les arguments
if ($argc < 2) {
    echo "Usage: php switch_google_config.php [local|production]\n\n";
    echo "Configurations disponibles:\n";
    echo "- local      : http://localhost:8000/api/auth/gmail/callback\n";
    echo "- production : https://dev-backend.hi-3d.com/api/auth/gmail/callback\n\n";
    exit(1);
}

$mode = $argv[1];

// URLs de configuration
$configs = [
    'local' => [
        'redirect_uri' => 'http://localhost:8000/api/auth/gmail/callback',
        'frontend_url' => 'http://localhost:3000',
        'description' => 'Configuration locale pour les tests'
    ],
    'production' => [
        'redirect_uri' => 'https://dev-backend.hi-3d.com/api/auth/gmail/callback',
        'frontend_url' => 'https://dev-frontend.hi-3d.com',
        'description' => 'Configuration de production'
    ]
];

if (!isset($configs[$mode])) {
    echo "❌ Mode invalide: $mode\n";
    echo "Modes disponibles: " . implode(', ', array_keys($configs)) . "\n";
    exit(1);
}

$config = $configs[$mode];

try {
    // Récupérer la configuration active
    $gmailConfig = GmailConfiguration::getActiveConfiguration();
    
    if (!$gmailConfig) {
        echo "❌ Aucune configuration Gmail active trouvée\n";
        exit(1);
    }
    
    // Afficher la configuration actuelle
    echo "📋 Configuration actuelle:\n";
    echo "- Redirect URI: " . $gmailConfig->redirect_uri . "\n";
    echo "- Client ID: " . substr($gmailConfig->client_id, 0, 20) . "...\n\n";
    
    // Mettre à jour la configuration
    echo "🔄 Mise à jour vers le mode: $mode\n";
    echo "- " . $config['description'] . "\n";
    echo "- Redirect URI: " . $config['redirect_uri'] . "\n";
    echo "- Frontend URL: " . $config['frontend_url'] . "\n\n";
    
    $gmailConfig->redirect_uri = $config['redirect_uri'];
    $gmailConfig->save();
    
    echo "✅ Configuration mise à jour avec succès!\n\n";
    
    // Instructions selon le mode
    if ($mode === 'local') {
        echo "🧪 Instructions pour les tests locaux:\n";
        echo "1. Démarrer le serveur Laravel:\n";
        echo "   cd hi3dback && php artisan serve\n\n";
        echo "2. Démarrer le frontend:\n";
        echo "   cd hi3dfront && npm start\n\n";
        echo "3. Tester sur: http://localhost:3000/login\n\n";
        echo "⚠️  ATTENTION: Cette configuration ne fonctionnera que localement!\n";
        echo "   Google Console doit être configuré avec l'URI locale.\n\n";
    } else {
        echo "🚀 Configuration de production activée:\n";
        echo "1. Déployer sur: https://dev-backend.hi-3d.com\n";
        echo "2. Frontend sur: https://dev-frontend.hi-3d.com\n";
        echo "3. Tester l'authentification Google\n\n";
        echo "✅ Cette configuration correspond à Google Console.\n\n";
    }
    
    // Afficher les URLs importantes
    echo "🌐 URLs importantes:\n";
    echo "- Redirect URI: " . $config['redirect_uri'] . "\n";
    echo "- Frontend: " . $config['frontend_url'] . "\n";
    echo "- Google OAuth: https://accounts.google.com/o/oauth2/auth\n\n";
    
    // Test de la redirection
    echo "🧪 Test de redirection:\n";
    if ($mode === 'local') {
        echo "curl -I \"http://localhost:8000/auth/gmail/frontend-redirect\"\n";
    } else {
        echo "curl -I \"https://dev-backend.hi-3d.com/auth/gmail/frontend-redirect\"\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n✅ Configuration terminée!\n";
