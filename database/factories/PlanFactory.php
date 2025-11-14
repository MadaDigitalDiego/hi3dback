<?php

namespace Database\Factories;

use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Plan>
 */
class PlanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(3),
            'name' => $this->faker->word(),
            'user_type' => $this->faker->randomElement(['professional', 'client']),
            'description' => $this->faker->paragraph(),
            'price' => $this->faker->randomFloat(2, 0, 100),
            'interval' => 'month',
            'interval_count' => 1,
            'is_active' => true,
            'sort_order' => $this->faker->numberBetween(0, 10),
            'features' => [
                'basic_profile' => true,
                'messaging' => true,
            ],
            'limits' => [
                'service_offers' => $this->faker->numberBetween(5, 50),
                'open_offers' => $this->faker->numberBetween(10, 100),
            ],
        ];
    }

    /**
     * Create a professional plan.
     */
    public function professional(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_type' => 'professional',
            'name' => 'Professional Plan',
        ]);
    }

    /**
     * Create a client plan.
     */
    public function client(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_type' => 'client',
            'name' => 'Client Plan',
        ]);
    }

    /**
     * Create a premium plan with high limits.
     */
    public function premium(): static
    {
        return $this->state(fn (array $attributes) => [
            'price' => 99.99,
            'max_services' => 100,
            'max_open_offers' => 200,
            'max_applications' => 1000,
            'max_messages' => 5000,
        ]);
    }

    /**
     * Create a free plan.
     */
    public function free(): static
    {
        return $this->state(fn (array $attributes) => [
            'price' => 0,
            'max_services' => 3,
            'max_open_offers' => 5,
            'max_applications' => 10,
            'max_messages' => 50,
        ]);
    }
}

