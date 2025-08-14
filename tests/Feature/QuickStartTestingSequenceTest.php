<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\ProfessionalProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

/**
 * Test de la séquence de test automatisée du guide quick-start-testing.md
 * 
 * Ce test reproduit exactement la séquence documentée :
 * 1. Login Client → Token sauvegardé automatiquement
 * 2. Login Professional → Token sauvegardé automatiquement
 * 3. Create Open Offer → offer_id sauvegardé automatiquement
 * 4. Apply to Offer → application_id sauvegardé automatiquement
 * 5. Accept Application → Candidature acceptée
 * 6. View Accepted Applications → Vérifier la liste
 * 7. Assign Offer to Professional → Offre attribuée
 */
class QuickStartTestingSequenceTest extends TestCase
{
    use RefreshDatabase;

    private $clientToken;
    private $professionalToken;
    private $offerId;
    private $applicationId;

    /** @test */
    public function automated_testing_sequence_from_quick_start_guide()
    {
        // Préparer les utilisateurs de test
        $this->setupTestUsers();

        // 1. AUTHENTIFICATION
        $this->step1_authentication();

        // 2. CRÉATION D'OFFRE
        $this->step2_create_offer();

        // 3. CANDIDATURE
        $this->step3_apply_to_offer();

        // 4. GESTION CANDIDATURE
        $this->step4_manage_application();

        // 5. ATTRIBUTION FINALE
        $this->step5_assign_offer();

        // Vérifications finales
        $this->final_verifications();
    }

    /**
     * Configuration des utilisateurs de test
     */
    private function setupTestUsers(): void
    {
        // Créer un client
        $this->clientUser = User::factory()->create([
            'first_name' => 'Test',
            'last_name' => 'Client',
            'email' => 'client.test@example.com',
            'password' => Hash::make('password123'),
            'is_professional' => false,
            'email_verified_at' => now(),
        ]);

        // Créer un professionnel
        $this->professionalUser = User::factory()->create([
            'first_name' => 'Test',
            'last_name' => 'Professional',
            'email' => 'professional.test@example.com',
            'password' => Hash::make('password123'),
            'is_professional' => true,
            'email_verified_at' => now(),
        ]);

        // Créer le profil professionnel
        ProfessionalProfile::factory()->create([
            'user_id' => $this->professionalUser->id,
            'first_name' => 'Test',
            'last_name' => 'Professional',
            'email' => 'professional.test@example.com',
            'availability_status' => 'available',
        ]);
    }

    /**
     * Étape 1 : Authentification
     * - Exécuter Login Client → Token sauvegardé automatiquement
     * - Exécuter Login Professional → Token sauvegardé automatiquement
     */
    private function step1_authentication(): void
    {
        // Login Client
        $clientLoginResponse = $this->postJson('/api/login', [
            'email' => 'client.test@example.com',
            'password' => 'password123',
        ]);

        $clientLoginResponse->assertStatus(200);
        $clientLoginResponse->assertJsonStructure(['token', 'user']);
        $this->clientToken = $clientLoginResponse->json('token');

        // Login Professional
        $professionalLoginResponse = $this->postJson('/api/login', [
            'email' => 'professional.test@example.com',
            'password' => 'password123',
        ]);

        $professionalLoginResponse->assertStatus(200);
        $professionalLoginResponse->assertJsonStructure(['token', 'user']);
        $this->professionalToken = $professionalLoginResponse->json('token');

        // Vérifications
        $this->assertNotEmpty($this->clientToken, 'Client token should be saved automatically');
        $this->assertNotEmpty($this->professionalToken, 'Professional token should be saved automatically');
    }

    /**
     * Étape 2 : Création d'offre
     * - Exécuter Create Open Offer → offer_id sauvegardé automatiquement
     */
    private function step2_create_offer(): void
    {
        $offerData = [
            'title' => 'Test Offer - Automated Sequence',
            'description' => 'This is a test offer created during automated testing sequence.',
            'categories' => ['Test', 'Automation'],
            'budget' => '1000-2000',
            'deadline' => now()->addDays(30)->format('Y-m-d'),
            'company' => 'Test Company',
            'website' => 'https://test-company.com',
            'recruitment_type' => 'company',
            'open_to_applications' => true,
            'auto_invite' => false,
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->clientToken,
        ])->postJson('/api/open-offers', $offerData);

        $response->assertStatus(201);
        $response->assertJsonStructure(['open_offer', 'message']);
        
