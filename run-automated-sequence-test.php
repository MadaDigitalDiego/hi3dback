<?php

/**
 * Script de test automatisé pour la séquence complète du workflow
 * 
 * Ce script exécute la séquence de test automatisée documentée dans quick-start-testing.md :
 * 1. Login Client → Token sauvegardé automatiquement
 * 2. Login Professional → Token sauvegardé automatiquement  
 * 3. Create Open Offer → offer_id sauvegardé automatiquement
 * 4. Apply to Offer → application_id sauvegardé automatiquement
 * 5. Accept Application → Candidature acceptée
 * 6. View Accepted Applications → Vérifier la liste
 * 7. Assign Offer to Professional → Offre attribuée
 * 
 * Usage: php run-automated-sequence-test.php
 */

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

// Configuration
$baseUrl = 'http://localhost:8000/api'; // Ajustez selon votre configuration
$clientEmail = 'client.test@example.com';
$professionalEmail = 'professional.test@example.com';
$password = 'password123';

// Variables globales pour stocker les données
$clientToken = null;
$professionalToken = null;
$offerId = null;
$applicationId = null;

/**
 * Fonction utilitaire pour faire des requêtes HTTP
 */
function makeRequest($method, $url, $data = null, $token = null) {
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    
    $headers = ['Content-Type: application/json'];
    if ($token) {
        $headers[] = 'Authorization: Bearer ' . $token;
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    if ($data) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [
        'status' => $httpCode,
        'data' => json_decode($response, true)
    ];
}

/**
 * Étape 1 : Authentification
 */
function step1_authentication() {
    global $baseUrl, $clientEmail, $professionalEmail, $password, $clientToken, $professionalToken;
    
    echo "🔐 Étape 1 : Authentification\n";
    
    // Login Client
    echo "   - Login Client...";
    $response = makeRequest('POST', $baseUrl . '/login', [
        'email' => $clientEmail,
        'password' => $password
    ]);
    
    if ($response['status'] === 200 && isset($response['data']['token'])) {
        $clientToken = $response['data']['token'];
        echo " ✅ Token sauvegardé automatiquement\n";
    } else {
        echo " ❌ Échec\n";
        print_r($response);
        exit(1);
    }
    
    // Login Professional
    echo "   - Login Professional...";
    $response = makeRequest('POST', $baseUrl . '/login', [
        'email' => $professionalEmail,
        'password' => $password
    ]);
    
    if ($response['status'] === 200 && isset($response['data']['token'])) {
        $professionalToken = $response['data']['token'];
        echo " ✅ Token sauvegardé automatiquement\n";
    } else {
        echo " ❌ Échec\n";
        print_r($response);
        exit(1);
    }
}

/**
 * Étape 2 : Création d'offre
 */
function step2_create_offer() {
    global $baseUrl, $clientToken, $offerId;
    
    echo "\n📝 Étape 2 : Création d'offre\n";
    echo "   - Create Open Offer...";
    
    $offerData = [
        'title' => 'Test Automatisé - Développement Application',
        'description' => 'Offre créée automatiquement lors du test de la séquence.',
        'categories' => ['Test', 'Développement'],
        'budget' => '2000-3000',
        'deadline' => date('Y-m-d', strtotime('+30 days')),
        'company' => 'Test Automation Corp',
        'website' => 'https://test-automation.com',
        'recruitment_type' => 'company',
        'open_to_applications' => true,
        'auto_invite' => false,
    ];
    
    $response = makeRequest('POST', $baseUrl . '/open-offers', $offerData, $clientToken);
    
    if ($response['status'] === 201 && isset($response['data']['open_offer']['id'])) {
        $offerId = $response['data']['open_offer']['id'];
        echo " ✅ offer_id sauvegardé automatiquement (ID: {$offerId})\n";
    } else {
        echo " ❌ Échec\n";
        print_r($response);
        exit(1);
    }
}

/**
 * Étape 3 : Candidature
 */
function step3_apply_to_offer() {
    global $baseUrl, $professionalToken, $offerId, $applicationId;
    
    echo "\n📋 Étape 3 : Candidature\n";
    echo "   - Apply to Offer...";
    
    $applicationData = [
        'proposal' => 'Je suis intéressé par cette offre de test automatisé.',
        'estimated_duration' => '2 semaines',
        'proposed_budget' => '2500',
    ];
    
    $response = makeRequest('POST', $baseUrl . "/open-offers/{$offerId}/apply", $applicationData, $professionalToken);
    
    if ($response['status'] === 201 && isset($response['data']['application']['id'])) {
        $applicationId = $response['data']['application']['id'];
        echo " ✅ application_id sauvegardé automatiquement (ID: {$applicationId})\n";
    } else {
        echo " ❌ Échec\n";
        print_r($response);
        exit(1);
    }
}

/**
 * Étape 4 : Gestion candidature
 */
function step4_manage_application() {
    global $baseUrl, $clientToken, $offerId, $applicationId;
    
    echo "\n⚖️ Étape 4 : Gestion candidature\n";
    
    // Accept Application
    echo "   - Accept Application...";
    $response = makeRequest('PATCH', $baseUrl . "/offer-applications/{$applicationId}/status", [
        'status' => 'accepted'
    ], $clientToken);
    
    if ($response['status'] === 200) {
        echo " ✅ Candidature acceptée\n";
    } else {
        echo " ❌ Échec\n";
        print_r($response);
        exit(1);
    }
    
    // View Accepted Applications
    echo "   - View Accepted Applications...";
    $response = makeRequest('GET', $baseUrl . "/open-offers/{$offerId}/accepted-applications", null, $clientToken);
    
    if ($response['status'] === 200 && 
        isset($response['data']['accepted_applications']) && 
        count($response['data']['accepted_applications']) === 1) {
        echo " ✅ Liste vérifiée (1 candidature acceptée)\n";
    } else {
        echo " ❌ Échec\n";
        print_r($response);
        exit(1);
    }
}

/**
 * Étape 5 : Attribution finale
 */
function step5_assign_offer() {
    global $baseUrl, $clientToken, $offerId, $applicationId;
    
    echo "\n🎯 Étape 5 : Attribution finale\n";
    echo "   - Assign Offer to Professional...";
    
    $response = makeRequest('POST', $baseUrl . "/open-offers/{$offerId}/assign", [
        'application_id' => $applicationId
    ], $clientToken);
    
    if ($response['status'] === 200) {
        echo " ✅ Offre attribuée\n";
    } else {
        echo " ❌ Échec\n";
        print_r($response);
        exit(1);
    }
}

/**
 * Vérifications finales
 */
function final_verifications() {
    global $baseUrl, $clientToken, $offerId;
    
    echo "\n🔍 Vérifications finales\n";
    echo "   - Vérification statut final de l'offre...";
    
    $response = makeRequest('GET', $baseUrl . "/open-offers/{$offerId}", null, $clientToken);
    
    if ($response['status'] === 200 && $response['data']['status'] === 'in_progress') {
        echo " ✅ Statut: in_progress\n";
    } else {
        echo " ❌ Statut incorrect\n";
        print_r($response);
        exit(1);
    }
}

/**
 * Affichage du résumé
 */
function display_summary() {
    global $clientToken, $professionalToken, $offerId, $applicationId;
    
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "🎉 SÉQUENCE DE TEST AUTOMATISÉE TERMINÉE AVEC SUCCÈS !\n";
    echo str_repeat("=", 60) . "\n";
    echo "📊 Résultats :\n";
    echo "   - Client Token: " . substr($clientToken, 0, 20) . "...\n";
    echo "   - Professional Token: " . substr($professionalToken, 0, 20) . "...\n";
    echo "   - Offer ID: {$offerId}\n";
    echo "   - Application ID: {$applicationId}\n";
    echo "   - Statut final de l'offre: in_progress\n";
    echo "   - Statut final de la candidature: accepted\n";
    echo "\n✅ Toutes les étapes de la séquence ont été exécutées avec succès !\n";
}

// Exécution du script principal
try {
    echo "🚀 Démarrage de la séquence de test automatisée\n";
    echo "📋 Séquence basée sur quick-start-testing.md\n\n";
    
    step1_authentication();
    step2_create_offer();
    step3_apply_to_offer();
    step4_manage_application();
    step5_assign_offer();
    final_verifications();
    display_summary();
    
} catch (Exception $e) {
    echo "\n❌ Erreur lors de l'exécution : " . $e->getMessage() . "\n";
    exit(1);
}
