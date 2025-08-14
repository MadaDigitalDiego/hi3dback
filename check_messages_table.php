<?php

// Chemin vers l'application Laravel
$basePath = __DIR__;

// Inclure l'autoloader de Composer
require $basePath . '/vendor/autoload.php';

// Charger l'application Laravel
$app = require_once $basePath . '/bootstrap/app.php';

// Obtenir la connexion à la base de données
$db = $app->make('db');

// Obtenir la structure de la table messages
$driver = $db->getDriverName();

if ($driver === 'mysql') {
    $columns = $db->select('DESCRIBE messages');
    echo "Structure de la table messages :\n";
    foreach ($columns as $column) {
        echo "- {$column->Field} ({$column->Type})" . ($column->Null === 'NO' ? ' NOT NULL' : '') . "\n";
    }
} elseif ($driver === 'pgsql') {
    $columns = $db->select("
        SELECT column_name, data_type, is_nullable
        FROM information_schema.columns
        WHERE table_name = 'messages'
        ORDER BY ordinal_position
    ");
    echo "Structure de la table messages :\n";
    foreach ($columns as $column) {
        echo "- {$column->column_name} ({$column->data_type})" . ($column->is_nullable === 'NO' ? ' NOT NULL' : '') . "\n";
    }
} else {
    echo "Driver de base de données non supporté: $driver\n";
}
