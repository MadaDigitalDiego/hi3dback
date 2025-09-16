<?php

/**
 * Script de démarrage rapide pour tester l'authentification Gmail
 * 
 * Ce script configure une démo complète de l'authentification Gmail
 */

echo "🚀 Configuration de la démo d'authentification Gmail\n";
echo "==================================================\n\n";

// Vérifier si nous sommes dans le bon répertoire
if (!file_exists('artisan')) {
    echo "❌ Erreur: Ce script doit être exécuté depuis la racine du projet Laravel\n";
    exit(1);
}

echo "1. 📋 Vérification des prérequis...\n";

// Vérifier que les fichiers nécessaires existent
$requiredFiles = [
    'app/Models/GmailConfiguration.php',
    'app/Services/GmailAuthService.php',
    'app/Http/Controllers/Api/GmailAuthController.php',
    'app/Filament/Resources/GmailConfigurationResource.php',
];

foreach ($requiredFiles as $file) {
    if (file_exists($file)) {
        echo "   ✅ $file\n";
    } else {
        echo "   ❌ $file manquant\n";
        exit(1);
    }
}

echo "\n2. 🗄️  Exécution des migrations...\n";
$output = shell_exec('php artisan migrate --force 2>&1');
echo $output;

echo "\n3. 🌱 Création d'une configuration de test...\n";
$output = shell_exec('php artisan db:seed --class=GmailConfigurationSeeder --force 2>&1');
echo $output;

echo "\n4. 🔧 Vérification de la configuration...\n";

// Tester la connexion à la base de données
try {
    $pdo = new PDO(
        'mysql:host=' . (getenv('DB_HOST') ?: 'localhost') . ';dbname=' . (getenv('DB_DATABASE') ?: 'hi3dback'),
        getenv('DB_USERNAME') ?: 'root',
        getenv('DB_PASSWORD') ?: ''
    );
    
    $stmt = $pdo->query('SELECT COUNT(*) FROM gmail_configurations WHERE is_active = 1');
    $count = $stmt->fetchColumn();
    
    if ($count > 0) {
        echo "   ✅ Configuration Gmail trouvée en base de données\n";
    } else {
        echo "   ⚠️  Aucune configuration Gmail active trouvée\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ Erreur de connexion à la base de données: " . $e->getMessage() . "\n";
}

echo "\n5. 🌐 Instructions pour démarrer le serveur...\n";
echo "   Exécutez dans un terminal séparé:\n";
echo "   php artisan serve\n\n";

echo "6. 🧪 Tests disponibles:\n";
echo "   - Test automatique: php test_gmail_auth.php\n";
echo "   - Collection Postman: Gmail_Auth_API.postman_collection.json\n";
echo "   - Interface admin: http://localhost:8000/admin\n\n";

echo "7. 📋 Endpoints API créés:\n";
echo "   GET  /api/auth/gmail/status   - Vérifier la configuration\n";
echo "   GET  /api/auth/gmail/redirect - Obtenir l'URL de redirection Google\n";
echo "   GET  /api/auth/gmail/callback - Callback pour Google OAuth\n\n";

echo "8. 🔑 Configuration Google OAuth requise:\n";
echo "   1. Aller sur https://console.cloud.google.com/\n";
echo "   2. Créer un projet ou en sélectionner un\n";
echo "   3. Activer l'API Google Identity\n";
echo "   4. Créer des identifiants OAuth 2.0\n";
echo "   5. Ajouter l'URI de redirection: http://localhost:8000/api/auth/gmail/callback\n";
echo "   6. Mettre à jour la configuration dans l'admin Filament\n\n";

echo "9. 🎯 Flux de test complet:\n";
echo "   1. Démarrer le serveur: php artisan serve\n";
echo "   2. Configurer Google OAuth (étape 8)\n";
echo "   3. Mettre à jour la config dans /admin\n";
echo "   4. Tester avec: php test_gmail_auth.php\n";
echo "   5. Ou ouvrir: http://localhost:8000/api/auth/gmail/redirect\n\n";

echo "✅ Configuration terminée!\n";
echo "📖 Consultez GMAIL_AUTH_DOCUMENTATION.md pour plus de détails.\n";

// Afficher les informations de configuration actuelles
echo "\n📊 Informations de configuration actuelles:\n";
echo "============================================\n";

try {
    $stmt = $pdo->query('SELECT name, client_id, redirect_uri, is_active FROM gmail_configurations ORDER BY created_at DESC LIMIT 1');
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($config) {
        echo "Nom: " . $config['name'] . "\n";
        echo "Client ID: " . substr($config['client_id'], 0, 20) . "...\n";
        echo "Redirect URI: " . $config['redirect_uri'] . "\n";
        echo "Active: " . ($config['is_active'] ? 'Oui' : 'Non') . "\n";
        
        if (strpos($config['client_id'], 'your-google-client-id') !== false) {
            echo "\n⚠️  ATTENTION: Vous utilisez encore les valeurs de test!\n";
            echo "   Remplacez par vos vraies clés Google OAuth dans l'admin.\n";
        }
    }
} catch (Exception $e) {
    echo "Impossible de récupérer les informations de configuration.\n";
}

echo "\n🎉 Prêt à tester l'authentification Gmail!\n";
