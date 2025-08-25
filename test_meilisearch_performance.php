<?php

require_once __DIR__ . '/vendor/autoload.php';

// Charger l'application Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Test des Performances Meilisearch Hi3D ===\n\n";

$baseUrl = 'http://localhost:8000/api/explorer';

// Fonction pour faire un appel API et mesurer le temps
function testApiCall($url, $description) {
    $startTime = microtime(true);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $totalTime = microtime(true) - $startTime;
    
    if ($httpCode === 200 && $response) {
        $data = json_decode($response, true);
        
        echo "✅ {$description}\n";
        echo "   URL: {$url}\n";
        echo "   HTTP Code: {$httpCode}\n";
        echo "   Total Time: " . round($totalTime * 1000, 2) . " ms\n";
        
        if (isset($data['performance'])) {
            $perf = $data['performance'];
            echo "   API Response Time: " . ($perf['total_execution_time_ms'] ?? 'N/A') . " ms\n";
            echo "   Search Method: " . ($perf['search_method'] ?? 'N/A') . "\n";
            
            if (isset($perf['meilisearch_time_ms'])) {
                echo "   Meilisearch Time: " . $perf['meilisearch_time_ms'] . " ms\n";
                echo "   Search Query: '" . ($perf['search_query'] ?? 'N/A') . "'\n";
            }
        }
        
        if (isset($data['pagination'])) {
            $pagination = $data['pagination'];
            echo "   Results: " . ($pagination['total'] ?? 0) . " total\n";
        }
        
        echo "\n";
        return $data;
    } else {
        echo "❌ {$description}\n";
        echo "   URL: {$url}\n";
        echo "   HTTP Code: {$httpCode}\n";
        echo "   Error: " . ($response ?: 'No response') . "\n\n";
        return null;
    }
}

// 1. Test des statistiques de recherche
echo "1. Test des statistiques de configuration...\n";
$stats = testApiCall($baseUrl . '/search-stats', 'Statistiques Meilisearch');

if ($stats && isset($stats['stats']['configuration'])) {
    $config = $stats['stats']['configuration'];
    echo "   Configuration détectée:\n";
    echo "   - Driver Scout: " . ($config['scout_driver'] ?? 'N/A') . "\n";
    echo "   - Host Meilisearch: " . ($config['meilisearch_host'] ?? 'N/A') . "\n";
    echo "   - Meilisearch configuré: " . ($config['meilisearch_configured'] ? 'Oui' : 'Non') . "\n";
    
    if (isset($stats['stats']['performance']['meilisearch_available'])) {
        echo "   - Meilisearch disponible: " . ($stats['stats']['performance']['meilisearch_available'] ? 'Oui' : 'Non') . "\n";
    }
    echo "\n";
}

// 2. Test de recherche de services avec Meilisearch
echo "2. Test de recherche de services avec Meilisearch...\n";
$searchQueries = ['web', 'développement', 'design', 'Laravel'];

foreach ($searchQueries as $query) {
    $url = $baseUrl . '/services?search=' . urlencode($query) . '&per_page=3';
    testApiCall($url, "Recherche services: '{$query}'");
}

// 3. Test de filtrage de services sans recherche (Eloquent)
echo "3. Test de filtrage de services avec Eloquent...\n";
$filters = [
    'min_price=100&max_price=1000',
    'sort_by=price_asc',
    'sort_by=rating'
];

foreach ($filters as $filter) {
    $url = $baseUrl . '/services?' . $filter . '&per_page=3';
    testApiCall($url, "Filtrage services: {$filter}");
}

// 4. Test de recherche de professionnels avec Meilisearch
echo "4. Test de recherche de professionnels avec Meilisearch...\n";
$profSearchQueries = ['développeur', 'designer', 'consultant'];

foreach ($profSearchQueries as $query) {
    $url = $baseUrl . '/professionals?search=' . urlencode($query) . '&per_page=3';
    testApiCall($url, "Recherche professionnels: '{$query}'");
}

// 5. Test de filtrage de professionnels sans recherche (Eloquent)
echo "5. Test de filtrage de professionnels avec Eloquent...\n";
$profFilters = [
    'city=Paris',
    'min_rate=50&max_rate=150',
    'availability=available'
];

foreach ($profFilters as $filter) {
    $url = $baseUrl . '/professionals?' . $filter . '&per_page=3';
    testApiCall($url, "Filtrage professionnels: {$filter}");
}

// 6. Comparaison des performances
echo "6. Comparaison des performances...\n";
echo "   Recherche avec Meilisearch vs Filtrage avec Eloquent\n";
echo "   - Meilisearch: Optimisé pour la recherche textuelle full-text\n";
echo "   - Eloquent: Optimisé pour les filtres structurés sur la base de données\n";
echo "   - Les temps incluent la latence réseau et le traitement Laravel\n\n";

echo "=== Test terminé ===\n";
echo "Consultez la documentation: docs/meilisearch-performance-integration.md\n";
