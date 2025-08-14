<?php

require_once __DIR__ . '/vendor/autoload.php';

// Charger l'application Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Test de la logique de réindexation ===\n\n";

$models = [
    'App\\Models\\ProfessionalProfile' => 'Profils professionnels',
    'App\\Models\\ServiceOffer' => 'Offres de service',
    'App\\Models\\Achievement' => 'Réalisations'
];

foreach ($models as $model => $name) {
    echo "📊 Test: {$name} ({$model})\n";
    
    try {
        // Vérifier si la classe existe
        if (!class_exists($model)) {
            echo "   ❌ Classe non trouvée\n";
            continue;
        }
        echo "   ✓ Classe trouvée\n";
        
        // Vérifier le trait Searchable
        $modelInstance = new $model;
        if (!method_exists($modelInstance, 'searchableAs')) {
            echo "   ❌ Trait Searchable manquant\n";
            continue;
        }
        echo "   ✓ Trait Searchable présent\n";
        
        // Compter les enregistrements
        $count = $model::count();
        echo "   📈 {$count} enregistrements trouvés\n";
        
        if ($count === 0) {
            echo "   ⚠️  Aucun enregistrement à indexer\n";
            continue;
        }
        
        // Tester l'index name
        $indexName = $modelInstance->searchableAs();
        echo "   🏷️  Index: {$indexName}\n";
        
        // Simuler l'indexation (sans vraiment indexer)
        echo "   🔄 Simulation de l'indexation...\n";
        
        $chunkSize = 10;
        $processed = 0;
        
        $model::chunk($chunkSize, function ($records) use (&$processed) {
            // Simuler le traitement sans appeler searchable()
            $processed += $records->count();
            echo "      ✓ Chunk de {$records->count()} enregistrements traité\n";
        });
        
        echo "   ✅ {$processed}/{$count} enregistrements traités avec succès\n";
        
    } catch (\Exception $e) {
        echo "   ❌ Erreur: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
}

echo "=== Test terminé ===\n";
