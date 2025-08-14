<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Achievement;
use App\Models\ServiceOffer;
use App\Models\ProfessionalProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GlobalSearchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test data
        $this->createTestData();
    }

    private function createTestData()
    {
        // Create a professional user
        $professional = User::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Developer',
            'email' => 'john@example.com',
            'is_professional' => true,
        ]);

        // Create a professional profile
        $profile = ProfessionalProfile::factory()->create([
            'user_id' => $professional->id,
            'first_name' => 'John',
            'last_name' => 'Developer',
            'title' => 'Full Stack Developer',
            'profession' => 'Web Development',
            'bio' => 'Experienced developer specializing in Laravel and React',
            'city' => 'Paris',
            'country' => 'France',
            'skills' => ['PHP', 'Laravel', 'React', 'JavaScript'],
            'completion_percentage' => 80,
            'availability_status' => 'available',
        ]);

        // Create a service offer
        ServiceOffer::factory()->create([
            'user_id' => $professional->id,
            'title' => 'Laravel Web Application Development',
            'description' => 'I will create a custom Laravel web application for your business',
            'price' => 500.00,
            'status' => 'active',
            'is_private' => false,
            'categories' => ['Web Development', 'Laravel'],
        ]);

        // Create an achievement - Use the actual profile ID
        Achievement::factory()->create([
            'professional_profile_id' => $profile->id,
            'title' => 'Laravel Certified Developer',
            'organization' => 'Laravel',
            'description' => 'Certified Laravel developer with advanced skills',
        ]);
    }

    public function test_global_search_endpoint(): void
    {
        $response = $this->getJson('/api/search?q=Laravel');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'query',
                        'total_count',
                        'results_by_type',
                        'combined_results',
                        'pagination',
                    ],
                ]);
    }

    public function test_search_professionals_endpoint(): void
    {
        $response = $this->getJson('/api/search/professionals?q=Developer');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'query',
                        'count',
                        'results',
                    ],
                ]);
    }

    public function test_search_services_endpoint(): void
    {
        $response = $this->getJson('/api/search/services?q=Laravel');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'query',
                        'count',
                        'results',
                    ],
                ]);
    }

    public function test_search_achievements_endpoint(): void
    {
        $response = $this->getJson('/api/search/achievements?q=Laravel');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'query',
                        'count',
                        'results',
                    ],
                ]);
    }

    public function test_search_suggestions_endpoint(): void
    {
        $response = $this->getJson('/api/search/suggestions?q=Lar');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'query',
                        'suggestions',
                    ],
                ]);
    }

    public function test_search_stats_endpoint(): void
    {
        $response = $this->getJson('/api/search/stats');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'total_professionals',
                        'total_services',
                        'total_achievements',
                        'searchable_professionals',
                        'active_services',
                    ],
                ]);
    }

    public function test_search_validation(): void
    {
        // Test missing query parameter
        $response = $this->getJson('/api/search');
        $response->assertStatus(422);

        // Test query too short
        $response = $this->getJson('/api/search?q=a');
        $response->assertStatus(422);

        // Test invalid type filter
        $response = $this->getJson('/api/search?q=test&types[]=invalid_type');
        $response->assertStatus(422);
    }

    public function test_search_with_filters(): void
    {
        $response = $this->getJson('/api/search/professionals?q=Developer&filters[city]=Paris&filters[availability_status]=available');

        $response->assertStatus(200);
    }
}
