<?php

require_once 'vendor/autoload.php';

// Charger l'application Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Http\Controllers\Api\DashboardController;
use Illuminate\Http\Request;

echo "Test de l'API Dashboard\n";

try {
    // Créer une requête simulée
    $request = new Request();
    
    // Simuler un utilisateur authentifié (ID 1)
    $user = User::find(1);
    
    if (!$user) {
        echo "Erreur: Utilisateur avec ID 1 non trouvé\n";
        exit(1);
    }
    
    echo "Utilisateur trouvé: " . $user->email . "\n";
    echo "Type d'utilisateur: " . ($user->is_professional ? 'Professionnel' : 'Client') . "\n";
    
    // Créer une requête avec l'utilisateur authentifié
    $request->setUserResolver(function () use ($user) {
        return $user;
    });
    
    // Tester le contrôleur
    $controller = new DashboardController();
    $response = $controller->getDashboardData($request);
    
    echo "Réponse de l'API:\n";
    echo json_encode($response->getData(), JSON_PRETTY_PRINT) . "\n";
    
} catch (Exception $e) {
    echo "Erreur: " . $e->getMessage() . "\n";
    echo "Fichier: " . $e->getFile() . "\n";
    echo "Ligne: " . $e->getLine() . "\n";
} 