        $this->offerId = $response->json('open_offer.id');
        $this->assertNotEmpty($this->offerId, 'offer_id should be saved automatically');
    }

    /**
     * Étape 3 : Candidature
     * - Exécuter Apply to Offer → application_id sauvegardé automatiquement
     */
    private function step3_apply_to_offer(): void
    {
        $applicationData = [
            'proposal' => 'I am interested in this automated test offer.',
            'estimated_duration' => '1 month',
            'proposed_budget' => '1500',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->professionalToken,
        ])->postJson("/api/open-offers/{$this->offerId}/apply", $applicationData);

        $response->assertStatus(201);
        $response->assertJsonStructure(['application', 'message']);
        
        $this->applicationId = $response->json('application.id');
        $this->assertNotEmpty($this->applicationId, 'application_id should be saved automatically');
    }

    /**
     * Étape 4 : Gestion candidature
     * - Exécuter Accept Application → Candidature acceptée
     * - Exécuter View Accepted Applications → Vérifier la liste
     */
    private function step4_manage_application(): void
    {
        // Accept Application
        $acceptResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->clientToken,
        ])->patchJson("/api/offer-applications/{$this->applicationId}/status", [
            'status' => 'accepted'
        ]);

        $acceptResponse->assertStatus(200);
        $acceptResponse->assertJson([
            'message' => 'Statut de la candidature mis à jour avec succès.'
        ]);

        // Vérifier que la candidature est acceptée
        $this->assertDatabaseHas('offer_applications', [
            'id' => $this->applicationId,
            'status' => 'accepted',
        ]);

        // View Accepted Applications
        $viewResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->clientToken,
        ])->getJson("/api/open-offers/{$this->offerId}/accepted-applications");

        $viewResponse->assertStatus(200);
        $viewResponse->assertJsonCount(1, 'accepted_applications');
        $viewResponse->assertJsonPath('accepted_applications.0.status', 'accepted');
        $viewResponse->assertJsonPath('accepted_applications.0.id', $this->applicationId);
    }

    /**
     * Étape 5 : Attribution finale
     * - Exécuter Assign Offer to Professional → Offre attribuée
     */
    private function step5_assign_offer(): void
    {
        $assignResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->clientToken,
        ])->postJson("/api/open-offers/{$this->offerId}/assign", [
            'application_id' => $this->applicationId
        ]);

        $assignResponse->assertStatus(200);
        $assignResponse->assertJson([
            'message' => 'Offre attribuée avec succès au professionnel choisi.'
        ]);

        // Vérifier que l'offre passe en 'in_progress'
        $this->assertDatabaseHas('open_offers', [
            'id' => $this->offerId,
            'status' => 'in_progress',
        ]);
    }

    /**
     * Vérifications finales de la séquence
     */
    private function final_verifications(): void
    {
        // Vérifier l'état final de l'offre
        $this->assertDatabaseHas('open_offers', [
            'id' => $this->offerId,
            'status' => 'in_progress',
        ]);

        // Vérifier l'état final de la candidature
        $this->assertDatabaseHas('offer_applications', [
            'id' => $this->applicationId,
            'status' => 'accepted',
        ]);

        // Vérifier que nous avons exactement une offre et une candidature
        $this->assertDatabaseCount('open_offers', 1);
        $this->assertDatabaseCount('offer_applications', 1);

        // Test de l'API finale pour s'assurer que tout fonctionne
        $finalOfferCheck = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->clientToken,
        ])->getJson("/api/open-offers/{$this->offerId}");

        $finalOfferCheck->assertStatus(200);
        $finalOfferCheck->assertJsonPath('status', 'in_progress');

        echo "\n✅ Séquence de test automatisée terminée avec succès !\n";
        echo "📊 Résultats :\n";
        echo "   - Client Token: " . substr($this->clientToken, 0, 20) . "...\n";
        echo "   - Professional Token: " . substr($this->professionalToken, 0, 20) . "...\n";
        echo "   - Offer ID: {$this->offerId}\n";
        echo "   - Application ID: {$this->applicationId}\n";
        echo "   - Statut final de l'offre: in_progress\n";
        echo "   - Statut final de la candidature: accepted\n";
    }

    /** @test */
    public function sequence_can_be_run_multiple_times()
    {
        // Exécuter la séquence une première fois
        $this->automated_testing_sequence_from_quick_start_guide();

        // Nettoyer et recommencer
        $this->refreshDatabase();

        // Exécuter la séquence une seconde fois
        $this->automated_testing_sequence_from_quick_start_guide();

        // Vérifier que tout fonctionne toujours
        $this->assertDatabaseCount('open_offers', 1);
        $this->assertDatabaseCount('offer_applications', 1);
    }
}
