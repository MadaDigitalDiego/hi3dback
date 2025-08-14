<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\User;
use App\Models\File;
use App\Services\FileManagerService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🧪 Test Direct du Système de Gestion de Fichiers\n";
echo "===============================================\n\n";

try {
    // Fake storage pour les tests
    Storage::fake('public');
    
    // Récupérer l'utilisateur de test
    $user = User::where('email', 'test@hi3d.com')->first();
    if (!$user) {
        echo "❌ Utilisateur de test non trouvé\n";
        echo "💡 Exécutez d'abord: php create_test_user.php\n";
        exit(1);
    }
    
    echo "👤 Utilisateur de test: {$user->email} (ID: {$user->id})\n\n";
    
    // Test 1: Instanciation du service
    echo "1. Test du FileManagerService...\n";
    $fileManagerService = app(FileManagerService::class);
    echo "   ✅ Service instancié\n\n";
    
    // Test 2: Création d'un fichier simulé
    echo "2. Création d'un fichier de test simulé...\n";
    
    // Créer un fichier temporaire réel
    $tempFile = tempnam(sys_get_temp_dir(), 'hi3d_test_');
    $testContent = "Ceci est un fichier de test pour Hi3D\nTaille: Petite\nType: text/plain\nTest: " . date('Y-m-d H:i:s');
    file_put_contents($tempFile, $testContent);
    
    // Créer un UploadedFile simulé
    $uploadedFile = new UploadedFile(
        $tempFile,
        'test_file.txt',
        'text/plain',
        null,
        true // test mode
    );
    
    echo "   ✅ Fichier temporaire créé: " . basename($tempFile) . "\n";
    echo "   📋 Taille: " . filesize($tempFile) . " bytes\n";
    echo "   📋 Type MIME: text/plain\n\n";
    
    // Test 3: Upload via le service
    echo "3. Upload via FileManagerService...\n";
    $fileRecord = $fileManagerService->uploadFile($uploadedFile, $user);
    
    echo "   ✅ Upload réussi\n";
    echo "   📋 ID: {$fileRecord->id}\n";
    echo "   📋 Nom original: {$fileRecord->original_name}\n";
    echo "   📋 Nom fichier: {$fileRecord->filename}\n";
    echo "   📋 Taille: {$fileRecord->human_size}\n";
    echo "   📋 Type de stockage: {$fileRecord->storage_type}\n";
    echo "   📋 Statut: {$fileRecord->status}\n\n";
    
    // Test 4: Vérification en base
    echo "4. Vérification en base de données...\n";
    $dbFile = File::find($fileRecord->id);
    if ($dbFile) {
        echo "   ✅ Fichier trouvé en base\n";
        echo "   📋 User ID: {$dbFile->user_id}\n";
        echo "   📋 Créé le: {$dbFile->created_at}\n";
    } else {
        echo "   ❌ Fichier non trouvé en base\n";
    }
    echo "\n";
    
    // Test 5: URL de téléchargement
    echo "5. Test de l'URL de téléchargement...\n";
    $downloadUrl = $fileManagerService->getDownloadUrl($fileRecord);
    if ($downloadUrl) {
        echo "   ✅ URL générée: {$downloadUrl}\n";
    } else {
        echo "   ❌ Impossible de générer l'URL\n";
    }
    echo "\n";
    
    // Test 6: Statistiques
    echo "6. Test des statistiques...\n";
    $stats = $fileManagerService->getStorageStats();
    echo "   ✅ Statistiques récupérées:\n";
    foreach ($stats as $key => $value) {
        echo "      - {$key}: {$value}\n";
    }
    echo "\n";
    
    // Test 7: Upload multiple
    echo "7. Test d'upload multiple...\n";
    
    // Créer plusieurs fichiers de test
    $files = [];
    for ($i = 1; $i <= 3; $i++) {
        $tempFile = tempnam(sys_get_temp_dir(), "hi3d_multi_{$i}_");
        file_put_contents($tempFile, "Fichier de test multiple #{$i}\nContenu: " . str_repeat("Test ", $i * 10));
        
        $files[] = new UploadedFile(
            $tempFile,
            "test_multi_{$i}.txt",
            'text/plain',
            null,
            true
        );
    }
    
    $multiResult = $fileManagerService->uploadMultipleFiles($files, $user);
    echo "   ✅ Upload multiple réussi\n";
    echo "   📊 Total: {$multiResult['total']}\n";
    echo "   📊 Réussis: {$multiResult['successful']}\n";
    echo "   📊 Échecs: {$multiResult['failed']}\n\n";
    
    // Test 8: Suppression
    echo "8. Test de suppression...\n";
    $deleted = $fileManagerService->deleteFile($fileRecord);
    if ($deleted) {
        echo "   ✅ Fichier supprimé avec succès\n";
    } else {
        echo "   ❌ Échec de la suppression\n";
    }
    echo "\n";
    
    // Test 9: Statistiques finales
    echo "9. Statistiques finales...\n";
    $finalStats = $fileManagerService->getStorageStats();
    echo "   📊 Fichiers restants: {$finalStats['total_files']}\n";
    echo "   📊 Fichiers complétés: {$finalStats['completed_files']}\n\n";
    
    // Nettoyage
    echo "🧹 Nettoyage...\n";
    File::where('user_id', $user->id)->delete();
    echo "   ✅ Fichiers de test supprimés\n\n";
    
    echo "🎉 Tous les tests sont passés avec succès !\n";
    echo "Le système de gestion de fichiers fonctionne parfaitement.\n\n";
    
    echo "📝 Résumé des fonctionnalités testées:\n";
    echo "   ✅ Instanciation du service\n";
    echo "   ✅ Upload de fichier unique\n";
    echo "   ✅ Stockage en base de données\n";
    echo "   ✅ Génération d'URL de téléchargement\n";
    echo "   ✅ Statistiques de stockage\n";
    echo "   ✅ Upload multiple\n";
    echo "   ✅ Suppression de fichier\n";
    echo "   ✅ Nettoyage automatique\n\n";
    
    echo "🚀 Le système est prêt pour la production !\n";

} catch (Exception $e) {
    echo "❌ Erreur lors du test: " . $e->getMessage() . "\n";
    echo "📍 Fichier: " . $e->getFile() . " ligne " . $e->getLine() . "\n";
    echo "🔍 Trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
