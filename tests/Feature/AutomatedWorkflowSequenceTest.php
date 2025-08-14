<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\OpenOffer;
use App\Models\OfferApplication;
use App\Models\ProfessionalProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

/**
 * Test de la séquence automatisée complète du workflow des offres
 * 
 * Cette classe teste la séquence suivante :
 * 1. Authentification (Login Client + Login Professional)
 * 2. Création d'offre (Create Open Offer)
 * 3. Candidature (Apply to Offer)
 * 4. Gestion candidature (Accept Application + View Accepted Applications)
 * 5. Attribution finale (Assign Offer to Professional)
 * 6. Gestion services professionnels (Create Service + View Services)
 * 7. Gestion réalisations/projets (Create Achievement + View Achievements)
 */
class AutomatedWorkflowSequenceTest extends TestCase
{
    use RefreshDatabase;

    protected $clientUser;
    protected $professionalUser;
    protected $clientToken;
    protected $professionalToken;
    protected $offerId;
    protected $applicationId;
    protected $serviceOfferId;
    protected $achievementId;

    protected function setUp(): void
    {
        parent::setUp();

        // Créer un utilisateur client
        $this->clientUser = User::factory()->create([
            'first_name' => 'Jean',
            'last_name' => 'Dupont',
            'email' => 'client@test.com',
            'is_professional' => false,
            'email_verified_at' => now(),
            'password' => Hash::make('password123'),
        ]);

        // Créer un utilisateur professionnel
        $this->professionalUser = User::factory()->create([
            'first_name' => 'Marie',
            'last_name' => 'Martin',
            'email' => 'professional@test.com',
            'is_professional' => true,
            'email_verified_at' => now(),
            'password' => Hash::make('password123'),
        ]);

        // Créer le profil professionnel
        ProfessionalProfile::factory()->create([
            'user_id' => $this->professionalUser->id,
            'first_name' => 'Marie',
            'last_name' => 'Martin',
            'email' => 'professional@test.com',
            'profession' => 'Développeur Full Stack',
            'skills' => ['PHP', 'Laravel', 'React', 'Mobile'],
            'years_of_experience' => 5,
            'hourly_rate' => 50,
            'availability_status' => 'available',
        ]);
    }

    /** @test */
    public function complete_automated_workflow_sequence_executes_successfully()
    {
        // ========================================
        // 1. AUTHENTIFICATION
        // ========================================
        
        // 1.1 Login Client → Token sauvegardé automatiquement
        $this->authenticateClient();
        
        // 1.2 Login Professional → Token sauvegardé automatiquement
        $this->authenticateProfessional();
        
        // ========================================
        // 2. CRÉATION D'OFFRE
        // ========================================
        
        // 2.1 Create Open Offer → offer_id sauvegardé automatiquement
        $this->createOpenOffer();
        
        // ========================================
        // 3. CANDIDATURE
        // ========================================
        
        // 3.1 Apply to Offer → application_id sauvegardé automatiquement
        $this->applyToOffer();
        
        // ========================================
        // 4. GESTION CANDIDATURE
        // ========================================
        
        // 4.1 Accept Application → Candidature acceptée
        $this->acceptApplication();
        
        // 4.2 View Accepted Applications → Vérifier la liste
        $this->viewAcceptedApplications();
        
        // ========================================
        // 5. ATTRIBUTION FINALE
        // ========================================
        
        // 5.1 Assign Offer to Professional → Offre attribuée
        $this->assignOfferToProfessional();
        
        // ========================================
        // 6. GESTION SERVICES PROFESSIONNELS
        // ========================================

        // 6.1 Create Service Offer → Service créé par le professionnel
        $this->createServiceOffer();

        // 6.2 View Professional Services → Consultation des services
        $this->viewProfessionalServices();

        // ========================================
        // 7. GESTION RÉALISATIONS/PROJETS
        // ========================================

        // 7.1 Create Achievement → Réalisation ajoutée par le professionnel
        $this->createAchievement();

        // 7.2 View Professional Achievements → Consultation des réalisations
        $this->viewProfessionalAchievements();

        // ========================================
        // VÉRIFICATIONS FINALES
        // ========================================

        $this->performFinalVerifications();
    }

