<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\ProfessionalProfile;
use App\Models\ClientProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class ProfileApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** @test */
    public function unauthenticated_users_cannot_access_profile_api()
    {
        // Tenter d'accéder à l'API de profil sans être authentifié
        $response = $this->getJson('/api/profile');

        // Vérifier que l'accès est refusé
        $response->assertStatus(401);
    }

    /** @test */
    public function professional_user_can_get_their_profile()
    {
        // Créer un utilisateur professionnel
        $user = User::factory()->create([
            'is_professional' => true
        ]);

        // Créer un profil professionnel
        $profile = ProfessionalProfile::create([
            'user_id' => $user->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'profession' => 'Developer',
            'expertise' => ['PHP', 'Laravel', 'Vue.js'],
            'years_of_experience' => 5,
            'hourly_rate' => 50.00,
            'skills' => ['Backend', 'Frontend'],
            'softwares' => ['Figma', 'Blender'],
            'availability_status' => ProfessionalProfile::AVAILABILITY_AVAILABLE,
        ]);

        // Authentifier l'utilisateur
        Sanctum::actingAs($user);

        // Accéder à l'API de profil
        $response = $this->getJson('/api/profile');

        // Vérifier que la réponse est correcte
        $response->assertStatus(200)
            ->assertJsonStructure([
                'profile' => [
                    'id',
                    'user_id',
                    'first_name',
                    'last_name',
                    'email',
                    'profession',
                        'expertise',
                        'years_of_experience',
                        'hourly_rate',
                        'skills',
                        'softwares',
                        'availability_status',
                    'user' => [
                        'id',
                        'first_name',
                        'last_name',
                        'email',
                        'is_professional',
                    ]
                ]
            ])
            ->assertJson([
                'profile' => [
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                    'email' => 'john.doe@example.com',
                    'profession' => 'Developer',
                    'years_of_experience' => 5,
                    'hourly_rate' => '50.00',
                    'skills' => ['Backend', 'Frontend'],
                    'softwares' => ['Figma', 'Blender'],
                    'availability_status' => ProfessionalProfile::AVAILABILITY_AVAILABLE,
                ]
            ]);
    }

    /** @test */
    public function client_user_can_get_their_profile()
    {
        // Créer un utilisateur client
        $user = User::factory()->create([
            'is_professional' => false
        ]);

        // Créer un profil client
        $profile = ClientProfile::create([
            'user_id' => $user->id,
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane.smith@example.com',
            'type' => 'entreprise',
            'company_name' => 'Smith Enterprises',
            'industry' => 'Technology',
            'company_size' => '11-50',
        ]);

        // Authentifier l'utilisateur
        Sanctum::actingAs($user);

        // Accéder à l'API de profil
        $response = $this->getJson('/api/profile');

        // Vérifier que la réponse est correcte
        $response->assertStatus(200)
            ->assertJsonStructure([
                'profile' => [
                    'id',
                    'user_id',
                    'first_name',
                    'last_name',
                    'email',
                    'type',
                    'company_name',
                    'industry',
                    'company_size',
                    'user' => [
                        'id',
                        'first_name',
                        'last_name',
                        'email',
                        'is_professional',
                    ]
                ]
            ])
            ->assertJson([
                'profile' => [
                    'first_name' => 'Jane',
                    'last_name' => 'Smith',
                    'email' => 'jane.smith@example.com',
                    'type' => 'entreprise',
                    'company_name' => 'Smith Enterprises',
                    'industry' => 'Technology',
                    'company_size' => '11-50',
                ]
            ]);
    }

    /** @test */
    public function professional_user_can_update_their_profile()
    {
        // Créer un utilisateur professionnel
        $user = User::factory()->create([
            'is_professional' => true
        ]);

        // Créer un profil professionnel
        $profile = ProfessionalProfile::create([
            'user_id' => $user->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'profession' => 'Developer',
            'expertise' => ['PHP', 'Laravel', 'Vue.js'],
            'years_of_experience' => 5,
            'hourly_rate' => 50.00,
        ]);

        // Authentifier l'utilisateur
        Sanctum::actingAs($user);

        // Données de mise à jour
        $updateData = [
            'profession' => 'Senior Developer',
            'expertise' => ['PHP', 'Laravel', 'Vue.js', 'React'],
            'years_of_experience' => 7,
            'hourly_rate' => 65.00,
            'bio' => 'Développeur expérimenté spécialisé dans les applications web modernes.',
        ];

        // Mettre à jour le profil via l'API
        $response = $this->putJson('/api/profile', $updateData);

        // Vérifier que la réponse est correcte
        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'profile' => [
                    'id',
                    'user_id',
                    'first_name',
                    'last_name',
                    'email',
                    'profession',
                    'expertise',
                    'years_of_experience',
                    'hourly_rate',
                    'bio',
                ]
            ])
            ->assertJson([
                'message' => 'Profil mis à jour avec succès.',
                'profile' => [
                    'profession' => 'Senior Developer',
                    'years_of_experience' => 7,
                    'hourly_rate' => '65.00',
                    'bio' => 'Développeur expérimenté spécialisé dans les applications web modernes.',
                ]
            ]);

        // Vérifier que les données ont été mises à jour dans la base de données
        $this->assertDatabaseHas('professional_profiles', [
            'user_id' => $user->id,
            'profession' => 'Senior Developer',
            'years_of_experience' => 7,
            'hourly_rate' => 65.00,
            'bio' => 'Développeur expérimenté spécialisé dans les applications web modernes.',
        ]);
    }

    /** @test */
    public function client_user_can_update_their_profile()
    {
        // Créer un utilisateur client
        $user = User::factory()->create([
            'is_professional' => false
        ]);

        // Créer un profil client
        $profile = ClientProfile::create([
            'user_id' => $user->id,
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane.smith@example.com',
            'type' => 'entreprise',
            'company_name' => 'Smith Enterprises',
            'industry' => 'Technology',
            'company_size' => '11-50',
        ]);

        // Authentifier l'utilisateur
        Sanctum::actingAs($user);

        // Données de mise à jour
        $updateData = [
            'company_name' => 'Smith Technologies',
            'industry' => 'Information Technology',
            'company_size' => '51-200',
            'position' => 'CTO',
            'website' => 'https://smithtech.com',
            'bio' => 'Entreprise spécialisée dans le développement de solutions technologiques innovantes.',
        ];

        // Mettre à jour le profil via l'API
        $response = $this->putJson('/api/profile', $updateData);

        // Vérifier que la réponse est correcte
        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'profile' => [
                    'id',
                    'user_id',
                    'first_name',
                    'last_name',
                    'email',
                    'type',
                    'company_name',
                    'industry',
                    'company_size',
                    'position',
                    'website',
                    'bio',
                ]
            ])
            ->assertJson([
                'message' => 'Profil mis à jour avec succès.',
                'profile' => [
                    'company_name' => 'Smith Technologies',
                    'industry' => 'Information Technology',
                    'company_size' => '51-200',
                    'position' => 'CTO',
                    'website' => 'https://smithtech.com',
                    'bio' => 'Entreprise spécialisée dans le développement de solutions technologiques innovantes.',
                ]
            ]);

        // Vérifier que les données ont été mises à jour dans la base de données
        $this->assertDatabaseHas('client_profiles', [
            'user_id' => $user->id,
            'company_name' => 'Smith Technologies',
            'industry' => 'Information Technology',
            'company_size' => '51-200',
            'position' => 'CTO',
            'website' => 'https://smithtech.com',
            'bio' => 'Entreprise spécialisée dans le développement de solutions technologiques innovantes.',
        ]);
    }

    /** @test */
    public function user_can_get_profile_completion_status()
    {
        // Créer un utilisateur professionnel
        $user = User::factory()->create([
            'is_professional' => true
        ]);

        // Créer un profil professionnel avec un pourcentage de complétion
        $profile = ProfessionalProfile::create([
            'user_id' => $user->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'profession' => 'Developer',
            'completion_percentage' => 65,
        ]);

        // Authentifier l'utilisateur
        Sanctum::actingAs($user);

        // Accéder à l'API de statut de complétion
        $response = $this->getJson('/api/profile/completion');

        // Vérifier que la réponse est correcte
        $response->assertStatus(200)
            ->assertJsonStructure([
                'completion_percentage',
                'is_completed'
            ])
            ->assertJson([
                'completion_percentage' => 65,
                'is_completed' => false
            ]);
    }

	    /** @test */
	    public function authenticated_user_can_delete_their_account()
	    {
	        // Cr e9er un utilisateur professionnel avec un profil associ e9
	        $user = User::factory()->create([
	            'is_professional' => true,
	        ]);

	        $profile = ProfessionalProfile::create([
	            'user_id' => $user->id,
	            'first_name' => 'John',
	            'last_name' => 'Doe',
	            'email' => 'john.doe@example.com',
	        ]);

	        // Authentifier l'utilisateur
	        Sanctum::actingAs($user);

	        // Supprimer le compte via l'API
	        $response = $this->deleteJson('/api/profile');

	        // V e9rifier la r e9ponse
	        $response->assertStatus(200)
	            ->assertJson([
	                'message' => 'Compte supprim e9 avec succ e8s.',
	            ]);

	        // V e9rifier que l'utilisateur et son profil ont bien  e9t e9 supprim e9s (cascade)
	        $this->assertDatabaseMissing('users', [
	            'id' => $user->id,
	        ]);

	        $this->assertDatabaseMissing('professional_profiles', [
	            'user_id' => $user->id,
	        ]);
	    }

	    /** @test */
	    public function unauthenticated_users_cannot_delete_their_account()
	    {
	        $response = $this->deleteJson('/api/profile');

	        $response->assertStatus(401);
	    }
}
