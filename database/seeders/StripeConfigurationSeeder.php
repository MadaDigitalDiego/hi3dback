<?php

namespace Database\Seeders;

use App\Models\StripeConfiguration;
use Illuminate\Database\Seeder;

class StripeConfigurationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupère les clés depuis les variables d'environnement
        $publicKey = env('STRIPE_KEY');
        $secretKey = env('STRIPE_SECRET');
        $webhookSecret = env('STRIPE_WEBHOOK_SECRET');

        // Crée ou met à jour la configuration Stripe
        StripeConfiguration::updateOrCreate(
            ['id' => 1], // Utilise l'ID 1 comme configuration par défaut
            [
                'public_key' => $publicKey,
                'secret_key' => $secretKey,
                'webhook_secret' => $webhookSecret,
                'mode' => env('STRIPE_MODE', 'test'),
                'is_active' => true,
                'description' => 'Configuration Stripe par défaut',
            ]
        );
    }
}