    /**
     * Étape 1.1 : Authentification du client
     */
    private function authenticateClient(): void
    {
        // Nettoyer tous les tokens existants
        // $this->clientUser->tokens()->delete();

        $response = $this->postJson('/api/login', [
            'email' => $this->clientUser->email,
            'password' => 'password123',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['token', 'user']);
        $response->assertJsonPath('user.is_professional', false);

        $this->clientToken = $response->json('token');

        $this->assertNotEmpty($this->clientToken, 'Le token client doit être généré');
    }

    /**
     * Étape 1.2 : Authentification du professionnel
     */
    private function authenticateProfessional(): void
    {
        // Nettoyer tous les tokens existants
        // $this->professionalUser->tokens()->delete();

        $response = $this->postJson('/api/login', [
            'email' => $this->professionalUser->email,
            'password' => 'password123',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['token', 'user']);
        $response->assertJsonPath('user.is_professional', true);

        $this->professionalToken = $response->json('token');

        $this->assertNotEmpty($this->professionalToken, 'Le token professionnel doit être généré');
    }

    /**
     * Étape 2.1 : Création d'une offre ouverte
     */
    private function createOpenOffer(): void
    {
        $offerData = [
            'title' => 'Développement d\'une application mobile e-commerce',
            'description' => 'Nous recherchons un développeur expérimenté pour créer une application mobile e-commerce avec React Native et Laravel API.',
            'categories' => ['Développement', 'Mobile', 'E-commerce'],
            'budget' => '10000',
            'deadline' => now()->addDays(45)->format('Y-m-d'),
            'company' => 'TechStart Solutions',
            'website' => 'https://techstart-solutions.com',
            'recruitment_type' => 'company',
            'open_to_applications' => true,
            'auto_invite' => false,
        ];
        
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->clientToken,
        ])->postJson('/api/open-offers', $offerData);

        if ($response->status() !== 201) {
            echo "Response status: " . $response->status() . "\n";
            echo "Response content: " . $response->getContent() . "\n";
        }

        $response->assertStatus(201);
        $response->assertJsonStructure(['open_offer', 'message']);
        $response->assertJsonPath('open_offer.status', 'open');
        $response->assertJsonPath('open_offer.title', $offerData['title']);
        
        $this->offerId = $response->json('open_offer.id');
        $this->assertNotEmpty($this->offerId, 'L\'ID de l\'offre doit être généré');
    }

    /**
     * Étape 3.1 : Candidature à l'offre
     */
    private function applyToOffer(): void
    {
        $applicationData = [
            'proposal' => 'Bonjour, je suis très intéressé par votre projet d\'application mobile e-commerce. Avec mes 5 ans d\'expérience en développement full-stack et ma spécialisation en React Native et Laravel, je peux vous livrer une solution robuste et performante.',
            'estimated_duration' => '2-3 mois',
            'proposed_budget' => '10000',
        ];
        


        $response = $this->actingAs($this->professionalUser, 'sanctum')
            ->postJson("/api/open-offers/{$this->offerId}/apply", $applicationData);



        $response->assertStatus(201);
        $response->assertJsonStructure(['application', 'message']);
        $response->assertJsonPath('application.status', 'pending');
        
        $this->applicationId = $response->json('application.id');
        $this->assertNotEmpty($this->applicationId, 'L\'ID de la candidature doit être généré');
        
        // Vérifier en base de données
        $this->assertDatabaseHas('offer_applications', [
            'id' => $this->applicationId,
            'open_offer_id' => $this->offerId,
            'status' => 'pending',
        ]);
    }

