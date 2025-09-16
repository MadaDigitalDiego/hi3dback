<?php

namespace Tests\Feature;

use App\Models\ServiceOffer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ServiceOfferAdditionalFieldsTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Test creating a service offer with additional fields.
     */
    public function test_can_create_service_offer_with_additional_fields(): void
    {
        $user = User::factory()->create(['is_professional' => true]);
        $this->actingAs($user);

        $serviceOfferData = [
            'title' => 'Test Service Offer',
            'description' => 'Test description',
            'price' => 1000,
            'price_unit' => 'par projet',
            'execution_time' => '1 semaine',
            'concepts' => '3',
            'revisions' => '2',
            'categories' => ['Architecture', 'Modélisation 3D'],
            'status' => 'published',
            'what_you_get' => 'Vous obtiendrez des modèles 3D de haute qualité',
            'who_is_this_for' => 'Ce service est destiné aux architectes et designers',
            'delivery_method' => 'Livraison numérique via la plateforme',
            'why_choose_me' => 'Choisissez-moi pour mon expertise et ma rapidité'
        ];

        $response = $this->postJson('/api/service-offers', $serviceOfferData);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'title',
                'description',
                'what_you_get',
                'who_is_this_for',
                'delivery_method',
                'why_choose_me'
            ]
        ]);

        $this->assertDatabaseHas('service_offers', [
            'title' => 'Test Service Offer',
            'what_you_get' => 'Vous obtiendrez des modèles 3D de haute qualité',
            'who_is_this_for' => 'Ce service est destiné aux architectes et designers',
            'delivery_method' => 'Livraison numérique via la plateforme',
            'why_choose_me' => 'Choisissez-moi pour mon expertise et ma rapidité'
        ]);
    }

    /**
     * Test updating a service offer with additional fields.
     */
    public function test_can_update_service_offer_with_additional_fields(): void
    {
        $user = User::factory()->create(['is_professional' => true]);
        $serviceOffer = ServiceOffer::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user);

        $updateData = [
            'what_you_get' => 'Nouveau contenu pour ce que vous obtenez',
            'who_is_this_for' => 'Nouveau public cible',
            'delivery_method' => 'Nouvelle méthode de livraison',
            'why_choose_me' => 'Nouvelles raisons de me choisir'
        ];

        $response = $this->putJson("/api/service-offers/{$serviceOffer->id}", $updateData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('service_offers', [
            'id' => $serviceOffer->id,
            'what_you_get' => 'Nouveau contenu pour ce que vous obtenez',
            'who_is_this_for' => 'Nouveau public cible',
            'delivery_method' => 'Nouvelle méthode de livraison',
            'why_choose_me' => 'Nouvelles raisons de me choisir'
        ]);
    }

    /**
     * Test that additional fields are returned in API response.
     */
    public function test_additional_fields_are_returned_in_api_response(): void
    {
        $user = User::factory()->create(['is_professional' => true]);
        $serviceOffer = ServiceOffer::factory()->create([
            'user_id' => $user->id,
            'what_you_get' => 'Test what you get',
            'who_is_this_for' => 'Test who is this for',
            'delivery_method' => 'Test delivery method',
            'why_choose_me' => 'Test why choose me'
        ]);

        $response = $this->getJson("/api/service-offers/{$serviceOffer->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'what_you_get' => 'Test what you get',
                'who_is_this_for' => 'Test who is this for',
                'delivery_method' => 'Test delivery method',
                'why_choose_me' => 'Test why choose me'
            ]
        ]);
    }
}
