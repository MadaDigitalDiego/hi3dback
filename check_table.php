<?php

// Chemin vers l'application Laravel
$basePath = __DIR__;

// Inclure l'autoloader de Composer
require $basePath . '/vendor/autoload.php';

// Charger l'application Laravel
$app = require_once $basePath . '/bootstrap/app.php';

// Obtenir la connexion à la base de données
$db = $app->make('db');

// Vérifier si la table existe
$tableExists = $db->getSchemaBuilder()->hasTable('service_messages');

echo "La table service_messages " . ($tableExists ? "existe" : "n'existe pas") . " dans la base de données." . PHP_EOL;

// Si la table n'existe pas, créer la table manuellement
if (!$tableExists) {
    echo "Création manuelle de la table service_messages..." . PHP_EOL;
    
    $db->statement('CREATE TABLE `service_messages` (
        `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        `sender_id` bigint(20) UNSIGNED NOT NULL,
        `recipient_id` bigint(20) UNSIGNED NOT NULL,
        `service_id` bigint(20) UNSIGNED DEFAULT NULL,
        `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
        `is_read` tinyint(1) NOT NULL DEFAULT 0,
        `read_at` timestamp NULL DEFAULT NULL,
        `created_at` timestamp NULL DEFAULT NULL,
        `updated_at` timestamp NULL DEFAULT NULL,
        PRIMARY KEY (`id`),
        KEY `service_messages_sender_id_foreign` (`sender_id`),
        KEY `service_messages_recipient_id_foreign` (`recipient_id`),
        KEY `service_messages_service_id_foreign` (`service_id`),
        CONSTRAINT `service_messages_sender_id_foreign` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
        CONSTRAINT `service_messages_recipient_id_foreign` FOREIGN KEY (`recipient_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
        CONSTRAINT `service_messages_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `service_offers` (`id`) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
    
    echo "Table service_messages créée avec succès." . PHP_EOL;
}
