<?php

require_once __DIR__ . '/vendor/autoload.php';

// Charger l'application Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Diagnostic Meilisearch Hi3D ===\n\n";

// 1. Configuration actuelle
echo "1. Configuration actuelle:\n";
$host = config('scout.meilisearch.host');
$key = config('scout.meilisearch.key');
$driver = config('scout.driver');

echo "   - Driver Scout: {$driver}\n";
echo "   - Host: {$host}\n";
echo "   - Key: " . substr($key, 0, 10) . "..." . substr($key, -4) . "\n\n";

// 2. Test de connectivité réseau
echo "2. Test de connectivité réseau:\n";
$parsedUrl = parse_url($host);
$hostname = $parsedUrl['host'] ?? 'unknown';

echo "   - Hostname: {$hostname}\n";
echo "   - Test DNS: ";
$ip = gethostbyname($hostname);
if ($ip !== $hostname) {
    echo "✓ Résolu vers {$ip}\n";
} else {
    echo "❌ Échec de résolution DNS\n";
}

// 3. Test de connexion HTTP
echo "\n3. Test de connexion HTTP:\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, rtrim($host, '/') . '/health');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $key,
    'Content-Type: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if (empty($curlError) && $httpCode === 200) {
    echo "   ✓ Connexion réussie (HTTP {$httpCode})\n";
    $healthData = json_decode($response, true);
    echo "   - Status: " . ($healthData['status'] ?? 'unknown') . "\n";
} else {
    echo "   ❌ Connexion échouée\n";
    echo "   - Code HTTP: {$httpCode}\n";
    echo "   - Erreur cURL: {$curlError}\n";
}

// 4. Test des modèles indexables
echo "\n4. Modèles indexables:\n";
$models = [
    'App\\Models\\ProfessionalProfile',
    'App\\Models\\ServiceOffer', 
    'App\\Models\\Achievement'
];

foreach ($models as $model) {
    if (class_exists($model)) {
        $count = $model::count();
        $traits = class_uses($model);
        $hasSearchable = in_array('Laravel\\Scout\\Searchable', $traits);
        echo "   - {$model}: {$count} enregistrements, Searchable: " . ($hasSearchable ? '✓' : '❌') . "\n";
    } else {
        echo "   - {$model}: ❌ Classe non trouvée\n";
    }
}

// 5. Recommandations
echo "\n5. Recommandations:\n";
if (!empty($curlError) || $httpCode !== 200) {
    echo "   🔧 Actions à effectuer:\n";
    echo "   1. Vérifiez que votre serveur Meilisearch cloud est actif\n";
    echo "   2. Vérifiez votre connexion internet\n";
    echo "   3. Contactez votre fournisseur Meilisearch cloud\n";
    echo "   4. Ou configurez un serveur Meilisearch local\n\n";
    
    echo "   📋 Configuration Meilisearch local (Docker):\n";
    echo "   docker run -it --rm -p 7700:7700 getmeili/meilisearch:latest\n";
    echo "   Puis mettez à jour MEILISEARCH_HOST=http://127.0.0.1:7700\n";
} else {
    echo "   ✅ La connexion Meilisearch fonctionne correctement!\n";
    echo "   Vous pouvez maintenant utiliser la réindexation dans l'interface admin.\n";
}

echo "\n=== Fin du diagnostic ===\n";
