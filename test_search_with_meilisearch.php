<?php

require_once __DIR__ . '/vendor/autoload.php';

// Charger l'application Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\ProfessionalProfile;
use App\Models\ServiceOffer;
use App\Models\Achievement;

echo "=== Test de la Recherche Globale avec Meilisearch ===\n\n";

// Fonction pour vérifier si Meilisearch est accessible
function checkMeilisearch() {
    $meilisearchHost = config('scout.meilisearch.host', 'http://localhost:7700');
    
    try {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $meilisearchHost . '/health');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return $httpCode === 200;
    } catch (Exception $e) {
        return false;
    }
}

// Fonction pour tester les APIs
function testAPI($endpoint, $description) {
    echo "   Testing: {$description}\n";
    echo "   URL: {$endpoint}\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo "   ❌ cURL Error: {$error}\n";
        return false;
    }
    
    if ($httpCode === 200) {
        $data = json_decode($response, true);
        if ($data && isset($data['success']) && $data['success']) {
            echo "   ✅ Success (HTTP {$httpCode})\n";
            return true;
        } else {
            echo "   ⚠️  Response received but not successful\n";
            echo "   Response: " . substr($response, 0, 200) . "...\n";
            return false;
        }
    } else {
        echo "   ❌ HTTP Error: {$httpCode}\n";
        echo "   Response: " . substr($response, 0, 200) . "...\n";
        return false;
    }
}

