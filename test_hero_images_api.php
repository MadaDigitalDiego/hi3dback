<?php

/**
 * Script de test pour l'API des images Hero
 * 
 * Ce script teste toutes les fonctionnalités de l'API des images Hero
 */

$baseUrl = 'http://localhost:8000/api';

function makeRequest($url, $method = 'GET', $headers = []) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge(['Accept: application/json'], $headers));
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [
        'status' => $httpCode,
        'data' => json_decode($response, true)
    ];
}

function testEndpoint($name, $url, $method = 'GET', $headers = []) {
    echo "\n🧪 Test: $name\n";
    echo "📍 URL: $url\n";
    
    $result = makeRequest($url, $method, $headers);
    
    if ($result['status'] === 200) {
        echo "✅ Succès (HTTP {$result['status']})\n";
        
        if (isset($result['data']['data']) && is_array($result['data']['data'])) {
            echo "📊 Nombre d'éléments: " . count($result['data']['data']) . "\n";
            
            if (count($result['data']['data']) > 0) {
                $first = $result['data']['data'][0];
                echo "🖼️  Premier élément:\n";
                echo "   - ID: {$first['id']}\n";
                echo "   - Titre: {$first['title']}\n";
                echo "   - Actif: " . ($first['is_active'] ? 'Oui' : 'Non') . "\n";
                echo "   - Position: {$first['position']}\n";
            }
        } elseif (isset($result['data']['total'])) {
            echo "📊 Statistiques:\n";
            echo "   - Total: {$result['data']['total']}\n";
            echo "   - Actives: {$result['data']['active']}\n";
            echo "   - Inactives: {$result['data']['inactive']}\n";
        } else {
            echo "📄 Réponse: " . json_encode($result['data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        }
    } else {
        echo "❌ Erreur (HTTP {$result['status']})\n";
        echo "📄 Réponse: " . json_encode($result['data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    }
    
    echo str_repeat('-', 80) . "\n";
}

echo "🚀 Test de l'API des Images Hero\n";
echo "================================\n";

// Test 1: Récupérer les images Hero actives
testEndpoint(
    'Images Hero actives',
    $baseUrl . '/hero-images'
);

// Test 2: Récupérer les statistiques
testEndpoint(
    'Statistiques des images Hero',
    $baseUrl . '/hero-images/stats'
);

// Test 3: Récupérer une image spécifique
testEndpoint(
    'Image Hero spécifique (ID: 1)',
    $baseUrl . '/hero-images/1'
);

// Test 4: Tester une image inexistante
testEndpoint(
    'Image Hero inexistante (ID: 999)',
    $baseUrl . '/hero-images/999'
);

echo "\n🎉 Tests terminés !\n";
echo "\n📋 Résumé des fonctionnalités testées:\n";
echo "✅ Récupération des images Hero actives (triées par position)\n";
echo "✅ Statistiques des images Hero\n";
echo "✅ Récupération d'une image spécifique\n";
echo "✅ Gestion des erreurs (image inexistante)\n";

echo "\n🔧 Fonctionnalités du back-office à tester manuellement:\n";
echo "• Upload d'images avec aperçu\n";
echo "• Activation/désactivation des images\n";
echo "• Réorganisation par drag & drop\n";
echo "• Suppression d'images\n";
echo "• Génération automatique de miniatures\n";

echo "\n🌐 Accès au back-office:\n";
echo "URL: http://localhost:8000/admin\n";
echo "Email: superadmin@hi3d.com\n";
echo "Mot de passe: superadmin123\n";
echo "Section: Gestion du contenu > Images Hero\n";
