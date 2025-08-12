<?php

// Chemin vers l'application Laravel
$basePath = __DIR__;

// Inclure l'autoloader de Composer
require $basePath . '/vendor/autoload.php';

// Charger l'application Laravel
$app = require_once $basePath . '/bootstrap/app.php';

// Obtenir le noyau de l'application
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

// Exécuter la commande de migration
$status = $kernel->call('migrate');

// Afficher le résultat
echo "Migration status: " . ($status === 0 ? "Success" : "Failed") . "\n";

// Terminer l'application
$kernel->terminate(null, $status);