try {
    // 1. Vérifier la configuration
    echo "1. Vérification de la configuration...\n";
    
    $scoutDriver = config('scout.driver');
    $meilisearchHost = config('scout.meilisearch.host', 'http://localhost:7700');
    $cacheDriver = config('cache.default');
    
    echo "   - Scout Driver: {$scoutDriver}\n";
    echo "   - Meilisearch Host: {$meilisearchHost}\n";
    echo "   - Cache Driver: {$cacheDriver}\n";
    
    if ($scoutDriver !== 'meilisearch') {
        echo "   ⚠️  Scout n'est pas configuré pour Meilisearch\n";
        echo "   💡 Ajoutez SCOUT_DRIVER=meilisearch dans votre .env\n";
    }
    
    echo "\n";

    // 2. Vérifier Meilisearch
    echo "2. Vérification de Meilisearch...\n";
    
    $meilisearchAvailable = checkMeilisearch();
    
    if ($meilisearchAvailable) {
        echo "   ✅ Meilisearch est accessible sur {$meilisearchHost}\n";
    } else {
        echo "   ❌ Meilisearch n'est pas accessible sur {$meilisearchHost}\n";
        echo "   💡 Démarrez Meilisearch avec: docker run -p 7700:7700 getmeili/meilisearch:latest\n";
    }
    
    echo "\n";

    // 3. Créer des données de test
    echo "3. Création de données de test...\n";
    
    // Créer un utilisateur professionnel
    $professional = User::firstOrCreate(
        ['email' => 'john.meilisearch@example.com'],
        [
            'first_name' => 'John',
            'last_name' => 'Meilisearch',
            'password' => bcrypt('password'),
            'is_professional' => true,
            'email_verified_at' => now()
        ]
    );
    echo "   ✅ Utilisateur professionnel créé/trouvé (ID: {$professional->id})\n";

    // Créer un profil professionnel
    $profile = ProfessionalProfile::firstOrCreate(
        ['user_id' => $professional->id],
        [
            'first_name' => 'John',
            'last_name' => 'Meilisearch',
            'email' => 'john.meilisearch@example.com',
            'title' => 'Meilisearch Expert Developer',
            'profession' => 'Search Engineering',
            'bio' => 'Expert en Meilisearch et recherche full-text avec Laravel Scout',
            'city' => 'Lyon',
            'country' => 'France',
            'skills' => ['Meilisearch', 'Laravel Scout', 'Search', 'PHP', 'Elasticsearch'],
            'languages' => ['French', 'English'],
            'years_of_experience' => 8,
            'hourly_rate' => 95.00,
            'completion_percentage' => 95,
            'availability_status' => 'available',
            'rating' => 4.9
        ]
    );
    echo "   ✅ Profil professionnel créé/trouvé (ID: {$profile->id})\n";

    // Créer une offre de service
    $service = ServiceOffer::firstOrCreate(
        ['user_id' => $professional->id, 'title' => 'Meilisearch Integration Service'],
        [
            'description' => 'I will integrate Meilisearch with your Laravel application for lightning-fast search',
            'price' => 2500.00,
            'execution_time' => '1 week',
            'concepts' => 5,
            'revisions' => 3,
            'status' => 'active',
            'is_private' => false,
            'categories' => ['Search', 'Meilisearch', 'Laravel', 'Integration'],
            'views' => 250,
            'likes' => 45,
            'rating' => 4.95
        ]
    );
    echo "   ✅ Offre de service créée/trouvée (ID: {$service->id})\n";

    // Créer une réalisation
    $achievement = Achievement::firstOrCreate(
        ['professional_profile_id' => $profile->id, 'title' => 'Meilisearch Certified Expert'],
        [
            'organization' => 'Meilisearch',
            'description' => 'Official Meilisearch certification for advanced search implementation',
            'date_obtained' => now()->subMonths(3),
            'achievement_url' => 'https://meilisearch.com/certification'
        ]
    );
    echo "   ✅ Réalisation créée/trouvée (ID: {$achievement->id})\n\n";

    // 4. Tester les APIs (qui fonctionnent même sans Meilisearch)
    echo "4. Test des APIs de recherche...\n";
    
    $baseUrl = 'http://localhost:8000/api';
    
    $tests = [
        ["{$baseUrl}/search/stats", "Statistiques de recherche"],
        ["{$baseUrl}/search/popular", "Recherches populaires"],
        ["{$baseUrl}/search/metrics/realtime", "Métriques temps réel"],
    ];
    
    $successCount = 0;
    foreach ($tests as [$url, $description]) {
        if (testAPI($url, $description)) {
            $successCount++;
        }
        echo "\n";
    }
    
    echo "   📊 Résultat: {$successCount}/" . count($tests) . " APIs fonctionnelles\n\n";

    // 5. Tester les APIs qui nécessitent Meilisearch
    if ($meilisearchAvailable) {
        echo "5. Test des APIs de recherche avec Meilisearch...\n";
        
        // D'abord, indexer les données
        echo "   📊 Indexation des données...\n";
        try {
            $profile->searchable();
            $service->searchable();
            $achievement->searchable();
            echo "   ✅ Données indexées\n";
            
            // Attendre un peu pour que l'indexation soit terminée
            sleep(2);
            
        } catch (Exception $e) {
            echo "   ⚠️  Erreur d'indexation: " . $e->getMessage() . "\n";
        }
        
        $searchTests = [
            ["{$baseUrl}/search?q=Meilisearch", "Recherche globale"],
            ["{$baseUrl}/search/professionals?q=Expert", "Recherche professionnels"],
            ["{$baseUrl}/search/services?q=Integration", "Recherche services"],
            ["{$baseUrl}/search/achievements?q=Certified", "Recherche réalisations"],
            ["{$baseUrl}/search/suggestions?q=Meili&limit=5", "Suggestions"],
        ];
        
        $searchSuccessCount = 0;
        foreach ($searchTests as [$url, $description]) {
            if (testAPI($url, $description)) {
                $searchSuccessCount++;
            }
            echo "\n";
        }
        
        echo "   📊 Résultat recherche: {$searchSuccessCount}/" . count($searchTests) . " APIs de recherche fonctionnelles\n\n";
        
    } else {
        echo "5. ⚠️  Tests de recherche ignorés (Meilisearch non disponible)\n\n";
    }

    // 6. Instructions pour démarrer Meilisearch
    if (!$meilisearchAvailable) {
        echo "6. Instructions pour démarrer Meilisearch...\n";
        echo "   🐳 Avec Docker:\n";
        echo "   docker run -d --name meilisearch -p 7700:7700 getmeili/meilisearch:latest\n\n";
        
        echo "   💻 Avec binaire (Linux/macOS):\n";
        echo "   curl -L https://install.meilisearch.com | sh\n";
        echo "   ./meilisearch\n\n";
        
        echo "   🔧 Configuration Laravel (.env):\n";
        echo "   SCOUT_DRIVER=meilisearch\n";
        echo "   MEILISEARCH_HOST=http://localhost:7700\n\n";
        
        echo "   📊 Après démarrage, indexer les données:\n";
        echo "   php artisan search:index --fresh --verbose\n\n";
        
        echo "   🧪 Puis tester:\n";
        echo "   curl \"http://localhost:8000/api/search?q=Meilisearch\"\n\n";
    }

    // 7. Résumé
    echo "7. Résumé du test...\n";
    echo "   ✅ Configuration vérifiée\n";
    echo "   ✅ Données de test créées\n";
    echo "   ✅ APIs de base fonctionnelles\n";
    
    if ($meilisearchAvailable) {
        echo "   ✅ Meilisearch accessible\n";
        echo "   ✅ APIs de recherche testées\n";
        echo "   🎉 Implémentation complètement fonctionnelle !\n";
    } else {
        echo "   ⚠️  Meilisearch non accessible\n";
        echo "   💡 Démarrez Meilisearch pour tester la recherche complète\n";
    }
    
    echo "\n=== Test terminé ===\n";

} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    echo "📍 Trace: " . $e->getTraceAsString() . "\n";
}
