<?php

namespace Tests\Feature;

use App\Models\StripeConfiguration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StripeConfigurationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Seed the database with initial Stripe configuration
        $this->seed(\Database\Seeders\StripeConfigurationSeeder::class);
    }

    /**
     * Test retrieving public Stripe key via API
     */
    public function test_get_stripe_public_key(): void
    {
        $response = $this->getJson('/api/stripe/public-key');

        $response->assertStatus(200);
        $response->assertJsonStructure(['public_key']);
        $this->assertNotEmpty($response->json('public_key'));
    }

    /**
     * Test retrieving Stripe configuration as admin
     */
    public function test_admin_can_get_stripe_configuration(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)
            ->getJson('/api/admin/stripe-config');

        $response->assertStatus(200);
        $response->assertJsonStructure(['id', 'mode', 'is_active']);
    }

    /**
     * Test non-admin cannot get Stripe configuration
     */
    public function test_non_admin_cannot_get_stripe_configuration(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $response = $this->actingAs($user)
            ->getJson('/api/admin/stripe-config');

        $response->assertStatus(403);
    }

    /**
     * Test admin can update Stripe configuration
     */
    public function test_admin_can_update_stripe_configuration(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)
            ->putJson('/api/admin/stripe-config', [
                'public_key' => 'pk_test_new_key',
                'secret_key' => 'sk_test_new_secret',
                'webhook_secret' => 'whsec_test_new_webhook',
                'mode' => 'test',
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('stripe_configurations', [
            'public_key' => 'pk_test_new_key',
        ]);
    }

    /**
     * Test StripeConfiguration model methods
     */
    public function test_stripe_configuration_model_methods(): void
    {
        $config = StripeConfiguration::getActive();
        $this->assertNotNull($config);

        $publicKey = StripeConfiguration::getPublicKey();
        $this->assertNotEmpty($publicKey);

        $secretKey = StripeConfiguration::getSecretKey();
        $this->assertNotEmpty($secretKey);

        $webhookSecret = StripeConfiguration::getWebhookSecret();
        $this->assertNotEmpty($webhookSecret);
    }
}

