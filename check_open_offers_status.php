<?php

// Chemin vers l'application Laravel
$basePath = __DIR__;

// Inclure l'autoloader de Composer
require $basePath . '/vendor/autoload.php';

// Charger l'application Laravel
$app = require_once $basePath . '/bootstrap/app.php';

// Obtenir la connexion à la base de données
$db = $app->make('db');

// Vérifier la structure de la colonne status
$column = $db->select("SHOW COLUMNS FROM open_offers WHERE Field = 'status'")[0];

echo "Structure de la colonne status dans la table open_offers :\n";
echo "Type: {$column->Type}\n";
echo "Null: {$column->Null}\n";
echo "Default: {$column->Default}\n";

// Récupérer les valeurs distinctes de la colonne status
$statuses = $db->table('open_offers')
    ->select('status')
    ->distinct()
    ->get()
    ->pluck('status')
    ->toArray();

echo "Valeurs distinctes de la colonne status :\n";
foreach ($statuses as $status) {
    echo "- {$status}\n";
}
