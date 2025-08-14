<?php

require_once __DIR__ . '/vendor/autoload.php';

// Charger l'application Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\ServiceOffer;

echo "=== Debug Meilisearch ===\n\n";

$meilisearchHost = config('scout.meilisearch.host');
$meilisearchKey = config('scout.meilisearch.key');

echo "Host: {$meilisearchHost}\n";
echo "Key: " . substr($meilisearchKey, 0, 10) . "...\n\n";

// 1. Vérifier les données dans la base de données
echo "1. Données dans la base de données:\n";
$services = ServiceOffer::all();
foreach ($services as $service) {
    echo "  Service ID: {$service->id}\n";
    echo "  Title: {$service->title}\n";
    echo "  Price: {$service->price} (type: " . gettype($service->price) . ")\n";
    echo "  Status: {$service->status}\n";
    echo "  Categories: " . json_encode($service->categories) . "\n";
    echo "  Searchable Array:\n";
    $searchableArray = $service->toSearchableArray();
    foreach ($searchableArray as $key => $value) {
        echo "    {$key}: " . json_encode($value) . " (type: " . gettype($value) . ")\n";
    }
    echo "\n";
}

// 2. Tester la recherche directe avec Scout
echo "2. Test de recherche Scout:\n";
try {
    $results = ServiceOffer::search('Integration')->get();
    echo "  Résultats trouvés: " . $results->count() . "\n";
    foreach ($results as $result) {
        echo "    - {$result->title} (Prix: {$result->price})\n";
    }
} catch (Exception $e) {
    echo "  Erreur: " . $e->getMessage() . "\n";
}

// 3. Tester les filtres Scout
echo "\n3. Test de filtres Scout:\n";
try {
    // Test avec filtre de prix
    $results = ServiceOffer::search('Integration')
        ->where('price', '<=', 3000)
        ->get();
    echo "  Résultats avec prix <= 3000: " . $results->count() . "\n";
    
    $results = ServiceOffer::search('Integration')
        ->where('price', '<=', 2500)
        ->get();
    echo "  Résultats avec prix <= 2500: " . $results->count() . "\n";
    
    $results = ServiceOffer::search('Integration')
        ->where('price', '<=', 2000)
        ->get();
    echo "  Résultats avec prix <= 2000: " . $results->count() . "\n";
    
} catch (Exception $e) {
    echo "  Erreur: " . $e->getMessage() . "\n";
}

// 4. Tester avec l'API Meilisearch directement
echo "\n4. Test API Meilisearch directe:\n";
try {
    // Recherche simple
    $cmd = "curl -X POST \"{$meilisearchHost}/indexes/service_offers_index/search\" " .
           "-H \"Authorization: Bearer {$meilisearchKey}\" " .
           "-H \"Content-Type: application/json\" " .
           "-d '{\"q\": \"Integration\"}'";
    
    echo "  Recherche simple:\n";
    $result = shell_exec($cmd);
    $data = json_decode($result, true);
    if ($data && isset($data['hits'])) {
        echo "    Résultats: " . count($data['hits']) . "\n";
        foreach ($data['hits'] as $hit) {
            echo "      - {$hit['title']} (Prix: {$hit['price']})\n";
        }
    } else {
        echo "    Erreur ou pas de résultats: " . $result . "\n";
    }
    
    // Recherche avec filtre
    $cmd = "curl -X POST \"{$meilisearchHost}/indexes/service_offers_index/search\" " .
           "-H \"Authorization: Bearer {$meilisearchKey}\" " .
           "-H \"Content-Type: application/json\" " .
           "-d '{\"q\": \"Integration\", \"filter\": \"price <= 3000\"}'";
    
    echo "\n  Recherche avec filtre prix <= 3000:\n";
    $result = shell_exec($cmd);
    $data = json_decode($result, true);
    if ($data && isset($data['hits'])) {
        echo "    Résultats: " . count($data['hits']) . "\n";
        foreach ($data['hits'] as $hit) {
            echo "      - {$hit['title']} (Prix: {$hit['price']})\n";
        }
    } else {
        echo "    Erreur ou pas de résultats: " . $result . "\n";
    }
    
} catch (Exception $e) {
    echo "  Erreur: " . $e->getMessage() . "\n";
}

echo "\n=== Debug terminé ===\n";