    /**
     * Étape 4.1 : Acceptation de la candidature
     */
    private function acceptApplication(): void
    {
        $response = $this->actingAs($this->clientUser, 'sanctum')
            ->patchJson("/api/offer-applications/{$this->applicationId}/status", [
                'status' => 'accepted'
            ]);
        
        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Statut de la candidature mis à jour et statut de l\'offre mis à jour.'
        ]);
        
        // Vérifier que la candidature est acceptée
        $this->assertDatabaseHas('offer_applications', [
            'id' => $this->applicationId,
            'status' => 'accepted',
        ]);
        
        // Vérifier que l'offre reste ouverte (pas encore attribuée)
        $this->assertDatabaseHas('open_offers', [
            'id' => $this->offerId,
            'status' => 'open',
        ]);
    }

    /**
     * Étape 4.2 : Consultation des candidatures acceptées
     */
    private function viewAcceptedApplications(): void
    {
        $response = $this->actingAs($this->clientUser, 'sanctum')
            ->getJson("/api/open-offers/{$this->offerId}/accepted-applications");
        
        $response->assertStatus(200);
        $response->assertJsonCount(1, 'accepted_applications');
        $response->assertJsonPath('accepted_applications.0.status', 'accepted');
        $response->assertJsonPath('accepted_applications.0.id', $this->applicationId);
        
        // Vérifier les détails de la candidature acceptée
        $acceptedApplication = $response->json('accepted_applications.0');
        $this->assertEquals($this->applicationId, $acceptedApplication['id']);
        $this->assertEquals('accepted', $acceptedApplication['status']);
        $this->assertArrayHasKey('freelance_profile', $acceptedApplication);
    }

    /**
     * Étape 5.1 : Attribution de l'offre au professionnel
     */
    private function assignOfferToProfessional(): void
    {
        $response = $this->actingAs($this->clientUser, 'sanctum')
            ->postJson("/api/open-offers/{$this->offerId}/assign", [
                'application_id' => $this->applicationId
            ]);
        
        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Offre attribuée avec succès au professionnel choisi.'
        ]);
        
        // Vérifier que l'offre passe en 'in_progress'
        $this->assertDatabaseHas('open_offers', [
            'id' => $this->offerId,
            'status' => 'in_progress',
        ]);
        
        // Vérifier que la candidature choisie reste acceptée
        $this->assertDatabaseHas('offer_applications', [
            'id' => $this->applicationId,
            'status' => 'accepted',
        ]);
    }

    /**
     * Vérifications finales du workflow
     */
    private function performFinalVerifications(): void
    {
        // Vérifier l'état final de l'offre
        $offerResponse = $this->actingAs($this->clientUser, 'sanctum')
            ->getJson("/api/open-offers/{$this->offerId}");
        
        $offerResponse->assertStatus(200);
        $offerResponse->assertJsonPath('open_offer.status', 'in_progress');
        
        // Vérifier que le client peut voir ses offres en cours
        $clientOffersResponse = $this->actingAs($this->clientUser, 'sanctum')
            ->getJson('/api/client/open-offers/in-progress');
        
        $clientOffersResponse->assertStatus(200);
        
        // Vérifier que le professionnel peut voir l'offre attribuée
        $professionalOffersResponse = $this->actingAs($this->professionalUser, 'sanctum')
            ->getJson("/api/professionals/{$this->professionalUser->id}/offers");
        
        $professionalOffersResponse->assertStatus(200);
        
        // Vérifications finales en base de données
        $this->assertDatabaseCount('open_offers', 1);
        $this->assertDatabaseCount('offer_applications', 1);
        
        // Vérifier l'intégrité des données
        $this->assertDatabaseHas('open_offers', [
            'id' => $this->offerId,
            'user_id' => $this->clientUser->id,
            'status' => 'in_progress',
            'title' => 'Développement d\'une application mobile e-commerce',
        ]);
        
        $this->assertDatabaseHas('offer_applications', [
            'id' => $this->applicationId,
            'open_offer_id' => $this->offerId,
            'status' => 'accepted',
        ]);
    }

    /**
     * Étape 6.1 : Création d'un service par le professionnel
     */
    private function createServiceOffer(): void
    {
        $response = $this->actingAs($this->professionalUser, 'sanctum')
            ->postJson('/api/service-offers', [
                'title' => 'Modélisation 3D Architecture',
                'description' => 'Service de modélisation 3D pour projets architecturaux avec rendu photoréaliste.',
                'price' => 1500.00,
                'execution_time' => '5-7 jours',
                'concepts' => '3',
                'revisions' => '2',
                'categories' => ['Architecture', 'Modélisation 3D'],
                'is_private' => false,
                'status' => 'published'
            ]);

        if ($response->status() !== 201) {
            dump('Service creation failed:', $response->status(), $response->json());
        }
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'id',
            'title',
            'description',
            'price',
            'execution_time',
            'concepts',
            'revisions',
            'categories',
            'is_private',
            'status',
            'user_id'
        ]);

        $this->serviceOfferId = $response->json('id');

        // Vérifier en base de données
        $this->assertDatabaseHas('service_offers', [
            'id' => $this->serviceOfferId,
            'title' => 'Modélisation 3D Architecture',
            'user_id' => $this->professionalUser->id,
            'status' => 'published',
        ]);
    }

    /**
     * Étape 6.2 : Consultation des services du professionnel
     */
    private function viewProfessionalServices(): void
    {
        // Consulter les services du professionnel (endpoint public)
        $response = $this->getJson("/api/professionals/{$this->professionalUser->id}/service-offers");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => [
                'id',
                'title',
                'description',
                'price',
                'execution_time',
                'categories',
                'status'
            ]
        ]);

        // Vérifier que notre service créé est dans la liste
        $services = $response->json();
        $createdService = collect($services)->firstWhere('id', $this->serviceOfferId);
        $this->assertNotNull($createdService);
        $this->assertEquals('Modélisation 3D Architecture', $createdService['title']);
    }

    /**
     * Étape 7.1 : Création d'une réalisation par le professionnel
     */
    private function createAchievement(): void
    {
        $response = $this->actingAs($this->professionalUser, 'sanctum')
            ->postJson('/api/achievements', [
                'title' => 'Villa Moderne - Projet Résidentiel',
                'organization' => 'Cabinet Architecture Moderne',
                'date_obtained' => '2024-06-15',
                'description' => 'Conception et modélisation 3D complète d\'une villa moderne de 250m² avec jardin paysager. Projet incluant plans architecturaux, rendus photoréalistes et visite virtuelle.',
                'achievement_url' => 'https://portfolio.example.com/villa-moderne'
            ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'achievement' => [
                'id',
                'title',
                'organization',
                'date_obtained',
                'description',
                'achievement_url',
                'professional_profile_id'
            ],
            'message'
        ]);

        $this->achievementId = $response->json('achievement.id');

        // Vérifier en base de données
        $this->assertDatabaseHas('achievements', [
            'id' => $this->achievementId,
            'title' => 'Villa Moderne - Projet Résidentiel',
            'professional_profile_id' => $this->professionalUser->professionalProfile->id,
        ]);
    }

    /**
     * Étape 7.2 : Consultation des réalisations du professionnel
     */
    private function viewProfessionalAchievements(): void
    {
        // Récupérer l'ID du profil professionnel
        $professionalProfileId = $this->professionalUser->professionalProfile->id;

        // Consulter les réalisations du professionnel (endpoint public)
        $response = $this->getJson("/api/professionals/{$professionalProfileId}/achievements");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'achievements' => [
                '*' => [
                    'id',
                    'title',
                    'organization',
                    'date_obtained',
                    'description'
                ]
            ]
        ]);

        // Vérifier que notre réalisation créée est dans la liste
        $achievements = $response->json('achievements');
        $createdAchievement = collect($achievements)->firstWhere('id', $this->achievementId);
        $this->assertNotNull($createdAchievement);
        $this->assertEquals('Villa Moderne - Projet Résidentiel', $createdAchievement['title']);
    }
}
