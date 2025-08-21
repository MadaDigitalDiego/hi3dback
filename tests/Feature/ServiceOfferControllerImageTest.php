<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\ProfessionalProfile;
use App\Models\ServiceOffer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ServiceOfferControllerImageTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Créer un utilisateur professionnel
        $this->user = User::factory()->create([
            'is_professional' => true,
        ]);
        
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
    public function test_get_service_offers_by_professional_returns_image_field()
    {
        $response = $this->getJson("/api/professionals/{$this->user->id}/service-offers");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    '*' => [
                        'id',
                        'title',
                        'description',
                        'price',
                        'image', // ✅ Vérifier que le champ image est présent
                        'views',
                        'likes',
                        'status',
                        'user'
                    ]
                ]);

        $services = $response->json();
        
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
    public function test_service_offer_resource_includes_image_field()
    {
        $serviceOffer = ServiceOffer::factory()->create([
            'image' => 'services/resource-test.jpg'
        ]);

        $resource = new \App\Http\Resources\ServiceOfferResource($serviceOffer);
        $resourceArray = $resource->toArray(request());

        $this->assertArrayHasKey('image', $resourceArray);
        $this->assertStringContains('storage/services/resource-test.jpg', $resourceArray['image']);
    }

    /** @test */
    public function test_service_offer_resource_handles_null_image()
    {
        $serviceOffer = ServiceOffer::factory()->create([
            'image' => null
        ]);

        $resource = new \App\Http\Resources\ServiceOfferResource($serviceOffer);
        $resourceArray = $resource->toArray(request());

        $this->assertArrayHasKey('image', $resourceArray);
        $this->assertNull($resourceArray['image']);
    }

    /** @test */
    public function test_service_offer_resource_handles_url_image()
    {
        $serviceOffer = ServiceOffer::factory()->create([
            'image' => 'https://example.com/external-image.jpg'
        ]);

        $resource = new \App\Http\Resources\ServiceOfferResource($serviceOffer);
        $resourceArray = $resource->toArray(request());

        $this->assertArrayHasKey('image', $resourceArray);
        $this->assertEquals('https://example.com/external-image.jpg', $resourceArray['image']);
    }
}
