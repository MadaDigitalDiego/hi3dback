<?php

require_once __DIR__ . '/vendor/autoload.php';

// Charger l'application Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\ProfessionalProfile;
use App\Models\ServiceOffer;
use App\Models\Achievement;
use App\Services\GlobalSearchService;

echo "=== Test de la Recherche Globale avec Meilisearch ===\n\n";

try {
    // 1. Vérifier que les modèles ont le trait Searchable
    echo "1. Vérification des traits Searchable...\n";
    
    $professionalTraits = class_uses(ProfessionalProfile::class);
    $serviceTraits = class_uses(ServiceOffer::class);
    $achievementTraits = class_uses(Achievement::class);
    
    if (in_array('Laravel\Scout\Searchable', $professionalTraits)) {
        echo "   ✓ ProfessionalProfile a le trait Searchable\n";
    } else {
        echo "   ❌ ProfessionalProfile n'a pas le trait Searchable\n";
    }
    
    if (in_array('Laravel\Scout\Searchable', $serviceTraits)) {
        echo "   ✓ ServiceOffer a le trait Searchable\n";
    } else {
        echo "   ❌ ServiceOffer n'a pas le trait Searchable\n";
    }
    
    if (in_array('Laravel\Scout\Searchable', $achievementTraits)) {
        echo "   ✓ Achievement a le trait Searchable\n";
    } else {
        echo "   ❌ Achievement n'a pas le trait Searchable\n";
    }
    
    echo "\n";

    // 2. Créer des données de test si elles n'existent pas
    echo "2. Création de données de test...\n";
    
    // Créer un utilisateur professionnel
    $professional = User::firstOrCreate(
        ['email' => 'john.developer@example.com'],
        [
            'first_name' => 'John',
            'last_name' => 'Developer',
            'password' => bcrypt('password'),
            'is_professional' => true,
            'email_verified_at' => now()
        ]
    );
    echo "   ✓ Utilisateur professionnel créé/trouvé (ID: {$professional->id})\n";

    // Créer un profil professionnel
    $profile = ProfessionalProfile::firstOrCreate(
        ['user_id' => $professional->id],
        [
            'first_name' => 'John',
            'last_name' => 'Developer',
            'email' => 'john.developer@example.com',
            'title' => 'Full Stack Laravel Developer',
            'profession' => 'Web Development',
            'bio' => 'Experienced Laravel developer specializing in modern web applications',
            'city' => 'Paris',
            'country' => 'France',
            'skills' => ['PHP', 'Laravel', 'React', 'Vue.js', 'MySQL'],
            'languages' => ['French', 'English'],
            'years_of_experience' => 5,
            'hourly_rate' => 75.00,
            'completion_percentage' => 85,
            'availability_status' => 'available',
            'rating' => 4.8
        ]
    );
    echo "   ✓ Profil professionnel créé/trouvé (ID: {$profile->id})\n";

    // Créer une offre de service
    $service = ServiceOffer::firstOrCreate(
        ['user_id' => $professional->id, 'title' => 'Laravel Web Application Development'],
        [
            'description' => 'I will create a custom Laravel web application with modern features and best practices',
            'price' => 1500.00,
            'execution_time' => '2 weeks',
            'concepts' => 3,
            'revisions' => 2,
            'status' => 'active',
            'is_private' => false,
            'categories' => ['Web Development', 'Laravel', 'PHP'],
            'views' => 150,
            'likes' => 25,
            'rating' => 4.9
        ]
    );
    echo "   ✓ Offre de service créée/trouvée (ID: {$service->id})\n";

    // Créer une réalisation
    $achievement = Achievement::firstOrCreate(
        ['professional_profile_id' => $profile->id, 'title' => 'Laravel Certified Developer'],
        [
            'organization' => 'Laravel',
            'description' => 'Official Laravel certification demonstrating advanced knowledge of the framework',
            'date_obtained' => now()->subMonths(6),
            'achievement_url' => 'https://laravel.com/certification'
        ]
    );
    echo "   ✓ Réalisation créée/trouvée (ID: {$achievement->id})\n\n";

    // 3. Tester les méthodes toSearchableArray
    echo "3. Test des méthodes toSearchableArray...\n";
    
    $profileArray = $profile->toSearchableArray();
    echo "   ✓ ProfessionalProfile->toSearchableArray() : " . count($profileArray) . " champs\n";
    echo "     - Champs inclus : " . implode(', ', array_keys($profileArray)) . "\n";
    
    $serviceArray = $service->toSearchableArray();
    echo "   ✓ ServiceOffer->toSearchableArray() : " . count($serviceArray) . " champs\n";
    echo "     - Champs inclus : " . implode(', ', array_keys($serviceArray)) . "\n";
    
    $achievementArray = $achievement->toSearchableArray();
    echo "   ✓ Achievement->toSearchableArray() : " . count($achievementArray) . " champs\n";
    echo "     - Champs inclus : " . implode(', ', array_keys($achievementArray)) . "\n\n";

    // 4. Tester le service de recherche globale
    echo "4. Test du GlobalSearchService...\n";
    
    $searchService = new GlobalSearchService();
    
    // Test de recherche simple
    echo "   Test de recherche pour 'Laravel'...\n";
    $results = $searchService->search('Laravel', ['per_page' => 5]);
    
    echo "   ✓ Recherche globale effectuée\n";
    echo "   - Query: {$results['query']}\n";
    echo "   - Total count: {$results['total_count']}\n";
    echo "   - Types de résultats: " . implode(', ', array_keys($results['results_by_type'])) . "\n";
    
    foreach ($results['results_by_type'] as $type => $typeResults) {
        echo "   - {$type}: " . $typeResults->count() . " résultats\n";
    }
    
    echo "\n";

    // 5. Tester les recherches spécifiques
    echo "5. Test des recherches spécifiques...\n";
    
    $professionalResults = $searchService->searchProfessionalProfiles('Developer');
    echo "   ✓ Recherche de professionnels: " . $professionalResults->count() . " résultats\n";
    
    $serviceResults = $searchService->searchServiceOffers('Laravel');
    echo "   ✓ Recherche de services: " . $serviceResults->count() . " résultats\n";
    
    $achievementResults = $searchService->searchAchievements('Laravel');
    echo "   ✓ Recherche de réalisations: " . $achievementResults->count() . " résultats\n";
    
    echo "\n";

    // 6. Tester les suggestions
    echo "6. Test des suggestions...\n";
    
    $suggestions = $searchService->getSuggestions('Lar', 5);
    echo "   ✓ Suggestions pour 'Lar': " . count($suggestions) . " suggestions\n";
    echo "   - Suggestions: " . implode(', ', $suggestions) . "\n\n";

    // 7. Informations pour les tests API
    echo "7. Informations pour les tests API...\n";
    echo "   Base URL: http://localhost:8000/api\n";
    echo "   Exemples de requêtes:\n";
    echo "   - GET /api/search?q=Laravel\n";
    echo "   - GET /api/search/professionals?q=Developer\n";
    echo "   - GET /api/search/services?q=Laravel\n";
    echo "   - GET /api/search/achievements?q=Laravel\n";
    echo "   - GET /api/search/suggestions?q=Lar&limit=5\n";
    echo "   - GET /api/search/stats\n\n";

    // 8. Commandes utiles
    echo "8. Commandes utiles...\n";
    echo "   Indexer les données:\n";
    echo "   - php artisan search:index --fresh\n";
    echo "   - php artisan search:index --model=professional_profiles\n";
    echo "   \n";
    echo "   Vider les index:\n";
    echo "   - php artisan search:flush --confirm\n";
    echo "   - php artisan search:flush --model=service_offers\n";
    echo "   \n";
    echo "   Lancer les tests:\n";
    echo "   - php artisan test --filter=GlobalSearchTest\n\n";

    echo "=== Test terminé avec succès ! ===\n";
    echo "💡 N'oubliez pas de démarrer Meilisearch et d'indexer vos données :\n";
    echo "   docker run -p 7700:7700 getmeili/meilisearch:latest\n";
    echo "   php artisan search:index --fresh\n";

} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    echo "📍 Trace: " . $e->getTraceAsString() . "\n";
}
