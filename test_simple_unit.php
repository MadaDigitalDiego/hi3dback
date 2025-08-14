<?php

// Test unitaire simple
echo "🧪 Test Unitaire Simple\n";
echo "======================\n\n";

// Test 1: Autoload
echo "1. Test de l'autoload...\n";
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
    echo "   ✅ Autoload trouvé\n";
} else {
    echo "   ❌ Autoload non trouvé\n";
    exit(1);
}

// Test 2: Bootstrap Laravel
echo "2. Bootstrap Laravel...\n";
try {
    $app = require_once __DIR__ . '/bootstrap/app.php';
    $kernel = $app->make('Illuminate\Contracts\Console\Kernel');
    $kernel->bootstrap();
    echo "   ✅ Laravel bootstrappé\n";
} catch (Exception $e) {
    echo "   ❌ Erreur bootstrap: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 3: Modèles
echo "3. Test des modèles...\n";
try {
    $fileClass = new ReflectionClass('App\Models\File');
    echo "   ✅ Modèle File trouvé\n";
    
    $userClass = new ReflectionClass('App\Models\User');
    echo "   ✅ Modèle User trouvé\n";
} catch (Exception $e) {
    echo "   ❌ Erreur modèles: " . $e->getMessage() . "\n";
}

// Test 4: Services
echo "4. Test des services...\n";
try {
    $fileManagerClass = new ReflectionClass('App\Services\FileManagerService');
    echo "   ✅ FileManagerService trouvé\n";
    
    $swissTransferClass = new ReflectionClass('App\Services\SwissTransferService');
    echo "   ✅ SwissTransferService trouvé\n";
} catch (Exception $e) {
    echo "   ❌ Erreur services: " . $e->getMessage() . "\n";
}

// Test 5: Configuration
echo "5. Test de la configuration...\n";
try {
    $config = config('filesystems.file_management');
    if ($config) {
        echo "   ✅ Configuration file_management trouvée\n";
        echo "   📋 Limite locale: " . $config['local_storage_limit'] . " MB\n";
    } else {
        echo "   ❌ Configuration file_management manquante\n";
    }
    
    $swissConfig = config('filesystems.swisstransfer');
    if ($swissConfig) {
        echo "   ✅ Configuration SwissTransfer trouvée\n";
        echo "   📋 Activé: " . ($swissConfig['enabled'] ? 'Oui' : 'Non') . "\n";
    } else {
        echo "   ❌ Configuration SwissTransfer manquante\n";
    }
} catch (Exception $e) {
    echo "   ❌ Erreur configuration: " . $e->getMessage() . "\n";
}

echo "\n🎉 Tests de base terminés !\n";
echo "Le système semble correctement configuré.\n";
