<?php

namespace Database\Factories;

use App\Models\Coupon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Coupon>
 */
class CouponFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = Coupon::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $now = now();

        return [
            'code' => strtoupper($this->faker->unique()->bothify('COUPON-####')),
            'stripe_coupon_id' => null,
            'description' => $this->faker->sentence(),
            'type' => $this->faker->randomElement(['fixed', 'percentage']),
            'value' => $this->faker->randomFloat(2, 1, 100),
            'max_discount' => null,
            'max_uses' => null,
            'used_count' => 0,
            'is_active' => true,
            // Start in the past and expire in the future by default so coupons are valid
            'starts_at' => (clone $now)->subDay(),
            'expires_at' => (clone $now)->addMonth(),
            'applicable_plans' => null,
            'metadata' => [],
        ];
    }
}
