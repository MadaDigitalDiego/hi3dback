<?php

require_once __DIR__ . '/vendor/autoload.php';

// Charger l'application Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\ProfessionalProfile;

echo "=== Test des APIs Likes et Vues ===\n\n";

try {
    // 1. Récupérer les utilisateurs de test
    $client = User::where('email', 'client@test.com')->first();
    $profile = ProfessionalProfile::first();
    
    if (!$client || !$profile) {
        echo "❌ Utilisateurs de test non trouvés. Exécutez d'abord test_likes_views.php\n";
        exit(1);
    }
    
    // 2. Créer un token d'authentification pour le client
    $token = $client->createToken('test-token')->plainTextToken;
    echo "1. Token créé pour le client: {$client->email}\n";
    echo "   Token: {$token}\n\n";
    
    // 3. URL de base de l'API
    $baseUrl = 'http://localhost:8000/api'; // Ajustez selon votre configuration
    
    echo "2. URLs des APIs à tester:\n";
    echo "   - POST {$baseUrl}/professionals/{$profile->id}/like\n";
    echo "   - DELETE {$baseUrl}/professionals/{$profile->id}/like\n";
    echo "   - POST {$baseUrl}/professionals/{$profile->id}/like/toggle\n";
    echo "   - GET {$baseUrl}/professionals/{$profile->id}/like/status\n";
    echo "   - POST {$baseUrl}/professionals/{$profile->id}/view\n";
    echo "   - GET {$baseUrl}/professionals/{$profile->id}/view/stats\n";
    echo "   - GET {$baseUrl}/professionals/{$profile->id}/view/status\n\n";
    
    echo "3. Commandes cURL pour tester:\n\n";
    
    // Commandes cURL pour les likes
    echo "# Liker un profil\n";
    echo "curl -X POST \"{$baseUrl}/professionals/{$profile->id}/like\" \\\n";
    echo "  -H \"Authorization: Bearer {$token}\" \\\n";
    echo "  -H \"Content-Type: application/json\"\n\n";
    
    echo "# Vérifier le statut du like\n";
    echo "curl -X GET \"{$baseUrl}/professionals/{$profile->id}/like/status\" \\\n";
    echo "  -H \"Authorization: Bearer {$token}\" \\\n";
    echo "  -H \"Content-Type: application/json\"\n\n";
    
    echo "# Toggle like\n";
    echo "curl -X POST \"{$baseUrl}/professionals/{$profile->id}/like/toggle\" \\\n";
    echo "  -H \"Authorization: Bearer {$token}\" \\\n";
    echo "  -H \"Content-Type: application/json\"\n\n";
    
    echo "# Unliker un profil\n";
    echo "curl -X DELETE \"{$baseUrl}/professionals/{$profile->id}/like\" \\\n";
    echo "  -H \"Authorization: Bearer {$token}\" \\\n";
    echo "  -H \"Content-Type: application/json\"\n\n";
    
    // Commandes cURL pour les vues
    echo "# Enregistrer une vue\n";
    echo "curl -X POST \"{$baseUrl}/professionals/{$profile->id}/view\" \\\n";
    echo "  -H \"Content-Type: application/json\"\n\n";
    
    echo "# Vérifier les statistiques de vues\n";
    echo "curl -X GET \"{$baseUrl}/professionals/{$profile->id}/view/stats\" \\\n";
    echo "  -H \"Content-Type: application/json\"\n\n";
    
    echo "# Vérifier le statut de vue\n";
    echo "curl -X GET \"{$baseUrl}/professionals/{$profile->id}/view/status\" \\\n";
    echo "  -H \"Content-Type: application/json\"\n\n";
    
    echo "4. Test automatique avec file_get_contents (si le serveur est démarré):\n\n";
    
    // Test simple avec file_get_contents
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => "Content-Type: application/json\r\n",
            'timeout' => 5
        ]
    ]);
    
    // Test de l'endpoint de santé
    $healthUrl = $baseUrl . '/health-check';
    echo "   Test de santé: {$healthUrl}\n";
    
    $result = @file_get_contents($healthUrl, false, $context);
    if ($result !== false) {
        echo "   ✓ API accessible: " . $result . "\n";
        
        // Test de l'endpoint de vue (public)
        $viewUrl = $baseUrl . "/professionals/{$profile->id}/view/stats";
        echo "   Test des stats de vues: {$viewUrl}\n";
        
        $viewResult = @file_get_contents($viewUrl, false, $context);
        if ($viewResult !== false) {
            echo "   ✓ Stats de vues: " . $viewResult . "\n";
        } else {
            echo "   ❌ Erreur lors de l'accès aux stats de vues\n";
        }
    } else {
        echo "   ❌ API non accessible. Assurez-vous que le serveur Laravel est démarré:\n";
        echo "       php artisan serve\n";
    }
    
    echo "\n=== Informations de test ===\n";
    echo "Client ID: {$client->id}\n";
    echo "Client Email: {$client->email}\n";
    echo "Profile ID: {$profile->id}\n";
    echo "Profile Title: {$profile->title}\n";
    echo "Token: {$token}\n";
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
