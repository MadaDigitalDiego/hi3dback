<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\OpenOffer;
use App\Models\OfferApplication;
use App\Models\ProfessionalProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class OpenOfferWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected $client;
    protected $professional1;
    protected $professional2;
    protected $openOffer;
    protected $clientToken;
    protected $professionalToken;

    protected function setUp(): void
    {
        parent::setUp();

        // Créer un client
        $this->client = User::factory()->create([
            'first_name' => 'Client',
            'last_name' => 'Test',
            'is_professional' => false,
            'email_verified_at' => now(),
            'password' => Hash::make('password123'),
        ]);

        // Créer des professionnels
        $this->professional1 = User::factory()->create([
            'first_name' => 'Professional',
            'last_name' => 'One',
            'is_professional' => true,
            'email_verified_at' => now(),
            'password' => Hash::make('password123'),
        ]);

        $this->professional2 = User::factory()->create([
            'first_name' => 'Professional',
            'last_name' => 'Two',
            'is_professional' => true,
            'email_verified_at' => now(),
            'password' => Hash::make('password123'),
        ]);

        // Créer des profils professionnels
        ProfessionalProfile::factory()->create([
            'user_id' => $this->professional1->id,
            'first_name' => 'Professional',
            'last_name' => 'One',
            'email' => $this->professional1->email,
            'availability_status' => 'available',
        ]);
        ProfessionalProfile::factory()->create([
            'user_id' => $this->professional2->id,
            'first_name' => 'Professional',
            'last_name' => 'Two',
            'email' => $this->professional2->email,
            'availability_status' => 'available',
        ]);

        // Créer une offre ouverte
        $this->openOffer = OpenOffer::factory()->create([
            'user_id' => $this->client->id,
            'status' => 'open',
        ]);
    }

    /** @test */
    public function complete_automated_workflow_sequence()
    {
        // 1. AUTHENTIFICATION

        // 1.1 Login Client → Token sauvegardé automatiquement
        $clientLoginResponse = $this->postJson('/api/login', [
            'email' => $this->client->email,
            'password' => 'password123',
        ]);

        $clientLoginResponse->assertStatus(200);
        $clientLoginResponse->assertJsonStructure(['token', 'user']);
        $this->clientToken = $clientLoginResponse->json('token');

        // 1.2 Login Professional → Token sauvegardé automatiquement
        $professionalLoginResponse = $this->postJson('/api/login', [
            'email' => $this->professional1->email,
            'password' => 'password123',
        ]);

        $professionalLoginResponse->assertStatus(200);
        $professionalLoginResponse->assertJsonStructure(['token', 'user']);
        $this->professionalToken = $professionalLoginResponse->json('token');

        // 2. CRÉATION D'OFFRE

        // 2.1 Create Open Offer → offer_id sauvegardé automatiquement
        $offerData = [
            'title' => 'Développement d\'une application mobile',
            'description' => 'Nous recherchons un développeur pour créer une application mobile innovante.',
            'categories' => ['Développement', 'Mobile'],
            'budget' => '5000-10000',
            'deadline' => now()->addDays(30)->format('Y-m-d'),
            'company' => 'TechCorp',
            'website' => 'https://techcorp.com',
            'recruitment_type' => 'company',
            'open_to_applications' => true,
            'auto_invite' => false,
        ];

        $createOfferResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->clientToken,
        ])->postJson('/api/open-offers', $offerData);

        $createOfferResponse->assertStatus(201);
        $createOfferResponse->assertJsonStructure(['open_offer', 'message']);
        $offerId = $createOfferResponse->json('open_offer.id');

        // 3. CANDIDATURE

        // 3.1 Apply to Offer → application_id sauvegardé automatiquement
        $applicationData = [
            'proposal' => 'Je suis très intéressé par ce projet. J\'ai 5 ans d\'expérience en développement mobile.',
            'estimated_duration' => '3 mois',
            'proposed_budget' => '7500',
        ];

        $applyResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->professionalToken,
        ])->postJson("/api/open-offers/{$offerId}/apply", $applicationData);

        $applyResponse->assertStatus(201);
        $applyResponse->assertJsonStructure(['application', 'message']);
        $applicationId = $applyResponse->json('application.id');

        // 4. GESTION CANDIDATURE

        // 4.1 Accept Application → Candidature acceptée
        $acceptResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->clientToken,
        ])->patchJson("/api/offer-applications/{$applicationId}/status", [
            'status' => 'accepted'
        ]);

        $acceptResponse->assertStatus(200);
        $acceptResponse->assertJson([
            'message' => 'Statut de la candidature mis à jour avec succès.'
        ]);

        // Vérifier que la candidature est acceptée
        $this->assertDatabaseHas('offer_applications', [
            'id' => $applicationId,
            'status' => 'accepted',
        ]);

        // 4.2 View Accepted Applications → Vérifier la liste
        $acceptedApplicationsResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->clientToken,
        ])->getJson("/api/open-offers/{$offerId}/accepted-applications");

        $acceptedApplicationsResponse->assertStatus(200);
        $acceptedApplicationsResponse->assertJsonCount(1, 'accepted_applications');
        $acceptedApplicationsResponse->assertJsonPath('accepted_applications.0.status', 'accepted');
        $acceptedApplicationsResponse->assertJsonPath('accepted_applications.0.id', $applicationId);

        // 5. ATTRIBUTION FINALE

        // 5.1 Assign Offer to Professional → Offre attribuée
        $assignResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->clientToken,
        ])->postJson("/api/open-offers/{$offerId}/assign", [
            'application_id' => $applicationId
        ]);

        $assignResponse->assertStatus(200);
        $assignResponse->assertJson([
            'message' => 'Offre attribuée avec succès au professionnel choisi.'
        ]);

        // Vérifier que l'offre passe en 'in_progress'
        $this->assertDatabaseHas('open_offers', [
            'id' => $offerId,
            'status' => 'in_progress',
        ]);

        // Vérifier que la candidature choisie reste acceptée
        $this->assertDatabaseHas('offer_applications', [
            'id' => $applicationId,
            'status' => 'accepted',
        ]);

        // VÉRIFICATIONS FINALES

        // Vérifier l'état final de l'offre
        $finalOfferCheck = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->clientToken,
        ])->getJson("/api/open-offers/{$offerId}");

        $finalOfferCheck->assertStatus(200);
        $finalOfferCheck->assertJsonPath('status', 'in_progress');

        // Vérifier que le professionnel peut voir l'offre attribuée
        $professionalOffersResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->professionalToken,
        ])->getJson("/api/professionals/{$this->professional1->id}/offers");

        $professionalOffersResponse->assertStatus(200);
        // L'offre devrait apparaître dans les offres attribuées au professionnel
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
