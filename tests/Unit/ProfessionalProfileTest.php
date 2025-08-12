<?php

namespace Tests\Unit;

use App\Models\ProfessionalProfile;
use App\Models\User;
use App\Models\Experience;
use App\Models\Achievement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfessionalProfileTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_belongs_to_a_user()
    {
        // Créer un utilisateur
        $user = User::factory()->create([
            'is_professional' => true
        ]);

        // Créer un profil professionnel associé à l'utilisateur
        $profile = ProfessionalProfile::create([
            'user_id' => $user->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'profession' => 'Developer',
            'expertise' => ['PHP', 'Laravel', 'Vue.js'],
            'years_of_experience' => 5,
            'hourly_rate' => 50.00,
            'skills' => ['PHP', 'Laravel', 'Vue.js', 'JavaScript'],
            'availability_status' => ProfessionalProfile::AVAILABILITY_AVAILABLE,
        ]);

        // Vérifier que la relation fonctionne correctement
        $this->assertInstanceOf(User::class, $profile->user);
        $this->assertEquals($user->id, $profile->user->id);
    }

    /** @test */
    public function it_has_many_experiences()
    {
        // Créer un utilisateur
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
        ]);

        // Créer des expériences associées au profil
        $experience1 = Experience::create([
            'professional_profile_id' => $profile->id,
            'title' => 'Senior Developer',
            'company' => 'Acme Inc.',
            'start_date' => '2018-01-01',
            'end_date' => '2020-12-31',
            'description' => 'Worked on various projects',
        ]);

        $experience2 = Experience::create([
            'professional_profile_id' => $profile->id,
            'title' => 'Lead Developer',
            'company' => 'XYZ Corp',
            'start_date' => '2021-01-01',
            'end_date' => null, // Emploi actuel
            'description' => 'Leading a team of developers',
        ]);

        // Vérifier que la relation fonctionne correctement
        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $profile->experiences);
        $this->assertCount(2, $profile->experiences);
        $this->assertTrue($profile->experiences->contains($experience1));
        $this->assertTrue($profile->experiences->contains($experience2));
    }

    /** @test */
    public function it_has_many_achievements()
    {
        // Créer un utilisateur
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
        ]);

        // Créer des réalisations associées au profil
        $achievement1 = Achievement::create([
            'professional_profile_id' => $profile->id,
            'title' => 'Laravel Certification',
            'issuer' => 'Laravel',
            'date' => '2019-05-15',
            'description' => 'Certified Laravel Developer',
        ]);

        $achievement2 = Achievement::create([
            'professional_profile_id' => $profile->id,
            'title' => 'Vue.js Certification',
            'issuer' => 'Vue.js',
            'date' => '2020-08-20',
            'description' => 'Certified Vue.js Developer',
        ]);

        // Vérifier que la relation fonctionne correctement
        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $profile->achievements);
        $this->assertCount(2, $profile->achievements);
        $this->assertTrue($profile->achievements->contains($achievement1));
        $this->assertTrue($profile->achievements->contains($achievement2));
    }

    /** @test */
    public function it_casts_attributes_correctly()
    {
        // Créer un utilisateur
        $user = User::factory()->create([
            'is_professional' => true
        ]);

        // Créer un profil professionnel avec des attributs qui doivent être castés
        $profile = ProfessionalProfile::create([
            'user_id' => $user->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'expertise' => ['PHP', 'Laravel', 'Vue.js'],
            'years_of_experience' => '5', // Devrait être casté en integer
            'hourly_rate' => '50.00', // Devrait être casté en decimal
            'skills' => ['PHP', 'Laravel', 'Vue.js', 'JavaScript'],
            'portfolio' => [
                [
                    'id' => '1',
                    'path' => '/storage/portfolio/image1.jpg',
                    'name' => 'Project 1',
                    'description' => 'Description of project 1',
                    'type' => 'image/jpeg',
                    'created_at' => '2023-01-01 12:00:00',
                ]
            ],
            'languages' => ['French', 'English', 'Spanish'],
            'services_offered' => ['Web Development', 'Mobile Development'],
            'social_links' => [
                'linkedin' => 'https://linkedin.com/in/johndoe',
                'github' => 'https://github.com/johndoe',
            ],
            'rating' => '4.5', // Devrait être casté en decimal
            'completion_percentage' => '85', // Devrait être casté en integer
        ]);

        // Vérifier que les attributs sont correctement castés
        $this->assertIsArray($profile->expertise);
        $this->assertIsInt($profile->years_of_experience);
        $this->assertIsFloat($profile->hourly_rate);
        $this->assertIsArray($profile->skills);
        $this->assertIsArray($profile->portfolio);
        $this->assertIsArray($profile->languages);
        $this->assertIsArray($profile->services_offered);
        $this->assertIsArray($profile->social_links);
        $this->assertIsFloat($profile->rating);
        $this->assertIsInt($profile->completion_percentage);
    }
}
