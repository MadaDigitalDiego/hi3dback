<?php

namespace Database\Factories;

use App\Models\OfferApplication;
use App\Models\OpenOffer;
use App\Models\ProfessionalProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OfferApplication>
 */
class OfferApplicationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = OfferApplication::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'open_offer_id' => OpenOffer::factory(),
            'professional_profile_id' => ProfessionalProfile::factory(),
            'proposal' => fake()->paragraphs(2, true),
            'estimated_duration' => fake()->randomElement([
                '1 semaine',
                '2 semaines',
                '1 mois',
                '2 mois',
                '3 mois',
                '6 mois',
                '1 an'
            ]),
            'proposed_budget' => fake()->numberBetween(500, 50000),
            'status' => 'pending',
            'cover_letter' => fake()->optional()->paragraphs(3, true),
            'availability_date' => fake()->dateTimeBetween('now', '+1 month')->format('Y-m-d'),
        ];
    }

    /**
     * Indicate that the application is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    /**
     * Indicate that the application is accepted.
     */
    public function accepted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'accepted',
        ]);
    }

    /**
     * Indicate that the application is rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
        ]);
    }

    /**
     * Create a detailed application with cover letter.
     */
    public function detailed(): static
    {
        return $this->state(fn (array $attributes) => [
            'proposal' => fake()->paragraphs(4, true),
            'cover_letter' => fake()->paragraphs(3, true),
        ]);
    }

    /**
     * Create a quick application without cover letter.
     */
    public function quick(): static
    {
        return $this->state(fn (array $attributes) => [
            'proposal' => fake()->paragraph(),
            'cover_letter' => null,
        ]);
    }

    /**
     * Create an application with competitive pricing.
     */
    public function competitive(): static
    {
        return $this->state(fn (array $attributes) => [
            'proposed_budget' => fake()->numberBetween(500, 2000),
            'estimated_duration' => fake()->randomElement(['1 semaine', '2 semaines', '1 mois']),
        ]);
    }

    /**
     * Create an application with premium pricing.
     */
    public function premium(): static
    {
        return $this->state(fn (array $attributes) => [
            'proposed_budget' => fake()->numberBetween(10000, 50000),
            'estimated_duration' => fake()->randomElement(['3 mois', '6 mois', '1 an']),
        ]);
    }
}
