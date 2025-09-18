<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\User;
use App\Models\ClientProfile;
use App\Models\ProfessionalProfile;
use App\Services\GmailAuthService;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🧪 Test de l'authentification Google - Scénarios métier\n";
echo "=" . str_repeat("=", 60) . "\n\n";

// Test 1: Utilisateur inexistant
echo "📋 Test 1: Utilisateur inexistant\n";
echo "-" . str_repeat("-", 40) . "\n";

$mockGoogleUser = new class {
    public function getEmail() { return 'inexistant@example.com'; }
    public function getName() { return 'Utilisateur Inexistant'; }
    public function getId() { return '123456789'; }
};

$service = new GmailAuthService();

try {
    // Simuler le processus avec un utilisateur inexistant
    $user = User::where('email', $mockGoogleUser->getEmail())->first();
    
    if (!$user) {
        echo "✅ Utilisateur non trouvé comme attendu\n";
        echo "📝 Réponse attendue: Erreur 'user_not_found'\n";
    } else {
        echo "❌ Erreur: Utilisateur trouvé alors qu'il ne devrait pas exister\n";
    }
} catch (Exception $e) {
    echo "❌ Erreur lors du test 1: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 2: Utilisateur existant avec profil incomplet
echo "📋 Test 2: Utilisateur existant avec profil incomplet\n";
echo "-" . str_repeat("-", 40) . "\n";

try {
    // Créer un utilisateur de test avec profil incomplet
    $testUser = User::firstOrCreate(
        ['email' => 'test.incomplet@example.com'],
        [
            'first_name' => 'Test',
            'last_name' => 'Incomplet',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
            'is_professional' => false,
            'profile_completed' => false, // Profil marqué comme incomplet
        ]
    );

    // Créer un profil client incomplet
    $clientProfile = ClientProfile::firstOrCreate(
        ['user_id' => $testUser->id],
        [
            'first_name' => 'Test',
            'last_name' => 'Incomplet',
            'email' => 'test.incomplet@example.com',
            'completion_percentage' => 30, // Moins de 60% requis
        ]
    );

    echo "✅ Utilisateur de test créé avec profil incomplet\n";
    echo "📊 Completion: {$clientProfile->completion_percentage}% (< 60% requis)\n";
    echo "📝 Réponse attendue: Erreur 'profile_incomplete'\n";

} catch (Exception $e) {
    echo "❌ Erreur lors du test 2: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 3: Utilisateur existant avec profil complet
echo "📋 Test 3: Utilisateur existant avec profil complet\n";
echo "-" . str_repeat("-", 40) . "\n";

try {
    // Créer un utilisateur de test avec profil complet
    $testUserComplete = User::firstOrCreate(
        ['email' => 'test.complet@example.com'],
        [
            'first_name' => 'Test',
            'last_name' => 'Complet',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
            'is_professional' => false,
            'profile_completed' => true, // Profil marqué comme complet
        ]
    );

    // Créer un profil client complet
    $clientProfileComplete = ClientProfile::firstOrCreate(
        ['user_id' => $testUserComplete->id],
        [
            'first_name' => 'Test',
            'last_name' => 'Complet',
            'email' => 'test.complet@example.com',
            'phone' => '+33123456789',
            'completion_percentage' => 80, // Plus de 60% requis
        ]
    );

    echo "✅ Utilisateur de test créé avec profil complet\n";
    echo "📊 Completion: {$clientProfileComplete->completion_percentage}% (>= 60% requis)\n";
    echo "📝 Réponse attendue: Connexion réussie avec token\n";

} catch (Exception $e) {
    echo "❌ Erreur lors du test 3: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 4: Utilisateur professionnel avec profil incomplet
echo "📋 Test 4: Utilisateur professionnel avec profil incomplet\n";
echo "-" . str_repeat("-", 40) . "\n";

try {
    // Créer un utilisateur professionnel de test avec profil incomplet
    $testUserPro = User::firstOrCreate(
        ['email' => 'test.pro.incomplet@example.com'],
        [
            'first_name' => 'Test',
            'last_name' => 'Pro Incomplet',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
            'is_professional' => true,
            'profile_completed' => false,
        ]
    );

    // Créer un profil professionnel incomplet
    $proProfile = ProfessionalProfile::firstOrCreate(
        ['user_id' => $testUserPro->id],
        [
            'first_name' => 'Test',
            'last_name' => 'Pro Incomplet',
            'email' => 'test.pro.incomplet@example.com',
            'completion_percentage' => 50, // Moins de 80% requis pour les pros
        ]
    );

    echo "✅ Utilisateur professionnel créé avec profil incomplet\n";
    echo "📊 Completion: {$proProfile->completion_percentage}% (< 80% requis pour les pros)\n";
    echo "📝 Réponse attendue: Erreur 'profile_incomplete'\n";

} catch (Exception $e) {
    echo "❌ Erreur lors du test 4: " . $e->getMessage() . "\n";
}

echo "\n";

// Résumé des tests
echo "📊 Résumé des scénarios de test\n";
echo "=" . str_repeat("=", 60) . "\n";
echo "1. ❌ Utilisateur inexistant → Erreur 'user_not_found'\n";
echo "2. ⚠️  Profil client incomplet → Erreur 'profile_incomplete'\n";
echo "3. ✅ Profil client complet → Connexion réussie\n";
echo "4. ⚠️  Profil pro incomplet → Erreur 'profile_incomplete'\n";
echo "\n";

echo "🔧 Pour tester manuellement:\n";
echo "1. Démarrer le serveur Laravel: php artisan serve\n";
echo "2. Démarrer le frontend React: npm start\n";
echo "3. Aller sur /login et cliquer sur 'Continue with Google'\n";
echo "4. Tester avec les emails ci-dessus\n";
echo "\n";

echo "✅ Tests de configuration terminés!\n";
