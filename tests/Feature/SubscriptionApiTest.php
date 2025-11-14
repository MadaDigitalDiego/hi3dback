<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Plan $plan;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->plan = Plan::factory()->create([
            'name' => 'Pro',
            'price' => 49.99,
            'is_active' => true,
        ]);
    }

    public function test_get_plans(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/subscriptions/plans');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => ['id', 'name', 'price', 'is_active', 'user_type']
                ]
            ]);
    }

    public function test_plans_filtered_by_professional_user_type(): void
    {
        // Create plans for both types
        $professionalPlan = Plan::factory()->create([
            'name' => 'Pro Plan',
            'user_type' => 'professional',
            'is_active' => true,
        ]);

        $clientPlan = Plan::factory()->create([
            'name' => 'Client Plan',
            'user_type' => 'client',
            'is_active' => true,
        ]);

        // Create a professional user
        $professionalUser = User::factory()->create(['is_professional' => true]);

        $response = $this->actingAs($professionalUser, 'sanctum')
            ->getJson('/api/subscriptions/plans');

        $response->assertStatus(200)
            ->assertJson(['user_type' => 'professional'])
            ->assertJsonCount(1, 'data');

        // Verify only professional plan is returned
        $this->assertEquals($professionalPlan->id, $response->json('data.0.id'));
    }

    public function test_plans_filtered_by_client_user_type(): void
    {
        // Create plans for both types
        $professionalPlan = Plan::factory()->create([
            'name' => 'Pro Plan',
            'user_type' => 'professional',
            'is_active' => true,
        ]);

        $clientPlan = Plan::factory()->create([
            'name' => 'Client Plan',
            'user_type' => 'client',
            'is_active' => true,
        ]);

        // Create a client user
        $clientUser = User::factory()->create(['is_professional' => false]);

        $response = $this->actingAs($clientUser, 'sanctum')
            ->getJson('/api/subscriptions/plans');

        $response->assertStatus(200)
            ->assertJson(['user_type' => 'client'])
            ->assertJsonCount(1, 'data');

        // Verify only client plan is returned
        $this->assertEquals($clientPlan->id, $response->json('data.0.id'));
    }

    public function test_plan_limits_are_returned(): void
    {
        $plan = Plan::factory()->create([
            'name' => 'Premium Plan',
            'user_type' => 'professional',
            'is_active' => true,
            'max_services' => 50,
            'max_open_offers' => 100,
            'max_applications' => 500,
            'max_messages' => 1000,
        ]);

        $user = User::factory()->create(['is_professional' => true]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/subscriptions/plans');

        $response->assertStatus(200)
            ->assertJsonPath('data.0.max_services', 50)
            ->assertJsonPath('data.0.max_open_offers', 100)
            ->assertJsonPath('data.0.max_applications', 500)
            ->assertJsonPath('data.0.max_messages', 1000);
    }

    public function test_get_current_subscription_when_none_exists(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/subscriptions/current');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'No active subscription'
            ]);
    }

    public function test_get_subscription_history(): void
    {
        Subscription::factory()->create([
            'user_id' => $this->user->id,
            'plan_id' => $this->plan->id,
            'stripe_status' => 'active',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/subscriptions/history');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => ['id', 'user_id', 'plan_id', 'stripe_status']
                ]
            ]);
    }

    public function test_user_is_premium_with_active_subscription(): void
    {
        Subscription::factory()->create([
            'user_id' => $this->user->id,
            'plan_id' => $this->plan->id,
            'stripe_status' => 'active',
        ]);

        $this->assertTrue($this->user->refresh()->isPremium());
    }

    public function test_user_is_not_premium_without_subscription(): void
    {
        $this->assertFalse($this->user->isPremium());
    }

    public function test_get_plan_limits(): void
    {
        $subscription = Subscription::factory()->create([
            'user_id' => $this->user->id,
            'plan_id' => $this->plan->id,
            'stripe_status' => 'active',
        ]);

        $limits = $this->user->refresh()->getPlanLimits();
        $this->assertIsArray($limits);
    }

    public function test_unauthorized_access_to_subscriptions(): void
    {
        $response = $this->getJson('/api/subscriptions/plans');

        $response->assertStatus(401);
    }
}

