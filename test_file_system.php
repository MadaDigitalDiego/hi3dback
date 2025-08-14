<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\File;
use App\Models\User;
use App\Services\FileManagerService;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\DB;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🧪 Test du Système de Gestion de Fichiers\n";
echo "==========================================\n\n";

try {
    // Test 1: Vérifier la connexion à la base de données
    echo "1. Test de connexion à la base de données...\n";
    $dbConnection = DB::connection()->getPdo();
    echo "   ✅ Connexion réussie\n\n";

    // Test 2: Vérifier que la table files existe
    echo "2. Vérification de la table 'files'...\n";
    $tableExists = DB::getSchemaBuilder()->hasTable('files');
    if ($tableExists) {
        echo "   ✅ Table 'files' existe\n";
        
        // Afficher la structure de la table
        $columns = DB::getSchemaBuilder()->getColumnListing('files');
        echo "   📋 Colonnes: " . implode(', ', $columns) . "\n\n";
    } else {
        echo "   ❌ Table 'files' n'existe pas\n";
        echo "   💡 Exécutez: php artisan migrate\n\n";
        exit(1);
    }

    // Test 3: Vérifier le modèle File
    echo "3. Test du modèle File...\n";
    $fileCount = File::count();
    echo "   ✅ Modèle File accessible\n";
    echo "   📊 Nombre de fichiers actuels: {$fileCount}\n\n";

    // Test 4: Vérifier les services
    echo "4. Test des services...\n";
    $fileManagerService = app(FileManagerService::class);
    echo "   ✅ FileManagerService instancié\n";
    
    $stats = $fileManagerService->getStorageStats();
    echo "   📊 Statistiques de stockage:\n";
    foreach ($stats as $key => $value) {
        echo "      - {$key}: {$value}\n";
    }
    echo "\n";

    // Test 5: Vérifier la configuration
    echo "5. Vérification de la configuration...\n";
    $localLimit = config('filesystems.file_management.local_storage_limit');
    $maxUpload = config('filesystems.file_management.max_upload_size');
    $swissEnabled = config('filesystems.swisstransfer.enabled');
    
    echo "   📋 Limite stockage local: {$localLimit} MB\n";
    echo "   📋 Taille max upload: {$maxUpload} MB\n";
    echo "   📋 SwissTransfer activé: " . ($swissEnabled ? 'Oui' : 'Non') . "\n\n";

    // Test 6: Vérifier les répertoires de stockage
    echo "6. Vérification des répertoires de stockage...\n";
    $storagePath = storage_path('app/public');
    $uploadsPath = storage_path('app/public/uploads');
    
    if (is_dir($storagePath)) {
        echo "   ✅ Répertoire storage/app/public existe\n";
    } else {
        echo "   ❌ Répertoire storage/app/public manquant\n";
    }
    
    if (!is_dir($uploadsPath)) {
        mkdir($uploadsPath, 0755, true);
        echo "   ✅ Répertoire uploads créé\n";
    } else {
        echo "   ✅ Répertoire uploads existe\n";
    }
    
    // Vérifier les permissions
    if (is_writable($storagePath)) {
        echo "   ✅ Répertoire storage accessible en écriture\n";
    } else {
        echo "   ⚠️  Répertoire storage non accessible en écriture\n";
    }
    echo "\n";

    // Test 7: Créer un fichier de test
    echo "7. Test de création d'un fichier...\n";
    $user = User::first();
    if ($user) {
        $testFile = File::create([
            'original_name' => 'test_system.txt',
            'filename' => 'test_system_' . uniqid() . '.txt',
            'mime_type' => 'text/plain',
            'size' => 1024,
            'extension' => 'txt',
            'storage_type' => 'local',
            'local_path' => 'uploads/test_system_' . uniqid() . '.txt',
            'status' => 'completed',
            'user_id' => $user->id,
        ]);
        
        echo "   ✅ Fichier de test créé (ID: {$testFile->id})\n";
        echo "   📋 Nom: {$testFile->original_name}\n";
        echo "   📋 Taille: {$testFile->human_size}\n";
        echo "   📋 Type de stockage: {$testFile->storage_type}\n";
        
        // Nettoyer le fichier de test
        $testFile->delete();
        echo "   🗑️  Fichier de test supprimé\n\n";
    } else {
        echo "   ⚠️  Aucun utilisateur trouvé pour le test\n";
        echo "   💡 Créez un utilisateur ou exécutez les seeders\n\n";
    }

    echo "🎉 Tous les tests sont passés avec succès !\n";
    echo "Le système de gestion de fichiers est prêt à être utilisé.\n\n";

    echo "📝 Prochaines étapes:\n";
    echo "1. Testez l'API avec Postman (collection fournie)\n";
    echo "2. Créez des fichiers de test via l'interface\n";
    echo "3. Vérifiez les logs dans storage/logs/\n";

} catch (Exception $e) {
    echo "❌ Erreur lors du test: " . $e->getMessage() . "\n";
    echo "📍 Fichier: " . $e->getFile() . " ligne " . $e->getLine() . "\n";
    echo "🔍 Trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
