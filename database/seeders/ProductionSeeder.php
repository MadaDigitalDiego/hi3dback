<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductionSeeder extends Seeder
{
    /**
     * Run the database seeder for production environment.
     * Includes only essential data needed for production.
     */
    public function run(): void
    {
        $this->command->info('🚀 Initialisation des données de production Hi3D...');
        $this->command->newLine();

        // Seeders essentiels pour la production
        $this->call([
            SuperAdminSeeder::class,
            PlanSeeder::class,
            CategorySeeder::class,
        ]);

        $this->command->newLine();
        $this->command->info('✅ Données de production initialisées avec succès !');
        $this->command->newLine();

        $this->command->info('📋 Résumé des données créées :');
        $this->command->info('• Super Administrateur : superadmin@hi3d.com');
        $this->command->info('• Plans d\'abonnement : Configurés');
        $this->command->info('• Catégories : ' . \App\Models\Category::count() . ' catégories créées');
        $this->command->newLine();

        $this->command->info('🌐 Accès au back-office :');
        $this->command->info('URL : ' . config('app.url') . '/admin');
        $this->command->info('Email : superadmin@hi3d.com');
        $this->command->info('Mot de passe : superadmin123');
        $this->command->newLine();
    }
}
