<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Création du Super Administrateur...');

        $email = 'superadmin@hi3d.com';

        // Vérifier si le super admin existe déjà
        $existingUser = User::where('email', $email)->first();

        if ($existingUser) {
            $this->command->warn("⚠️  Un utilisateur avec l'email {$email} existe déjà.");

            // Mettre à jour le rôle si ce n'est pas déjà un super admin
            if ($existingUser->role !== 'super_admin') {
                $existingUser->update(['role' => 'super_admin']);
                $this->command->info("✅ Rôle mis à jour vers 'super_admin' pour l'utilisateur existant.");
            } else {
                $this->command->info("ℹ️  L'utilisateur est déjà un super administrateur.");
            }

            $this->displayUserInfo($existingUser);
            return;
        }

        try {
            // Créer le super administrateur
            $superAdmin = User::create([
                'first_name' => 'Super',
                'last_name' => 'Admin',
                'email' => $email,
                'password' => Hash::make('superadmin123'),
                'email_verified_at' => now(),
                'is_professional' => false,
                'profile_completed' => true,
                'role' => 'super_admin',
            ]);

            $this->command->info('✅ Super Administrateur créé avec succès !');
            $this->displayUserInfo($superAdmin);

        } catch (\Exception $e) {
            $this->command->error('❌ Erreur lors de la création du Super Administrateur : ' . $e->getMessage());
        }
    }

    /**
     * Afficher les informations de l'utilisateur
     */
    private function displayUserInfo(User $user): void
    {
        $this->command->newLine();
        $this->command->table(
            ['Champ', 'Valeur'],
            [
                ['ID', $user->id],
                ['Nom complet', $user->first_name . ' ' . $user->last_name],
                ['Email', $user->email],
                ['Rôle', $user->role],
                ['Email vérifié', $user->email_verified_at ? 'Oui' : 'Non'],
                ['Profil complété', $user->profile_completed ? 'Oui' : 'Non'],
                ['Créé le', $user->created_at->format('d/m/Y H:i:s')],
                ['Modifié le', $user->updated_at->format('d/m/Y H:i:s')],
            ]
        );

        $this->command->newLine();
        $this->command->info('🌐 Informations de connexion :');
        $this->command->info('URL du back-office : ' . config('app.url') . '/admin');
        $this->command->info('Email : ' . $user->email);
        $this->command->info('Mot de passe : superadmin123');
        $this->command->newLine();
    }
}
