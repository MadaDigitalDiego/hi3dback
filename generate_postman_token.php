<?php

require_once __DIR__ . '/vendor/autoload.php';

// Charger l'application Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;

echo "=== Générateur de Token Postman ===\n\n";

try {
    // Récupérer ou créer l'utilisateur de test
    $client = User::where('email', 'client@test.com')->first();

    if (!$client) {
        echo "❌ Utilisateur client@test.com non trouvé.\n";
        echo "💡 Exécutez d'abord: php test_likes_views.php\n";
        exit(1);
    }

    echo "✅ Utilisateur trouvé: {$client->email} (ID: {$client->id})\n\n";

    // Supprimer les anciens tokens
    $client->tokens()->delete();
    echo "🗑️  Anciens tokens supprimés\n";

    // Créer un nouveau token
    $token = $client->createToken('postman-testing-token')->plainTextToken;
    echo "🔑 Nouveau token généré\n\n";

    // Afficher les informations pour Postman
    echo "=== INFORMATIONS POUR POSTMAN ===\n\n";
    echo "📋 Variables d'environnement à configurer :\n";
    echo "┌─────────────────────────────────────────────────────────────────┐\n";
    echo "│ Variable Name          │ Value                                  │\n";
    echo "├─────────────────────────────────────────────────────────────────┤\n";
    echo "│ base_url              │ http://localhost:8000/api              │\n";
    echo "│ auth_token            │ {$token} │\n";
    echo "│ professional_profile_id│ 1                                      │\n";
    echo "│ user_email            │ client@test.com                        │\n";
    echo "└─────────────────────────────────────────────────────────────────┘\n\n";

    echo "🔗 Token complet à copier :\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo $token . "\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

    echo "📝 Instructions :\n";
    echo "1. Ouvrir Postman\n";
    echo "2. Sélectionner l'environnement 'Likes & Views Environment'\n";
    echo "3. Modifier la variable 'auth_token'\n";
    echo "4. Coller le token ci-dessus\n";
    echo "5. Sauvegarder l'environnement\n\n";

    echo "🧪 Test rapide :\n";
    echo "curl -X GET \"http://localhost:8000/api/professionals/1/like/status\" \\\n";
    echo "  -H \"Authorization: Bearer {$token}\" \\\n";
    echo "  -H \"Content-Type: application/json\"\n\n";

    echo "✅ Token généré avec succès !\n";
    echo "⏰ Valide jusqu'à suppression manuelle ou régénération\n";

} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    echo "📍 Trace: " . $e->getTraceAsString() . "\n";
}
