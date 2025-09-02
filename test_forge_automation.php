<?php

/**
 * Script de test pour l'automatisation Meilisearch avec Laravel Forge
 * 
 * Usage: php test_forge_automation.php
 */

require_once __DIR__ . '/vendor/autoload.php';

// Charger l'application Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\ProfessionalProfile;
use App\Models\ServiceOffer;
use App\Models\Achievement;
use App\Jobs\IndexSearchableModelsJob;
use Illuminate\Support\Facades\Http;

echo "🧪 Test de l'automatisation Meilisearch avec Laravel Forge\n";
echo "=" . str_repeat("=", 60) . "\n\n";

// 1. Test de la configuration
echo "1. 🔧 Vérification de la configuration...\n";
$meilisearchHost = config('scout.meilisearch.host');
$meilisearchKey = config('scout.meilisearch.key');
$scoutDriver = config('scout.driver');

echo "   Scout Driver: {$scoutDriver}\n";
echo "   Meilisearch Host: {$meilisearchHost}\n";
echo "   Meilisearch Key: " . (empty($meilisearchKey) ? 'Non configuré' : substr($meilisearchKey, 0, 10) . '...') . "\n";

if ($scoutDriver !== 'meilisearch') {
    echo "   ❌ Scout driver n'est pas configuré sur 'meilisearch'\n";
} else {
    echo "   ✅ Configuration Scout OK\n";
}

// 2. Test de connexion Meilisearch
echo "\n2. 🌐 Test de connexion Meilisearch...\n";
try {
    $response = Http::timeout(10)->get("{$meilisearchHost}/health");
    if ($response->successful()) {
        echo "   ✅ Meilisearch est accessible\n";
        $healthData = $response->json();
        echo "   Status: " . ($healthData['status'] ?? 'unknown') . "\n";
    } else {
        echo "   ❌ Meilisearch n'est pas accessible (HTTP {$response->status()})\n";
    }
} catch (Exception $e) {
    echo "   ❌ Erreur de connexion: " . $e->getMessage() . "\n";
}

// 3. Vérification des modèles searchable
echo "\n3. 📊 Vérification des modèles searchable...\n";
$models = [
    'ProfessionalProfile' => ProfessionalProfile::class,
    'ServiceOffer' => ServiceOffer::class,
    'Achievement' => Achievement::class,
];

foreach ($models as $name => $class) {
    $count = $class::count();
    $searchableCount = $class::where(function($query) use ($class) {
        if (method_exists($class, 'shouldBeSearchable')) {
            // Pour ProfessionalProfile avec completion_percentage >= 50
            if ($class === ProfessionalProfile::class) {
                $query->where('completion_percentage', '>=', 50);
            }
        }
    })->count();
    
    echo "   {$name}: {$count} total, {$searchableCount} indexables\n";
}

// 4. Test des commandes Artisan
echo "\n4. 🔨 Test des commandes Artisan disponibles...\n";
$commands = [
    'forge:index' => 'Commande d\'indexation Forge',
    'search:index' => 'Commande d\'indexation générale',
    'search:flush' => 'Commande de vidage des index',
    'meilisearch:reindex' => 'Commande de réindexation',
];

foreach ($commands as $command => $description) {
    try {
        $output = shell_exec("php artisan list | grep '{$command}'");
        if (!empty($output)) {
            echo "   ✅ {$command} - {$description}\n";
        } else {
            echo "   ❌ {$command} - Non trouvée\n";
        }
    } catch (Exception $e) {
        echo "   ❌ {$command} - Erreur: " . $e->getMessage() . "\n";
    }
}

// 5. Test des scripts Composer
echo "\n5. 📦 Vérification des scripts Composer...\n";
$composerFile = file_get_contents(__DIR__ . '/composer.json');
$composer = json_decode($composerFile, true);

$expectedScripts = [
    'meilisearch:index',
    'meilisearch:reindex',
    'deploy:production'
];

