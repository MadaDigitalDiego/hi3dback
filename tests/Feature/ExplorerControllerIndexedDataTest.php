<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\ProfessionalProfile;
use App\Models\ServiceOffer;
use App\Models\Achievement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ExplorerControllerIndexedDataTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test users
        $this->user1 = User::factory()->create();
        $this->user2 = User::factory()->create();
        
        // Create professional profiles
        $this->professional1 = ProfessionalProfile::factory()->create([
            'user_id' => $this->user1->id,
            'completion_percentage' => 90,
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);
        
        $this->professional2 = ProfessionalProfile::factory()->create([
            'user_id' => $this->user2->id,
            'completion_percentage' => 85,
            'first_name' => 'Jane',
            'last_name' => 'Smith',
        ]);
        
        // Create incomplete profile (should not be indexed)
        $this->incompleteProfile = ProfessionalProfile::factory()->create([
            'user_id' => User::factory()->create()->id,
            'completion_percentage' => 50,
        ]);
        
        // Create service offers
        $this->service1 = ServiceOffer::factory()->create([
            'user_id' => $this->user1->id,
            'title' => 'Web Development',
            'is_private' => false,
        ]);
        
        $this->service2 = ServiceOffer::factory()->create([
            'user_id' => $this->user2->id,
            'title' => 'UI Design',
            'is_private' => false,
        ]);
        
        // Create private service (should not be indexed)
        $this->privateService = ServiceOffer::factory()->create([
            'user_id' => $this->user1->id,
            'title' => 'Private Service',
            'is_private' => true,
        ]);
        
        // Create achievements
        $this->achievement1 = Achievement::factory()->create([
            'professional_profile_id' => $this->professional1->id,
            'title' => 'Award Winner',
            'status' => 'published',
        ]);
        
        $this->achievement2 = Achievement::factory()->create([
            'professional_profile_id' => $this->professional2->id,
            'title' => 'Certification',
            'status' => 'published',
        ]);
        
        // Create draft achievement (should not be indexed)
        $this->draftAchievement = Achievement::factory()->create([
            'professional_profile_id' => $this->professional1->id,
            'title' => 'Draft Achievement',
            'status' => 'draft',
        ]);
    }

    /** @test */
    public function test_list_indexed_data_returns_all_data()
    {
        $response = $this->getJson('/api/explorer/indexed-data');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        '*' => [
                            'id',
                            'type',
                            'index',
                            'data',
                            'created_at',
                            'updated_at',
                        ]
                    ],
                    'pagination' => [
                        'total',
                        'per_page',
                        'current_page',
                        'last_page',
                    ],
                    'index_stats' => [
                        'professional_profiles_index',
                        'service_offers_index',
                        'achievements_index',
                    ],
                    'performance' => [
                        'total_execution_time_ms',
                    ]
                ]);

        $this->assertTrue($response->json('success'));
        $this->assertGreaterThan(0, $response->json('pagination.total'));
    }

    /** @test */
    public function test_list_indexed_data_filters_by_professional_profiles_index()
    {
        $response = $this->getJson('/api/explorer/indexed-data?index=professional_profiles_index');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data',
                    'pagination',
                    'index_stats',
                ]);

        $this->assertTrue($response->json('success'));
        
        // Verify all returned items are professional profiles
        $data = $response->json('data');
        foreach ($data as $item) {
            $this->assertEquals('professional_profile', $item['type']);
            $this->assertEquals('professional_profiles_index', $item['index']);
        }
        
        // Verify only indexed profiles are returned (completion_percentage >= 80)
        $this->assertCount(2, $data);
    }

    /** @test */
    public function test_list_indexed_data_filters_by_service_offers_index()
    {
        $response = $this->getJson('/api/explorer/indexed-data?index=service_offers_index');

        $response->assertStatus(200);
        $this->assertTrue($response->json('success'));
        
        // Verify all returned items are service offers
        $data = $response->json('data');
        foreach ($data as $item) {
            $this->assertEquals('service_offer', $item['type']);
            $this->assertEquals('service_offers_index', $item['index']);
        }
        
        // Verify only public services are returned
        $this->assertCount(2, $data);
    }

    /** @test */
    public function test_list_indexed_data_filters_by_achievements_index()
    {
        $response = $this->getJson('/api/explorer/indexed-data?index=achievements_index');

        $response->assertStatus(200);
        $this->assertTrue($response->json('success'));
        
        // Verify all returned items are achievements
        $data = $response->json('data');
        foreach ($data as $item) {
            $this->assertEquals('achievement', $item['type']);
            $this->assertEquals('achievements_index', $item['index']);
        }
        
        // Verify only published achievements are returned
        $this->assertCount(2, $data);
    }

    /** @test */
    public function test_list_indexed_data_pagination()
    {
        $response = $this->getJson('/api/explorer/indexed-data?per_page=2&page=1');

        $response->assertStatus(200);
        $this->assertTrue($response->json('success'));
        
        $pagination = $response->json('pagination');
        $this->assertEquals(1, $pagination['current_page']);
        $this->assertEquals(2, $pagination['per_page']);
        $this->assertLessThanOrEqual(2, count($response->json('data')));
    }

    /** @test */
    public function test_list_indexed_data_includes_index_stats()
    {
        $response = $this->getJson('/api/explorer/indexed-data');

        $response->assertStatus(200);
        
        $stats = $response->json('index_stats');
        $this->assertArrayHasKey('professional_profiles_index', $stats);
        $this->assertArrayHasKey('service_offers_index', $stats);
        $this->assertArrayHasKey('achievements_index', $stats);
        
        // Verify counts
        $this->assertEquals(2, $stats['professional_profiles_index']['count']);
        $this->assertEquals(2, $stats['service_offers_index']['count']);
        $this->assertEquals(2, $stats['achievements_index']['count']);
    }

    /** @test */
    public function test_list_indexed_data_includes_performance_metrics()
    {
        $response = $this->getJson('/api/explorer/indexed-data');

        $response->assertStatus(200);
        
        $performance = $response->json('performance');
        $this->assertArrayHasKey('total_execution_time_ms', $performance);
        $this->assertGreaterThan(0, $performance['total_execution_time_ms']);
    }

    /** @test */
    public function test_list_indexed_data_professional_profile_structure()
    {
        $response = $this->getJson('/api/explorer/indexed-data?index=professional_profiles_index');

        $response->assertStatus(200);
        
        $data = $response->json('data');
        $this->assertGreaterThan(0, count($data));
        
        $firstItem = $data[0];
        $this->assertArrayHasKey('data', $firstItem);
        
        $profileData = $firstItem['data'];
        $this->assertArrayHasKey('id', $profileData);
        $this->assertArrayHasKey('first_name', $profileData);
        $this->assertArrayHasKey('last_name', $profileData);
        $this->assertArrayHasKey('skills', $profileData);
        $this->assertArrayHasKey('rating', $profileData);
    }

    /** @test */
    public function test_list_indexed_data_service_offer_structure()
    {
        $response = $this->getJson('/api/explorer/indexed-data?index=service_offers_index');

        $response->assertStatus(200);
        
        $data = $response->json('data');
        $this->assertGreaterThan(0, count($data));
        
        $firstItem = $data[0];
        $this->assertArrayHasKey('data', $firstItem);
        
        $serviceData = $firstItem['data'];
        $this->assertArrayHasKey('id', $serviceData);
        $this->assertArrayHasKey('title', $serviceData);
        $this->assertArrayHasKey('price', $serviceData);
        $this->assertArrayHasKey('rating', $serviceData);
    }

    /** @test */
    public function test_list_indexed_data_achievement_structure()
    {
        $response = $this->getJson('/api/explorer/indexed-data?index=achievements_index');

        $response->assertStatus(200);
        
        $data = $response->json('data');
        $this->assertGreaterThan(0, count($data));
        
        $firstItem = $data[0];
        $this->assertArrayHasKey('data', $firstItem);
        
        $achievementData = $firstItem['data'];
        $this->assertArrayHasKey('id', $achievementData);
        $this->assertArrayHasKey('title', $achievementData);
        $this->assertArrayHasKey('category', $achievementData);
    }
}

