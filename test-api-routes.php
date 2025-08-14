<?php

/**
 * Script de test pour vérifier les routes API
 */

// Vérifier que PHP est installé et fonctionne
echo "PHP version: " . PHP_VERSION . "\n";

// Vérifier que l'application Laravel est accessible
$basePath = __DIR__;
if (file_exists($basePath . '/artisan')) {
    echo "Laravel application found at: " . $basePath . "\n";
} else {
    echo "Error: Laravel application not found at: " . $basePath . "\n";
    exit(1);
}

// Exécuter la commande artisan route:list pour obtenir la liste des routes
echo "Executing 'php artisan route:list'...\n";
$output = shell_exec('php artisan route:list');
echo $output . "\n";

echo "API routes check completed.\n";