foreach ($expectedScripts as $script) {
    if (isset($composer['scripts'][$script])) {
        echo "   ✅ {$script} - Configuré\n";
    } else {
        echo "   ❌ {$script} - Non configuré\n";
    }
}

// 6. Test du job d'indexation
echo "\n6. 🚀 Test du job d'indexation...\n";
try {
    $job = new IndexSearchableModelsJob();
    echo "   ✅ Job IndexSearchableModelsJob créé avec succès\n";
    echo "   Timeout: {$job->timeout}s\n";
    echo "   Tentatives: {$job->tries}\n";
} catch (Exception $e) {
    echo "   ❌ Erreur lors de la création du job: " . $e->getMessage() . "\n";
}

// 7. Vérification des fichiers d'automatisation
echo "\n7. 📁 Vérification des fichiers d'automatisation...\n";
$files = [
    'forge-deploy.sh' => 'Script de déploiement Forge',
    'forge-cron-jobs.md' => 'Documentation des tâches cron',
    'FORGE_MEILISEARCH_AUTOMATION.md' => 'Guide d\'automatisation',
    'app/Console/Commands/ForgeIndexCommand.php' => 'Commande Forge',
    'app/Jobs/IndexSearchableModelsJob.php' => 'Job d\'indexation',
    'app/Http/Middleware/AutoIndexMiddleware.php' => 'Middleware d\'auto-indexation',
];

foreach ($files as $file => $description) {
    if (file_exists(__DIR__ . '/' . $file)) {
        echo "   ✅ {$file} - {$description}\n";
    } else {
        echo "   ❌ {$file} - Manquant\n";
    }
}

// 8. Test de l'indexation (simulation)
echo "\n8. 🔍 Test de simulation d'indexation...\n";
try {
    // Compter les enregistrements avant
    $profileCount = ProfessionalProfile::count();
    $offerCount = ServiceOffer::count();
    $achievementCount = Achievement::count();
    
    echo "   Profils professionnels: {$profileCount}\n";
    echo "   Offres de service: {$offerCount}\n";
    echo "   Réalisations: {$achievementCount}\n";
    
    if ($profileCount > 0 || $offerCount > 0 || $achievementCount > 0) {
        echo "   ✅ Des données sont disponibles pour l'indexation\n";
    } else {
        echo "   ⚠️  Aucune donnée à indexer\n";
    }
} catch (Exception $e) {
    echo "   ❌ Erreur lors du test: " . $e->getMessage() . "\n";
}

// 9. Recommandations
echo "\n9. 💡 Recommandations pour Laravel Forge...\n";
echo "   📋 Variables d'environnement à configurer:\n";
echo "      - SCOUT_DRIVER=meilisearch\n";
echo "      - MEILISEARCH_HOST=https://your-instance.com\n";
echo "      - MEILISEARCH_KEY=your-api-key\n";
echo "      - QUEUE_CONNECTION=database (ou redis)\n";
echo "      - FORGE_WEBHOOK_URL=https://hooks.slack.com/... (optionnel)\n\n";

echo "   ⏰ Tâches cron à ajouter dans Forge:\n";
echo "      0 2 * * * cd /home/forge/hi3dback && php artisan forge:index --check-health\n";
echo "      0 */6 * * * cd /home/forge/hi3dback && php artisan forge:index --check-health\n\n";

echo "   🔄 Script de déploiement:\n";
echo "      Remplacer le script de déploiement par le contenu de forge-deploy.sh\n\n";

echo "   🚀 Commandes de test après déploiement:\n";
echo "      composer run meilisearch:index\n";
echo "      php artisan forge:index --check-health\n";
echo "      curl \"https://your-domain.com/api/search/stats\"\n\n";

echo "✅ Test d'automatisation terminé!\n";
echo "📖 Consultez FORGE_MEILISEARCH_AUTOMATION.md pour le guide complet.\n";
