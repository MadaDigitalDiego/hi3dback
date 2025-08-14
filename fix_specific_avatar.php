<?php

// Script pour corriger un chemin d'avatar spécifique dans la base de données

// Charger l'environnement Laravel
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

// ID du profil à corriger
$profileId = 8;
$table = 'professional_profiles';

try {
    // Récupérer l'enregistrement actuel
    $profile = DB::table($table)->where('id', $profileId)->first();
    
    if (!$profile) {
        echo "Aucun profil trouvé avec l'ID $profileId dans la table $table\n";
        exit;
    }
    
    echo "Profil trouvé avec l'ID $profileId\n";
    echo "Chemin d'avatar actuel : " . ($profile->avatar ?? 'NULL') . "\n";
    
    // Définir un nouveau chemin d'avatar par défaut
    $newAvatarPath = '/storage/default_avatars/default_profile.png';
    
    // Mettre à jour l'enregistrement
    DB::table($table)
        ->where('id', $profileId)
        ->update(['avatar' => $newAvatarPath]);
    
    echo "Chemin d'avatar mis à jour avec succès : $newAvatarPath\n";
    
} catch (\Exception $e) {
    echo "Erreur lors de la mise à jour du chemin d'avatar : " . $e->getMessage() . "\n";
    Log::error('Erreur lors de la mise à jour du chemin d\'avatar : ' . $e->getMessage());
}
