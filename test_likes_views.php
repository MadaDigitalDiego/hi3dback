<?php

require_once __DIR__ . '/vendor/autoload.php';

// Charger l'application Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\ProfessionalProfile;
use App\Models\ProfessionalProfileView;
use App\Models\UserFavorite;

echo "=== Test des fonctionnalités Likes et Vues ===\n\n";

try {
    // 1. Créer un utilisateur client
    echo "1. Création d'un utilisateur client...\n";
    $client = User::firstOrCreate(
        ['email' => 'client@test.com'],
        [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'password' => bcrypt('password'),
            'is_professional' => false,
            'email_verified_at' => now()
        ]
    );
    echo "   ✓ Client créé/trouvé avec ID: {$client->id}\n\n";

    // 2. Créer un utilisateur professionnel
    echo "2. Création d'un utilisateur professionnel...\n";
    $professional = User::firstOrCreate(
        ['email' => 'pro@test.com'],
        [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'password' => bcrypt('password'),
            'is_professional' => true,
            'email_verified_at' => now()
        ]
    );
    echo "   ✓ Professionnel créé/trouvé avec ID: {$professional->id}\n\n";

    // 3. Créer un profil professionnel
    echo "3. Création d'un profil professionnel...\n";
    $profile = ProfessionalProfile::firstOrCreate(
        ['user_id' => $professional->id],
        [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'pro@test.com',
            'title' => 'Développeur Web',
            'profession' => 'Développement',
            'bio' => 'Développeur web expérimenté',
            'hourly_rate' => 50.00,
            'years_of_experience' => 5,
            'availability_status' => 'available'
        ]
    );
    echo "   ✓ Profil professionnel créé/trouvé avec ID: {$profile->id}\n\n";

    // 4. Tester les likes
    echo "4. Test des fonctionnalités de likes...\n";
    
    // Vérifier l'état initial
    $initialLikes = $profile->likers()->count();
    echo "   - Likes initiaux: {$initialLikes}\n";
    
    // Liker le profil
    $like = $client->like($profile);
    echo "   ✓ Like ajouté\n";
    
    // Vérifier le nombre de likes
    $newLikes = $profile->likers()->count();
    echo "   - Nouveaux likes: {$newLikes}\n";
    
    // Vérifier si le client a liké le profil
    $hasLiked = $client->hasLiked($profile);
    echo "   - Client a liké: " . ($hasLiked ? 'Oui' : 'Non') . "\n";
    
    // Vérifier si le profil est en favoris
    $isFavorite = $client->hasFavorite($profile);
    echo "   - En favoris: " . ($isFavorite ? 'Oui' : 'Non') . "\n\n";

    // 5. Tester les vues
    echo "5. Test des fonctionnalités de vues...\n";
    
    // Vérifier l'état initial
    $initialViews = $profile->views()->count();
    echo "   - Vues initiales: {$initialViews}\n";
    
    // Enregistrer une vue
    $view = $profile->recordView($client->id, 'test-session-123', '127.0.0.1', 'Test User Agent');
    echo "   ✓ Vue enregistrée\n";
    
    // Vérifier le nombre de vues
    $newViews = $profile->views()->count();
    echo "   - Nouvelles vues: {$newViews}\n";
    
    // Tenter d'enregistrer une vue en double (doit être ignorée)
    $duplicateView = $profile->recordView($client->id, 'test-session-123', '127.0.0.1', 'Test User Agent');
    $finalViews = $profile->views()->count();
    echo "   - Vues après tentative de doublon: {$finalViews} (doit être identique)\n";
    echo "   - Doublon ignoré: " . ($duplicateView === null ? 'Oui' : 'Non') . "\n\n";

    // 6. Tester le unlike
    echo "6. Test du unlike...\n";
    
    // Unliker le profil
    $unlikeResult = $client->unlike($profile);
    echo "   ✓ Unlike effectué\n";
    
    // Vérifier le nombre de likes après unlike
    $finalLikes = $profile->likers()->count();
    echo "   - Likes après unlike: {$finalLikes}\n";
    
    // Vérifier si toujours en favoris (doit être retiré)
    $stillFavorite = $client->hasFavorite($profile);
    echo "   - Encore en favoris: " . ($stillFavorite ? 'Oui' : 'Non') . "\n\n";

    // 7. Statistiques finales
    echo "7. Statistiques finales...\n";
    echo "   - Total likes: " . $profile->getTotalLikesAttribute() . "\n";
    echo "   - Total vues: " . $profile->getTotalViewsAttribute() . "\n";
    echo "   - Score de popularité: " . $profile->getPopularityScore() . "\n";

    echo "\n=== Test terminé avec succès ! ===\n";

} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
