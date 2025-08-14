<?php

// Chemin vers l'application Laravel
$basePath = __DIR__;

// Inclure l'autoloader de Composer
require $basePath . '/vendor/autoload.php';

// Charger l'application Laravel
$app = require_once $basePath . '/bootstrap/app.php';

// Obtenir le noyau de l'application
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

// Exécuter la commande Artisan
$status = $kernel->handle(
    $input = new Symfony\Component\Console\Input\ArrayInput([
        'command' => 'migrate',
        '--force' => true, // Forcer l'exécution en production
    ]),
    new Symfony\Component\Console\Output\BufferedOutput
);

// Terminer le noyau
$kernel->terminate($input, $status);

// Afficher le résultat
echo "Migration terminée avec le statut : " . $status . PHP_EOL;
