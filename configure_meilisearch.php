<?php

require_once __DIR__ . '/vendor/autoload.php';

// Charger l'application Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Configuration de Meilisearch ===\n\n";

$meilisearchHost = config('scout.meilisearch.host');
$meilisearchKey = config('scout.meilisearch.key');

echo "Host: {$meilisearchHost}\n";
echo "Key: " . substr($meilisearchKey, 0, 10) . "...\n\n";

// Configuration des index
$indexes = [
    'professional_profiles_index' => [
        'filterable' => [
            'city',
            'country', 
            'availability_status',
            'years_of_experience',
            'hourly_rate',
            'rating',
            'completion_percentage',
            'skills',
            'languages'
        ],
        'sortable' => [
            'rating',
            'hourly_rate',
            'years_of_experience',
            'completion_percentage'
        ]
    ],
    'service_offers_index' => [
        'filterable' => [
            'price',
            'status',
            'is_private',
            'categories',
            'rating',
            'views',
            'likes',
            'user_id'
        ],
        'sortable' => [
            'price',
            'rating',
            'views',
            'likes'
        ]
    ],
    'achievements_index' => [
        'filterable' => [
            'organization',
            'date_obtained',
            'professional_profile_id'
        ],
        'sortable' => [
            'date_obtained'
        ]
    ]
];

function configureIndex($host, $key, $indexName, $config) {
    echo "Configuration de l'index: {$indexName}\n";
    
    // Configurer les attributs filtrables
    if (!empty($config['filterable'])) {
        $filterableData = json_encode($config['filterable']);
        $cmd = "curl -X PUT \"{$host}/indexes/{$indexName}/settings/filterable-attributes\" " .
               "-H \"Authorization: Bearer {$key}\" " .
               "-H \"Content-Type: application/json\" " .
               "-d '{$filterableData}'";
        
        echo "  Configuring filterable attributes...\n";
        $result = shell_exec($cmd);
        echo "  Response: " . trim($result) . "\n";
    }
    
    // Configurer les attributs triables
    if (!empty($config['sortable'])) {
        $sortableData = json_encode($config['sortable']);
        $cmd = "curl -X PUT \"{$host}/indexes/{$indexName}/settings/sortable-attributes\" " .
               "-H \"Authorization: Bearer {$key}\" " .
               "-H \"Content-Type: application/json\" " .
               "-d '{$sortableData}'";
        
        echo "  Configuring sortable attributes...\n";
        $result = shell_exec($cmd);
        echo "  Response: " . trim($result) . "\n";
    }
    
    echo "  ✅ Index {$indexName} configuré\n\n";
}

try {
    foreach ($indexes as $indexName => $config) {
        configureIndex($meilisearchHost, $meilisearchKey, $indexName, $config);
    }
    
    echo "🎉 Configuration terminée !\n\n";
    
    echo "Attendez quelques secondes pour que les changements soient appliqués...\n";
    sleep(3);
    
    echo "Vous pouvez maintenant tester les filtres :\n";
    echo "curl \"http://localhost:8000/api/search/professionals?q=Expert&filters%5Bcity%5D=Lyon\"\n";
    echo "curl \"http://localhost:8000/api/search/services?q=Integration&filters%5Bmax_price%5D=3000\"\n";
    echo "curl \"http://localhost:8000/api/search/achievements?q=Certified&filters%5Borganization%5D=Meilisearch\"\n";
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}
