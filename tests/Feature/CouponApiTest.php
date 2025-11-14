<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Plan;
use App\Models\Coupon;
use App\Models\Subscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CouponApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Plan $plan;
    protected Coupon $coupon;
    protected Subscription $subscription;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->plan = Plan::factory()->create([
            'name' => 'Pro',
            'price' => 49.99,
            'is_active' => true,
        ]);
        $this->coupon = Coupon::factory()->create([
            'code' => 'SAVE10',
            'type' => 'percentage',
            'value' => 10,
            'is_active' => true,
        ]);
        $this->subscription = Subscription::factory()->create([
            'user_id' => $this->user->id,
            'plan_id' => $this->plan->id,
            'stripe_status' => 'active',
        ]);
    }

    public function test_get_available_coupons(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/coupons/available');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data'
            ]);
    }

    public function test_get_coupon_details(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/coupons/SAVE10');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => ['code', 'type', 'value', 'is_valid']
            ]);
    }

    public function test_get_invalid_coupon(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/coupons/INVALID');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Coupon not found'
            ]);
    }

    public function test_coupon_is_valid(): void
    {
        $this->assertTrue($this->coupon->isValid());
    }

    public function test_coupon_calculate_discount(): void
    {
        $discount = $this->coupon->calculateDiscount(100);
        $this->assertEquals(10, $discount);
    }

    public function test_unauthorized_access_to_coupons(): void
    {
        $response = $this->getJson('/api/coupons/available');

        $response->assertStatus(401);
    }
}

