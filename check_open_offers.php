<?php

// Chemin vers l'application Laravel
$basePath = __DIR__;

// Inclure l'autoloader de Composer
require $basePath . '/vendor/autoload.php';

// Charger l'application Laravel
$app = require_once $basePath . '/bootstrap/app.php';

// Obtenir la connexion à la base de données
$db = $app->make('db');

// Vérifier s'il existe des offres ouvertes
$openOffers = $db->table('open_offers')->get();

echo "Offres ouvertes dans la base de données :\n";
foreach ($openOffers as $offer) {
    echo "- ID: {$offer->id}, Titre: {$offer->title}\n";
}
