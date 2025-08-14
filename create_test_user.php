<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\User;
use Illuminate\Support\Facades\Hash;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "👤 Création d'un utilisateur de test\n";
echo "====================================\n\n";

try {
    // Vérifier si l'utilisateur existe déjà
    $existingUser = User::where('email', 'test@hi3d.com')->first();
    
    if ($existingUser) {
        echo "✅ Utilisateur de test existe déjà\n";
        $user = $existingUser;
    } else {
        // Créer un nouvel utilisateur
        $user = User::create([
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@hi3d.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
            'is_professional' => true,
        ]);
        echo "✅ Utilisateur de test créé\n";
    }
    
    echo "📋 Détails de l'utilisateur:\n";
    echo "   - ID: {$user->id}\n";
    echo "   - Email: {$user->email}\n";
    echo "   - Nom: {$user->first_name} {$user->last_name}\n\n";
    
    // Créer un token d'authentification
    $token = $user->createToken('test-token')->plainTextToken;
    
    echo "🔑 Token d'authentification généré:\n";
    echo "   {$token}\n\n";
    
    echo "📝 Utilisez ce token dans vos requêtes API:\n";
    echo "   Authorization: Bearer {$token}\n\n";
    
    echo "🧪 Commandes de test cURL:\n";
    echo "================================\n\n";
    
    // Test 1: Ping
    echo "1. Test de ping:\n";
    echo "curl -X GET \"http://localhost:8000/api/ping\" \\\n";
    echo "  -H \"Authorization: Bearer {$token}\"\n\n";
    
    // Test 2: Liste des fichiers
    echo "2. Liste des fichiers:\n";
    echo "curl -X GET \"http://localhost:8000/api/files\" \\\n";
    echo "  -H \"Authorization: Bearer {$token}\"\n\n";
    
    // Test 3: Upload d'un fichier (vous devrez créer un fichier test.txt)
    echo "3. Upload d'un fichier (créez d'abord un fichier test.txt):\n";
    echo "curl -X POST \"http://localhost:8000/api/files/upload\" \\\n";
    echo "  -H \"Authorization: Bearer {$token}\" \\\n";
    echo "  -F \"files[]=@test.txt\"\n\n";
    
    echo "💡 Conseils:\n";
    echo "- Remplacez localhost:8000 par votre URL de serveur\n";
    echo "- Créez des fichiers de test de différentes tailles\n";
    echo "- Utilisez la collection Postman fournie pour des tests plus avancés\n";
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    exit(1);
}
