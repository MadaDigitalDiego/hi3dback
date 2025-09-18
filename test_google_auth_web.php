<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🧪 Test des routes web Google Auth\n";
echo "=" . str_repeat("=", 50) . "\n\n";

// Test 1: Vérifier que les routes web sont disponibles
echo "📋 Test 1: Vérification des routes web\n";
echo "-" . str_repeat("-", 40) . "\n";

$routes = [
    '/auth/gmail/frontend-redirect',
    '/auth/gmail/frontend-callback'
];

foreach ($routes as $route) {
    try {
        $response = file_get_contents("http://localhost:8000" . $route, false, stream_context_create([
            'http' => [
                'method' => 'GET',
                'ignore_errors' => true,
                'timeout' => 5
            ]
        ]));
        
        if ($response !== false) {
            echo "✅ Route $route accessible\n";
        } else {
            echo "❌ Route $route non accessible\n";
        }
    } catch (Exception $e) {
        echo "⚠️  Route $route - Erreur: " . $e->getMessage() . "\n";
    }
}

echo "\n";

// Test 2: Vérifier la configuration Google
echo "📋 Test 2: Configuration Google OAuth\n";
echo "-" . str_repeat("-", 40) . "\n";

try {
    $response = file_get_contents("http://localhost:8000/api/auth/gmail/status", false, stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => "Accept: application/json\r\n",
            'ignore_errors' => true
        ]
    ]));
    
    if ($response) {
        $data = json_decode($response, true);
        if ($data && isset($data['configured']) && $data['configured']) {
            echo "✅ Configuration Google OAuth active\n";
            echo "📝 Scopes: " . implode(', ', $data['configuration']['scopes']) . "\n";
            echo "🔗 Redirect URI: " . $data['configuration']['redirect_uri'] . "\n";
        } else {
            echo "❌ Configuration Google OAuth inactive\n";
        }
    } else {
        echo "❌ Impossible de vérifier la configuration\n";
    }
} catch (Exception $e) {
    echo "❌ Erreur lors de la vérification: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 3: Vérifier les variables d'environnement
echo "📋 Test 3: Variables d'environnement\n";
echo "-" . str_repeat("-", 40) . "\n";

$envVars = [
    'GOOGLE_CLIENT_ID' => env('GOOGLE_CLIENT_ID'),
    'GOOGLE_CLIENT_SECRET' => env('GOOGLE_CLIENT_SECRET'),
    'GOOGLE_REDIRECT_URI' => env('GOOGLE_REDIRECT_URI'),
    'FRONTEND_URL' => env('FRONTEND_URL', 'http://localhost:3000')
];

foreach ($envVars as $var => $value) {
    if ($value) {
        if ($var === 'GOOGLE_CLIENT_SECRET') {
            echo "✅ $var: " . substr($value, 0, 10) . "...\n";
        } else {
            echo "✅ $var: $value\n";
        }
    } else {
        echo "❌ $var: Non définie\n";
    }
}

echo "\n";

// Instructions de test manuel
echo "🔧 Instructions de test manuel\n";
echo "=" . str_repeat("=", 50) . "\n";
echo "1. Démarrer le serveur Laravel:\n";
echo "   cd hi3dback && php artisan serve\n\n";
echo "2. Démarrer le frontend React:\n";
echo "   cd hi3dfront && npm start\n\n";
echo "3. Aller sur http://localhost:3000/login\n\n";
echo "4. Cliquer sur 'Continue with Google'\n\n";
echo "5. Vérifier le comportement selon les scénarios:\n";
echo "   - Utilisateur inexistant → Notification d'erreur\n";
echo "   - Profil incomplet → Notification d'erreur\n";
echo "   - Profil complet → Connexion réussie\n\n";

echo "🌐 URLs importantes:\n";
echo "- Frontend: http://localhost:3000\n";
echo "- Backend: http://localhost:8000\n";
echo "- Google Redirect: http://localhost:8000/auth/gmail/frontend-redirect\n";
echo "- Google Callback: http://localhost:8000/auth/gmail/frontend-callback\n\n";

echo "✅ Tests terminés!\n";
