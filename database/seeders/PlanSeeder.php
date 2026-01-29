<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'title' => 'Abonnement Gratuit hi3d',
                'name' => 'free',
                'user_type' => 'professional',
                'price' => 0.00,
                'description' => 'Pour les artistes qui débutent',
                'stripe_product_id' => 'prod_SNWXPVxEHnr8gw',
                'stripe_price_id' => 'price_1RSlU5FKK6JoGdxmf9PuiJfw',
                'interval' => 'month',
                'interval_count' => 1,
                'is_active' => true,
                // Free professional plan: 0 quota on main resources
                'max_services' => 2,
                'max_open_offers' => 2,
                'max_applications' => 2,
                'max_messages' => 2,
                'features' => json_encode([
                    'Profil de base',
                    'Portfolio limité (3 projets)',
                    'Accès aux projets publics',
                    'Messagerie de base',
                    'Support communautaire',
                ])
            ],
            [
                'title' => 'Abonnement Pro hi3d',
                'name' => 'pro',
                'user_type' => 'professional',
                'price' => 19.99,
                'description' => 'Pour les artistes professionnels',
                'stripe_product_id' => 'prod_SNWbF8gvSLABUt',
                'stripe_price_id' => 'price_1RSlXxFKK6JoGdxmSv8SKhAk',
                'interval' => 'month',
                'interval_count' => 1,
                'is_active' => true,
                'max_services' => 50,
                'max_open_offers' => 0,
                'max_applications' => 500,
                'max_messages' => 1000,
                'features' => json_encode([
                    'Profil avancé',
                    'Portfolio illimité',
                    'Accès aux projets publics et privés',
                    'Messagerie avancée',
                    'Support par email',
                ])
            ],
            [
                'title' => 'Abonnement Entreprise hi3d',
                'name' => 'enterprise',
                'user_type' => 'professional',
                'price' => 49.99,
                'description' => 'Pour les studios et agences',
                'stripe_product_id' => 'prod_SNWdgusveP2aUL',
                'stripe_price_id' => 'price_1RSlZpFKK6JoGdxmRq9XwONW',
                'interval' => 'month',
                'interval_count' => 1,
                'is_active' => true,
                'max_services' => 200,
                'max_open_offers' => 0,
                'max_applications' => 5000,
                'max_messages' => 10000,
                'features' => json_encode([
                    'Profils multiples',
                    'Portfolio illimité',
                    'Accès à tous les projets',
                    'Messagerie avancée avec CRM',
                    'Support dédié 24/7',
                ])
            ],
            [
                'title' => 'Plan Client Gratuit',
                'name' => 'client_free',
                'user_type' => 'client',
                'price' => 0.00,
                'description' => 'Pour les clients - Accès illimité à toutes les fonctionnalités',
                'stripe_product_id' => null,
                'stripe_price_id' => null,
                'interval' => 'month',
                'interval_count' => 1,
                'is_active' => true,
                'max_services' => 0, // Not applicable for clients
                'max_open_offers' => 999, // Unlimited
                'max_applications' => 0, // Not applicable for clients
                'max_messages' => 9999, // Unlimited for practical purposes
                'features' => json_encode([
                    'Profil de base',
                    'Création d\'offres illimitées',
                    'Messagerie illimitée',
                    'Support communautaire',
                    'Tous les tarifs sont accessibles',
                ])
            ],
            [
                'title' => 'Plan Client Pro',
                'name' => 'client_pro',
                'user_type' => 'client',
                'price' => 14.99,
                'description' => 'Pour les clients professionnels - Accès illimité à toutes les fonctionnalités',
                'stripe_product_id' => null,
                'stripe_price_id' => null,
                'interval' => 'month',
                'interval_count' => 1,
                'is_active' => true,
                'max_services' => 0, // Not applicable for clients
                'max_open_offers' => 999, // Unlimited
                'max_applications' => 0, // Not applicable for clients
                'max_messages' => 99999, // Unlimited for practical purposes
                'features' => json_encode([
                    'Profil avancé',
                    'Création d\'offres illimitées',
                    'Messagerie illimitée',
                    'Support par email',
                    'Tous les tarifs sont accessibles',
                ])
            ]
        ];

        foreach ($plans as $plan) {
            DB::table('plans')->insert($plan);
        }
    }
}
