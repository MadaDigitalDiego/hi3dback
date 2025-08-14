<?php

// Chemin vers l'application Laravel
$basePath = __DIR__;

// Inclure l'autoloader de Composer
require $basePath . '/vendor/autoload.php';

// Charger l'application Laravel
$app = require_once $basePath . '/bootstrap/app.php';

// Obtenir la connexion à la base de données
$db = $app->make('db');

// Vérifier si l'offre pour les messages de service existe déjà
$serviceMessageOffer = $db->table('open_offers')
    ->where('title', 'Service Messages')
    ->first();

if (!$serviceMessageOffer) {
    // Créer une offre ouverte fictive pour les messages de service
    $id = $db->table('open_offers')->insertGetId([
        'title' => 'Service Messages',
        'description' => 'Cette offre est utilisée pour les messages de service',
        'budget' => 0,
        'status' => 'active',
        'user_id' => 1, // ID de l'administrateur
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    echo "Offre ouverte pour les messages de service créée avec l'ID: {$id}\n";
} else {
    echo "L'offre ouverte pour les messages de service existe déjà avec l'ID: {$serviceMessageOffer->id}\n";
}
