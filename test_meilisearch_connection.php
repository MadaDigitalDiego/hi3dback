<?php

require_once __DIR__ . '/vendor/autoload.php';

// Charger l'application Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Test de connexion Meilisearch ===\n\n";

$host = config('scout.meilisearch.host');
$key = config('scout.meilisearch.key');

echo "Host configuré: {$host}\n";
echo "Key configurée: " . substr($key, 0, 10) . "...\n\n";

try {
    $client = new \Meilisearch\Client($host, $key);
    
    echo "Test de connexion...\n";
    $health = $client->health();
    echo "✓ Connexion réussie!\n";
    echo "Status: " . json_encode($health) . "\n\n";
    
    echo "Test des index...\n";
    $indexes = $client->getIndexes();
    echo "Nombre d'index: " . count($indexes->getResults()) . "\n";
    
    foreach ($indexes->getResults() as $index) {
        echo "- Index: {$index->getUid()}\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Erreur de connexion: " . $e->getMessage() . "\n";
    echo "Code d'erreur: " . $e->getCode() . "\n";
    
    if (strpos($e->getMessage(), 'Could not resolve host') !== false) {
        echo "\n🔍 Diagnostic:\n";
        echo "- L'URL Meilisearch semble inaccessible\n";
        echo "- Vérifiez que le serveur cloud Meilisearch est actif\n";
        echo "- Vérifiez votre connexion internet\n";
    }
}
