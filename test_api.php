<?php

// Définir les en-têtes pour permettre l'accès depuis n'importe quelle origine
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

// URL de l'API à tester
$api_url = 'http://localhost:8000/api/professionals';

// Initialiser cURL
$ch = curl_init();

// Configurer les options cURL
curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, false);

// Exécuter la requête cURL
$response = curl_exec($ch);

// Vérifier s'il y a des erreurs
if (curl_errno($ch)) {
    echo json_encode(['error' => 'Erreur cURL: ' . curl_error($ch)]);
    exit;
}

// Fermer la session cURL
curl_close($ch);

// Afficher la réponse
echo $response;
