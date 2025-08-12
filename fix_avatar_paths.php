<?php

// Script pour corriger les chemins d'avatar incorrects dans la base de données

// Charger l'environnement Laravel
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

// Fonction pour corriger les chemins d'avatar
function fixAvatarPaths($table) {
    echo "Correction des chemins d'avatar dans la table $table...\n";
    
    // Récupérer tous les enregistrements avec un avatar qui ne commence pas par '/storage/'
    $records = DB::table($table)
        ->whereNotNull('avatar')
        ->where('avatar', 'not like', '/storage/%')
        ->get();
    
    echo "Nombre d'enregistrements à corriger : " . count($records) . "\n";
    
    foreach ($records as $record) {
        $oldPath = $record->avatar;
        
        // Vérifier si le chemin est un chemin temporaire (comme C:\xampp\tmp\phpCA5.tmp)
        if (strpos($oldPath, ':\\') !== false || strpos($oldPath, '/tmp/') !== false) {
            echo "Chemin temporaire détecté pour l'ID $record->id : $oldPath - Impossible de corriger automatiquement\n";
            continue;
        }
        
        // Corriger le chemin en ajoutant le préfixe '/storage/'
        $newPath = '/storage/' . $oldPath;
        
        // Mettre à jour l'enregistrement
        DB::table($table)
            ->where('id', $record->id)
            ->update(['avatar' => $newPath]);
        
        echo "Corrigé pour l'ID $record->id : $oldPath -> $newPath\n";
    }
    
    echo "Correction terminée pour la table $table\n\n";
}

// Corriger les chemins d'avatar dans les tables concernées
try {
    // Vérifier si les tables existent
    $tables = DB::select("SHOW TABLES LIKE 'freelance_profiles'");
    if (count($tables) > 0) {
        fixAvatarPaths('freelance_profiles');
    } else {
        echo "La table freelance_profiles n'existe pas\n";
    }
    
    $tables = DB::select("SHOW TABLES LIKE 'company_profiles'");
    if (count($tables) > 0) {
        fixAvatarPaths('company_profiles');
    } else {
        echo "La table company_profiles n'existe pas\n";
    }
    
    $tables = DB::select("SHOW TABLES LIKE 'professional_profiles'");
    if (count($tables) > 0) {
        fixAvatarPaths('professional_profiles');
    } else {
        echo "La table professional_profiles n'existe pas\n";
    }
    
    $tables = DB::select("SHOW TABLES LIKE 'client_profiles'");
    if (count($tables) > 0) {
        fixAvatarPaths('client_profiles');
    } else {
        echo "La table client_profiles n'existe pas\n";
    }
    
    echo "Toutes les corrections ont été effectuées avec succès\n";
} catch (\Exception $e) {
    echo "Erreur lors de la correction des chemins d'avatar : " . $e->getMessage() . "\n";
    Log::error('Erreur lors de la correction des chemins d\'avatar : ' . $e->getMessage());
}
