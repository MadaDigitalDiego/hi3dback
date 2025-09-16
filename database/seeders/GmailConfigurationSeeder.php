<?php

namespace Database\Seeders;

use App\Models\GmailConfiguration;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GmailConfigurationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer une configuration de test (à remplacer par les vraies valeurs)
        GmailConfiguration::create([
            'name' => 'Configuration Gmail de Test',
            'client_id' => 'your-google-client-id.apps.googleusercontent.com',
            'client_secret' => 'your-google-client-secret',
            'redirect_uri' => url('/api/auth/gmail/callback'),
            'scopes' => ['openid', 'profile', 'email'],
            'is_active' => true,
            'description' => 'Configuration de test pour l\'authentification Gmail. Remplacez les valeurs par vos vraies clés Google OAuth.',
        ]);
    }
}
