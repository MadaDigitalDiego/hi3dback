<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\OpenOffer;
use App\Models\OfferApplication;
use App\Models\ProfessionalProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OpenOfferWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected $client;
    protected $professional1;
    protected $professional2;
    protected $openOffer;

    protected function setUp(): void
    {
        parent::setUp();

        // Créer un client
        $this->client = User::factory()->create([
            'is_professional' => false,
            'email_verified_at' => now(),
        ]);

        // Créer des professionnels
        $this->professional1 = User::factory()->create([
            'is_professional' => true,
            'email_verified_at' => now(),
        ]);
        
        $this->professional2 = User::factory()->create([
            'is_professional' => true,
            'email_verified_at' => now(),
        ]);

        // Créer des profils professionnels
        ProfessionalProfile::factory()->create(['user_id' => $this->professional1->id]);
        ProfessionalProfile::factory()->create(['user_id' => $this->professional2->id]);

        // Créer une offre ouverte
        $this->openOffer = OpenOffer::factory()->create([
            'user_id' => $this->client->id,
            'status' => 'open',
        ]);
    }

    /** @test */
    public function client_can_accept_application_without_changing_offer_status()
    {
        // Créer une candidature
        $application = OfferApplication::create([
            'open_offer_id' => $this->openOffer->id,
            'professional_profile_id' => $this->professional1->professionalProfile->id,
            'proposal' => 'Ma proposition pour ce projet',
            'status' => 'pending',
        ]);

        // Le client accepte la candidature
        $response = $this->actingAs($this->client)
            ->patchJson("/api/offer-applications/{$application->id}/status", [
                'status' => 'accepted'
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Statut de la candidature mis à jour avec succès.'
        ]);

        // Vérifier que la candidature est acceptée
        $this->assertDatabaseHas('offer_applications', [
            'id' => $application->id,
            'status' => 'accepted',
        ]);

        // Vérifier que l'offre reste en statut 'open'
        $this->assertDatabaseHas('open_offers', [
            'id' => $this->openOffer->id,
            'status' => 'open',
        ]);
    }

    /** @test */
    public function client_can_assign_offer_to_accepted_professional()
    {
        // Créer deux candidatures
        $application1 = OfferApplication::create([
            'open_offer_id' => $this->openOffer->id,
            'professional_profile_id' => $this->professional1->professionalProfile->id,
            'proposal' => 'Proposition 1',
            'status' => 'accepted',
        ]);

        $application2 = OfferApplication::create([
            'open_offer_id' => $this->openOffer->id,
            'professional_profile_id' => $this->professional2->professionalProfile->id,
            'proposal' => 'Proposition 2',
            'status' => 'accepted',
        ]);

        // Le client attribue l'offre au premier professionnel
        $response = $this->actingAs($this->client)
            ->postJson("/api/open-offers/{$this->openOffer->id}/assign", [
                'application_id' => $application1->id
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Offre attribuée avec succès au professionnel choisi.'
        ]);

        // Vérifier que l'offre passe en 'in_progress'
        $this->assertDatabaseHas('open_offers', [
            'id' => $this->openOffer->id,
            'status' => 'in_progress',
        ]);

        // Vérifier que la candidature choisie reste acceptée
        $this->assertDatabaseHas('offer_applications', [
            'id' => $application1->id,
            'status' => 'accepted',
        ]);

        // Vérifier que l'autre candidature est rejetée automatiquement
        $this->assertDatabaseHas('offer_applications', [
            'id' => $application2->id,
            'status' => 'rejected',
        ]);
    }

    /** @test */
    public function client_can_view_accepted_applications()
    {
        // Créer des candidatures avec différents statuts
        OfferApplication::create([
            'open_offer_id' => $this->openOffer->id,
            'professional_profile_id' => $this->professional1->professionalProfile->id,
            'proposal' => 'Proposition acceptée',
            'status' => 'accepted',
        ]);

        OfferApplication::create([
            'open_offer_id' => $this->openOffer->id,
            'professional_profile_id' => $this->professional2->professionalProfile->id,
            'proposal' => 'Proposition rejetée',
            'status' => 'rejected',
        ]);

        // Le client consulte les candidatures acceptées
        $response = $this->actingAs($this->client)
            ->getJson("/api/open-offers/{$this->openOffer->id}/accepted-applications");

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'accepted_applications');
        $response->assertJsonPath('accepted_applications.0.status', 'accepted');
    }

    /** @test */
    public function cannot_assign_offer_to_non_accepted_application()
    {
        // Créer une candidature en attente
        $application = OfferApplication::create([
            'open_offer_id' => $this->openOffer->id,
            'professional_profile_id' => $this->professional1->professionalProfile->id,
            'proposal' => 'Proposition en attente',
            'status' => 'pending',
        ]);

        // Tenter d'attribuer l'offre à une candidature non acceptée
        $response = $this->actingAs($this->client)
            ->postJson("/api/open-offers/{$this->openOffer->id}/assign", [
                'application_id' => $application->id
            ]);

        $response->assertStatus(400);
        $response->assertJson([
            'message' => 'Seules les candidatures acceptées peuvent être attribuées.'
        ]);

        // Vérifier que l'offre reste en statut 'open'
        $this->assertDatabaseHas('open_offers', [
            'id' => $this->openOffer->id,
            'status' => 'open',
        ]);
    }
}
