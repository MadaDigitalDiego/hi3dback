<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\OpenOffer;
use App\Models\ServiceOffer;
use App\Models\Message;
use App\Models\Contact;
use App\Models\OfferApplication;
use App\Models\ProfessionalProfile;
use App\Models\ClientProfile;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;

class DemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Création de données de démonstration...');

        // Créer quelques utilisateurs de test
        $professional1 = User::create([
            'first_name' => 'Jean',
            'last_name' => 'Dupont',
            'email' => 'jean.dupont@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'is_professional' => true,
            'profile_completed' => true,
            'role' => 'user',
        ]);

        $professional2 = User::create([
            'first_name' => 'Marie',
            'last_name' => 'Martin',
            'email' => 'marie.martin@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'is_professional' => true,
            'profile_completed' => true,
            'role' => 'user',
        ]);

        $client1 = User::create([
            'first_name' => 'Pierre',
            'last_name' => 'Durand',
            'email' => 'pierre.durand@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'is_professional' => false,
            'profile_completed' => true,
            'role' => 'user',
        ]);

        $client2 = User::create([
            'first_name' => 'Sophie',
            'last_name' => 'Leroy',
            'email' => 'sophie.leroy@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'is_professional' => false,
            'profile_completed' => false,
            'role' => 'user',
        ]);

        // Créer des profils professionnels
        ProfessionalProfile::create([
            'user_id' => $professional1->id,
            'first_name' => $professional1->first_name,
            'last_name' => $professional1->last_name,
            'email' => $professional1->email,
            'phone' => '+33 1 23 45 67 89',
            'address' => '123 Rue de la Paix',
            'city' => 'Paris',
            'country' => 'France',
            'bio' => 'Architecte 3D spécialisé dans la visualisation architecturale.',
        ]);

        ProfessionalProfile::create([
            'user_id' => $professional2->id,
            'first_name' => $professional2->first_name,
            'last_name' => $professional2->last_name,
            'email' => $professional2->email,
            'phone' => '+33 2 34 56 78 90',
            'address' => '456 Avenue des Champs',
            'city' => 'Lyon',
            'country' => 'France',
            'bio' => 'Designer 3D freelance avec 5 ans d\'expérience.',
        ]);

        // Créer des profils clients
        ClientProfile::create([
            'user_id' => $client1->id,
            'first_name' => $client1->first_name,
            'last_name' => $client1->last_name,
            'email' => $client1->email,
            'phone' => '+33 3 45 67 89 01',
            'address' => '789 Boulevard Saint-Germain',
            'city' => 'Marseille',
            'country' => 'France',
        ]);

        ClientProfile::create([
            'user_id' => $client2->id,
            'first_name' => $client2->first_name,
            'last_name' => $client2->last_name,
            'email' => $client2->email,
            'phone' => '+33 4 56 78 90 12',
            'address' => '321 Rue de Rivoli',
            'city' => 'Toulouse',
            'country' => 'France',
        ]);

        // Créer quelques offres ouvertes
        OpenOffer::create([
            'user_id' => $client1->id,
            'title' => 'Modélisation 3D d\'une maison moderne',
            'description' => 'Je recherche un professionnel pour créer une modélisation 3D complète d\'une maison moderne de 150m².',
            'budget' => '2000-3000€',
            'deadline' => '2025-08-15',
            'company' => 'Architecture Moderne SARL',
            'status' => 'active',
        ]);

        OpenOffer::create([
            'user_id' => $client2->id,
            'title' => 'Rendu 3D pour projet commercial',
            'description' => 'Besoin de rendus 3D photoréalistes pour un centre commercial.',
            'budget' => '5000-8000€',
            'deadline' => '2025-09-01',
            'company' => 'Commercial Design',
            'status' => 'active',
        ]);

        // Créer quelques services
        ServiceOffer::create([
            'user_id' => $professional1->id,
            'title' => 'Modélisation 3D architecturale',
            'description' => 'Service de modélisation 3D pour projets architecturaux résidentiels et commerciaux.',
            'price' => 1500.00,
            'execution_time' => '2-3 semaines',
            'concepts' => 3,
            'revisions' => 2,
            'status' => 'active',
        ]);

        ServiceOffer::create([
            'user_id' => $professional2->id,
            'title' => 'Rendu photoréaliste',
            'description' => 'Création de rendus 3D photoréalistes pour vos projets.',
            'price' => 800.00,
            'execution_time' => '1-2 semaines',
            'concepts' => 2,
            'revisions' => 3,
            'status' => 'active',
        ]);

        // Créer quelques contacts
        Contact::create([
            'user_id' => $client1->id,
            'name' => 'Pierre Durand',
            'email' => 'pierre.durand@example.com',
            'phone' => '+33 3 45 67 89 01',
            'notes' => 'Intéressé par nos services de modélisation 3D.',
        ]);

        Contact::create([
            'user_id' => $professional1->id,
            'name' => 'Jean Dupont',
            'email' => 'jean.dupont@example.com',
            'phone' => '+33 1 23 45 67 89',
            'notes' => 'Professionnel expérimenté en architecture 3D.',
        ]);

        // Créer quelques messages
        Message::create([
            'sender_id' => $client1->id,
            'receiver_id' => $professional1->id,
            'message_text' => 'Bonjour, je suis intéressé par vos services de modélisation 3D.',
            'is_read' => false,
        ]);

        Message::create([
            'sender_id' => $professional1->id,
            'receiver_id' => $client1->id,
            'message_text' => 'Bonjour, merci pour votre intérêt. Je serais ravi de discuter de votre projet.',
            'is_read' => true,
        ]);

        $this->command->info('Données de démonstration créées avec succès !');
        $this->command->info('- 4 utilisateurs (2 professionnels, 2 clients)');
        $this->command->info('- 4 profils (2 professionnels, 2 clients)');
        $this->command->info('- 2 offres ouvertes');
        $this->command->info('- 2 services');
        $this->command->info('- 2 contacts');
        $this->command->info('- 2 messages');
    }
}
