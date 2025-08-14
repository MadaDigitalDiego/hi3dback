<?php

namespace Tests\Unit;

use App\Models\ClientProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientProfileTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_belongs_to_a_user()
    {
        // Créer un utilisateur
        $user = User::factory()->create([
            'is_professional' => false
        ]);

        // Créer un profil client associé à l'utilisateur
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

        // Vérifier que la relation fonctionne correctement
        $this->assertInstanceOf(User::class, $profile->user);
        $this->assertEquals($user->id, $profile->user->id);
    }

    /** @test */
    public function it_casts_attributes_correctly()
    {
        // Créer un utilisateur
        $user = User::factory()->create([
            'is_professional' => false
        ]);

        // Créer un profil client avec des attributs qui doivent être castés
        $profile = ClientProfile::create([
            'user_id' => $user->id,
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane.smith@example.com',
            'type' => 'entreprise',
            'company_name' => 'Smith Enterprises',
            'industry' => 'Technology',
            'company_size' => '11-50',
            'social_links' => [
                'linkedin' => 'https://linkedin.com/in/janesmith',
                'twitter' => 'https://twitter.com/janesmith',
            ],
            'preferences' => [
                'notifications' => true,
                'newsletter' => false,
            ],
            'birth_date' => '1985-05-15', // Devrait être casté en date
            'completion_percentage' => '75', // Devrait être casté en integer
        ]);

        // Vérifier que les attributs sont correctement castés
        $this->assertIsArray($profile->social_links);
        $this->assertIsArray($profile->preferences);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $profile->birth_date);
        $this->assertIsInt($profile->completion_percentage);
    }

    /** @test */
    public function it_can_be_created_for_individual_client()
    {
        // Créer un utilisateur
        $user = User::factory()->create([
            'is_professional' => false
        ]);

        // Créer un profil client de type particulier
        $profile = ClientProfile::create([
            'user_id' => $user->id,
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane.smith@example.com',
            'type' => 'particulier',
            'phone' => '+33 6 12 34 56 78',
            'address' => '123 Rue de Paris',
            'city' => 'Paris',
            'country' => 'France',
            'bio' => 'Je suis un particulier à la recherche de professionnels pour mes projets.',
        ]);

        // Vérifier que le profil a été créé correctement
        $this->assertEquals('particulier', $profile->type);
        $this->assertEquals('Jane', $profile->first_name);
        $this->assertEquals('Smith', $profile->last_name);
        $this->assertEquals('+33 6 12 34 56 78', $profile->phone);
        $this->assertNull($profile->company_name);
        $this->assertNull($profile->industry);
    }

    /** @test */
    public function it_can_be_created_for_company_client()
    {
        // Créer un utilisateur
        $user = User::factory()->create([
            'is_professional' => false
        ]);

        // Créer un profil client de type entreprise
        $profile = ClientProfile::create([
            'user_id' => $user->id,
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane.smith@example.com',
            'type' => 'entreprise',
            'company_name' => 'Smith Enterprises',
            'industry' => 'Technology',
            'company_size' => '11-50',
            'position' => 'CEO',
            'website' => 'https://smithenterprises.com',
            'phone' => '+33 1 23 45 67 89',
            'address' => '456 Avenue des Champs-Élysées',
            'city' => 'Paris',
            'country' => 'France',
            'bio' => 'Nous sommes une entreprise de technologie à la recherche de professionnels pour nos projets.',
        ]);

        // Vérifier que le profil a été créé correctement
        $this->assertEquals('entreprise', $profile->type);
        $this->assertEquals('Smith Enterprises', $profile->company_name);
        $this->assertEquals('Technology', $profile->industry);
        $this->assertEquals('11-50', $profile->company_size);
        $this->assertEquals('CEO', $profile->position);
        $this->assertEquals('https://smithenterprises.com', $profile->website);
    }
}
