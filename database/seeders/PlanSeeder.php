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
                'price' => 0.00,
                'description' => 'Pour les artistes qui débutent',
                'stripe_product_id' => 'prod_SNWXPVxEHnr8gw',
                'stripe_price_id' => 'price_1RSlU5FKK6JoGdxmf9PuiJfw',
                'interval' => 'month',
                'interval_count' => 1,
                'is_active' => true,
                'features' => json_encode([
                    'Profil de base',
                    'Portfolio limité (3 projets)',
                    'Accès aux projets publics',
                    'Messagerie de base',
                    'Support communautaire',
                    'Projets premium',
                    'Mise en avant du profil',
                    'Support prioritaire'
                ])
            ],
            [
                'title' => 'Abonnement Pro hi3d',
                'name' => 'pro',
                'price' => 19.99,
                'description' => 'Pour les artistes professionnels',
                'stripe_product_id' => 'prod_SNWbF8gvSLABUt',
                'stripe_price_id' => 'price_1RSlXxFKK6JoGdxmSv8SKhAk',
                'interval' => 'month',
                'interval_count' => 1,
                'is_active' => true,
                'features' => json_encode([
                    'Profil avancé',
                    'Portfolio illimité',
                    'Accès aux projets publics et privés',
                    'Messagerie avancée',
                    'Support par email',
                    'Accès aux projets premium',
                    'Mise en avant du profil',
                    'Support prioritaire'
                ])
            ],
            [
                'title' => 'Abonnement Entreprise hi3d',
                'name' => 'enterprise',
                'price' => 49.99,
                'description' => 'Pour les studios et agences',
                'stripe_product_id' => 'prod_SNWdgusveP2aUL',
                'stripe_price_id' => 'price_1RSlZpFKK6JoGdxmRq9XwONW',
                'interval' => 'month',
                'interval_count' => 1,
                'is_active' => true,
                'features' => json_encode([
                    'Profils multiples',
                    'Portfolio illimité',
                    'Accès à tous les projets',
                    'Messagerie avancée avec CRM',
                    'Support dédié',
                    'Accès aux projets premium',
                    'Mise en avant des profils',
                    'Support prioritaire 24/7'
                ])
            ]
        ];

        foreach ($plans as $plan) {
            DB::table('plans')->insert($plan);
        }
    }
}
