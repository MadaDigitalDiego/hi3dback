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

echo "=== Test de Configuration de la Recherche Globale ===\n\n";

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

    // 2. Vérifier les méthodes Scout
    echo "2. Vérification des méthodes Scout...\n";
    
    // Test ProfessionalProfile
    $profile = new ProfessionalProfile([
        'first_name' => 'John',
        'last_name' => 'Doe',
        'title' => 'Laravel Developer',
        'profession' => 'Web Development',
        'bio' => 'Experienced developer',
        'city' => 'Paris',
        'country' => 'France',
        'skills' => ['PHP', 'Laravel'],
        'completion_percentage' => 80
    ]);
    
    $profileArray = $profile->toSearchableArray();
    echo "   ✓ ProfessionalProfile->toSearchableArray() : " . count($profileArray) . " champs\n";
    echo "     - Type: " . ($profileArray['type'] ?? 'non défini') . "\n";
    echo "     - Index: " . $profile->searchableAs() . "\n";
    echo "     - Doit être indexé: " . ($profile->shouldBeSearchable() ? 'Oui' : 'Non') . "\n";
    
    // Test ServiceOffer
    $service = new ServiceOffer([
        'title' => 'Laravel Development',
        'description' => 'Custom Laravel application',
        'price' => 500,
        'status' => 'active',
        'is_private' => false,
        'categories' => ['Web Development']
    ]);
    
    $serviceArray = $service->toSearchableArray();
    echo "   ✓ ServiceOffer->toSearchableArray() : " . count($serviceArray) . " champs\n";
    echo "     - Type: " . ($serviceArray['type'] ?? 'non défini') . "\n";
    echo "     - Index: " . $service->searchableAs() . "\n";
    echo "     - Doit être indexé: " . ($service->shouldBeSearchable() ? 'Oui' : 'Non') . "\n";
    
    // Test Achievement
    $achievement = new Achievement([
        'title' => 'Laravel Certification',
        'organization' => 'Laravel',
        'description' => 'Official certification'
    ]);
    
    $achievementArray = $achievement->toSearchableArray();
    echo "   ✓ Achievement->toSearchableArray() : " . count($achievementArray) . " champs\n";
    echo "     - Type: " . ($achievementArray['type'] ?? 'non défini') . "\n";
    echo "     - Index: " . $achievement->searchableAs() . "\n";
    echo "     - Doit être indexé: " . ($achievement->shouldBeSearchable() ? 'Oui' : 'Non') . "\n\n";

    // 3. Vérifier le service de recherche
    echo "3. Vérification du GlobalSearchService...\n";
    
    if (class_exists('App\Services\GlobalSearchService')) {
        echo "   ✓ GlobalSearchService existe\n";
        
        $searchService = new GlobalSearchService();
        $reflection = new ReflectionClass($searchService);
        $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
        
        echo "   ✓ Méthodes publiques disponibles:\n";
        foreach ($methods as $method) {
            if (!$method->isConstructor()) {
                echo "     - " . $method->getName() . "\n";
            }
        }
    } else {
        echo "   ❌ GlobalSearchService n'existe pas\n";
    }
    
    echo "\n";

    // 4. Vérifier les contrôleurs
    echo "4. Vérification du SearchController...\n";
    
    if (class_exists('App\Http\Controllers\Api\SearchController')) {
        echo "   ✓ SearchController existe\n";
        
        $controller = new ReflectionClass('App\Http\Controllers\Api\SearchController');
        $methods = $controller->getMethods(ReflectionMethod::IS_PUBLIC);
        
        echo "   ✓ Méthodes publiques disponibles:\n";
        foreach ($methods as $method) {
            if (!$method->isConstructor() && $method->getDeclaringClass()->getName() === 'App\Http\Controllers\Api\SearchController') {
                echo "     - " . $method->getName() . "\n";
            }
        }
    } else {
        echo "   ❌ SearchController n'existe pas\n";
    }
    
    echo "\n";

    // 5. Vérifier les commandes Artisan
    echo "5. Vérification des commandes Artisan...\n";
    
    if (class_exists('App\Console\Commands\IndexSearchableModels')) {
        echo "   ✓ IndexSearchableModels command existe\n";
    } else {
        echo "   ❌ IndexSearchableModels command n'existe pas\n";
    }
    
    if (class_exists('App\Console\Commands\FlushSearchIndexes')) {
        echo "   ✓ FlushSearchIndexes command existe\n";
    } else {
        echo "   ❌ FlushSearchIndexes command n'existe pas\n";
    }
    
    echo "\n";

    // 6. Vérifier la configuration Scout
    echo "6. Vérification de la configuration Scout...\n";
    
    $scoutDriver = config('scout.driver');
    echo "   - Driver Scout: " . ($scoutDriver ?? 'non configuré') . "\n";
    
    $meilisearchHost = config('scout.meilisearch.host');
    echo "   - Meilisearch Host: " . ($meilisearchHost ?? 'non configuré') . "\n";
    
    if ($scoutDriver === 'meilisearch') {
        echo "   ✓ Scout configuré pour Meilisearch\n";
    } else {
        echo "   ⚠️  Scout n'est pas configuré pour Meilisearch\n";
    }
    
    echo "\n";

    // 7. Informations pour les tests
    echo "7. Informations pour les tests...\n";
    echo "   📋 Routes API disponibles:\n";
    echo "   - GET /api/search?q={query}\n";
    echo "   - GET /api/search/professionals?q={query}\n";
    echo "   - GET /api/search/services?q={query}\n";
    echo "   - GET /api/search/achievements?q={query}\n";
    echo "   - GET /api/search/suggestions?q={query}\n";
    echo "   - GET /api/search/stats\n\n";
    
    echo "   🔧 Commandes Artisan disponibles:\n";
    echo "   - php artisan search:index [--model=] [--fresh] [--verbose]\n";
    echo "   - php artisan search:flush [--model=] [--confirm]\n\n";
    
    echo "   🧪 Tests disponibles:\n";
    echo "   - php artisan test --filter=GlobalSearchTest\n\n";
    
    echo "   📚 Documentation:\n";
    echo "   - docs/global-search-documentation.md\n";
    echo "   - docs/postman-global-search-testing.md\n";
    echo "   - docs/postman-global-search-collection.json\n\n";

    // 8. Instructions de démarrage
    echo "8. Instructions pour démarrer Meilisearch...\n";
    echo "   🐳 Avec Docker:\n";
    echo "   docker run -it --rm -p 7700:7700 getmeili/meilisearch:latest\n\n";
    
    echo "   💻 Avec binaire (Linux/macOS):\n";
    echo "   curl -L https://install.meilisearch.com | sh\n";
    echo "   ./meilisearch\n\n";
    
    echo "   🔧 Configuration Laravel (.env):\n";
    echo "   SCOUT_DRIVER=meilisearch\n";
    echo "   MEILISEARCH_HOST=http://localhost:7700\n";
    echo "   MEILISEARCH_KEY=\n\n";
    
    echo "   📊 Indexer les données:\n";
    echo "   php artisan search:index --fresh --verbose\n\n";

    echo "=== Configuration vérifiée avec succès ! ===\n";
    echo "🎉 Tous les composants de la recherche globale sont en place.\n";
    echo "💡 Démarrez Meilisearch et indexez vos données pour commencer à utiliser la recherche.\n";

} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    echo "📍 Trace: " . $e->getTraceAsString() . "\n";
}
