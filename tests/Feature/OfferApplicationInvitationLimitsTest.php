<?php

namespace Tests\Feature;

use App\Models\OpenOffer;
use App\Models\OfferApplication;
use App\Models\Plan;
use App\Models\ProfessionalProfile;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfferApplicationInvitationLimitsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Create a professional user with an active subscription
     * and a given max_applications plan limit.
     */
    protected function createProfessionalWithPlan(int $maxApplications): User
    {
        $plan = Plan::factory()->professional()->create([
            'max_applications' => $maxApplications,
        ]);

        $professional = User::factory()->create([
            'is_professional' => true,
            'email_verified_at' => now(),
        ]);

        ProfessionalProfile::factory()->available()->create([
            'user_id' => $professional->id,
            'email' => $professional->email,
        ]);

        Subscription::factory()->create([
            'user_id' => $professional->id,
            'plan_id' => $plan->id,
            'stripe_status' => 'active',
        ]);

        return $professional->fresh();
    }

    /**
     * Create an invitation (OfferApplication with status `invited`)
     * for the given professional.
     */
    protected function createInvitationFor(User $professional): OfferApplication
    {
        // Create a simple client who owns the open offer
        $client = User::factory()->create([
            'is_professional' => false,
            'email_verified_at' => now(),
        ]);

        // Create an open offer using the real schema (no extra columns)
        $openOffer = OpenOffer::create([
            'user_id' => $client->id,
            'title' => 'Test Open Offer',
            'description' => 'Description pour test d\'invitation.',
            'categories' => ['Test'],
            'filters' => null,
            'budget' => null,
            'deadline' => now()->addDays(7),
            'company' => null,
            'website' => null,
            'files' => null,
            'attachment_links' => null,
            'recruitment_type' => 'freelance',
            'open_to_applications' => true,
            'auto_invite' => false,
            'status' => 'open',
            'views_count' => 0,
        ]);

        $profile = $professional->professionalProfile;

        return OfferApplication::create([
            'open_offer_id' => $openOffer->id,
            'professional_profile_id' => $profile->id,
            'proposal' => 'Invitation envoyee par le client',
            'status' => 'invited',
        ]);
    }

    /** @test */
    public function professional_cannot_accept_invitation_when_plan_applications_limit_is_zero(): void
    {
        $professional = $this->createProfessionalWithPlan(0);
        $invitation = $this->createInvitationFor($professional);

        $response = $this->actingAs($professional, 'sanctum')
            ->putJson("/api/offer-applications/{$invitation->id}/accept");

        $response->assertStatus(403);
        $response->assertJsonStructure(['message']);

        $this->assertDatabaseHas('offer_applications', [
            'id' => $invitation->id,
            'status' => 'invited',
        ]);
    }

    /** @test */
    public function professional_can_accept_invitation_when_under_plan_applications_limit(): void
    {
        $professional = $this->createProfessionalWithPlan(5);
        $invitation = $this->createInvitationFor($professional);

        $response = $this->actingAs($professional, 'sanctum')
            ->putJson("/api/offer-applications/{$invitation->id}/accept");

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Offre acceptée avec succès.']);

        $this->assertDatabaseHas('offer_applications', [
            'id' => $invitation->id,
            'status' => 'accepted',
        ]);
    }

    /** @test */
    public function professional_can_accept_invitation_even_when_applications_limit_is_reached(): void
    {
        $professional = $this->createProfessionalWithPlan(1);
        $profile = $professional->professionalProfile;

        // Une candidature deja creee (utilisee) compte dans le quota
        $client = User::factory()->create([
            'is_professional' => false,
            'email_verified_at' => now(),
        ]);

        $existingOffer = OpenOffer::create([
            'user_id' => $client->id,
            'title' => 'Offre existante',
            'description' => 'Offre existante pour test de quota.',
            'categories' => ['Test'],
            'filters' => null,
            'budget' => null,
            'deadline' => now()->addDays(7),
            'company' => null,
            'website' => null,
            'files' => null,
            'attachment_links' => null,
            'recruitment_type' => 'freelance',
            'open_to_applications' => true,
            'auto_invite' => false,
            'status' => 'open',
            'views_count' => 0,
        ]);

        OfferApplication::create([
            'open_offer_id' => $existingOffer->id,
            'professional_profile_id' => $profile->id,
            'proposal' => 'Premiere candidature',
            'status' => 'pending',
        ]);

        // Invitation à accepter (status = invited)
        $invitation = $this->createInvitationFor($professional);

        $response = $this->actingAs($professional, 'sanctum')
            ->putJson("/api/offer-applications/{$invitation->id}/accept");

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Offre acceptée avec succès.']);

        $this->assertDatabaseHas('offer_applications', [
            'id' => $invitation->id,
            'status' => 'accepted',
        ]);
    }

    /** @test */
    public function professional_cannot_accept_non_invited_application_when_applications_limit_is_reached(): void
    {
        $professional = $this->createProfessionalWithPlan(1);
        $profile = $professional->professionalProfile;

        $client = User::factory()->create([
            'is_professional' => false,
            'email_verified_at' => now(),
        ]);

        $openOffer = OpenOffer::create([
            'user_id' => $client->id,
            'title' => 'Offre client',
            'description' => 'Offre client pour test de depassement de quota.',
            'categories' => ['Test'],
            'filters' => null,
            'budget' => null,
            'deadline' => now()->addDays(7),
            'company' => null,
            'website' => null,
            'files' => null,
            'attachment_links' => null,
            'recruitment_type' => 'freelance',
            'open_to_applications' => true,
            'auto_invite' => false,
            'status' => 'open',
            'views_count' => 0,
        ]);

        // Une candidature "pending" existe deja et consomme le quota
        $application = OfferApplication::create([
            'open_offer_id' => $openOffer->id,
            'professional_profile_id' => $profile->id,
            'proposal' => 'Candidature directe',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($professional, 'sanctum')
            ->putJson("/api/offer-applications/{$application->id}/accept");

        $response->assertStatus(403);
        $response->assertJsonStructure(['message']);

        $this->assertDatabaseHas('offer_applications', [
            'id' => $application->id,
            'status' => 'pending',
        ]);
    }
}

