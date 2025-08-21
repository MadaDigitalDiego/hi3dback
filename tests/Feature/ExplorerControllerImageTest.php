<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\ProfessionalProfile;
use App\Models\ServiceOffer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ExplorerControllerImageTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Créer un utilisateur avec un profil professionnel
        $this->user = User::factory()->create();
        $this->professional = ProfessionalProfile::factory()->create([
            'user_id' => $this->user->id,
            'completion_percentage' => 90,
        ]);
        
        // Créer des services avec et sans images
        $this->serviceWithImage = ServiceOffer::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Service avec image',
            'image' => 'services/test-image.jpg',
            'is_private' => false,
        ]);
        
        $this->serviceWithoutImage = ServiceOffer::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Service sans image',
            'image' => null,
            'is_private' => false,
        ]);
        
        $this->serviceWithUrlImage = ServiceOffer::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Service avec URL image',
            'image' => 'https://example.com/image.jpg',
            'is_private' => false,
        ]);
    }

    /** @test */
    public function test_get_services_returns_image_field()
    {
        $response = $this->getJson('/api/explorer/services');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'services' => [
                        '*' => [
                            'id',
                            'title',
                            'description',
                            'price',
                            'image', // ✅ Vérifier que le champ image est présent
                            'views',
                            'likes',
                            'rating',
                            'professional'
                        ]
                    ],
                    'pagination'
                ]);

        $services = $response->json('services');
        
        // Vérifier que les services avec images ont des URLs correctes
        $serviceWithImageData = collect($services)->firstWhere('title', 'Service avec image');
        $this->assertNotNull($serviceWithImageData);
        $this->assertStringContains('storage/services/test-image.jpg', $serviceWithImageData['image']);
        
        // Vérifier que les services sans image ont null
        $serviceWithoutImageData = collect($services)->firstWhere('title', 'Service sans image');
        $this->assertNotNull($serviceWithoutImageData);
        $this->assertNull($serviceWithoutImageData['image']);
        
        // Vérifier que les URLs complètes sont conservées
        $serviceWithUrlImageData = collect($services)->firstWhere('title', 'Service avec URL image');
        $this->assertNotNull($serviceWithUrlImageData);
        $this->assertEquals('https://example.com/image.jpg', $serviceWithUrlImageData['image']);
    }

    /** @test */
    public function test_get_professional_details_returns_services_with_images()
    {
        $response = $this->getJson("/api/explorer/professionals/{$this->professional->id}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'professional' => [
                        'id',
                        'first_name',
                        'last_name',
                        'services' => [
                            '*' => [
                                'id',
                                'title',
                                'description',
                                'price',
                                'image', // ✅ Vérifier que le champ image est présent
                                'views',
                                'likes',
                                'rating'
                            ]
                        ]
                    ]
                ]);

        $services = $response->json('professional.services');
        
        // Vérifier que les services avec images ont des URLs correctes
        $serviceWithImageData = collect($services)->firstWhere('title', 'Service avec image');
        $this->assertNotNull($serviceWithImageData);
        $this->assertStringContains('storage/services/test-image.jpg', $serviceWithImageData['image']);
    }

    /** @test */
    public function test_get_professionals_returns_services_with_images()
    {
        $response = $this->getJson('/api/explorer/professionals');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'professionals' => [
                        '*' => [
                            'id',
                            'first_name',
                            'last_name',
                            'services' => [
                                '*' => [
                                    'id',
                                    'title',
                                    'description',
                                    'price',
                                    'image', // ✅ Vérifier que le champ image est présent
                                    'views',
                                    'likes',
                                    'rating'
                                ]
                            ]
                        ]
                    ],
                    'pagination'
                ]);

        $professionals = $response->json('professionals');
        $professional = collect($professionals)->first();
        $services = $professional['services'];
        
        // Vérifier que les services avec images ont des URLs correctes
        $serviceWithImageData = collect($services)->firstWhere('title', 'Service avec image');
        $this->assertNotNull($serviceWithImageData);
        $this->assertStringContains('storage/services/test-image.jpg', $serviceWithImageData['image']);
    }
}
