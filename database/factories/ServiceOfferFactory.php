<?php

namespace Database\Factories;

use App\Models\ServiceOffer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ServiceOffer>
 */
class ServiceOfferFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ServiceOffer::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->paragraphs(3, true),
            'price' => $this->faker->randomFloat(2, 100, 5000),
            'execution_time' => $this->faker->randomElement(['1-2 jours', '3-5 jours', '1 semaine', '2 semaines', '1 mois']),
            'concepts' => $this->faker->numberBetween(1, 5),
            'revisions' => $this->faker->numberBetween(1, 3),
            'is_private' => $this->faker->boolean(20), // 20% chance d'être privé
            'status' => $this->faker->randomElement(['active', 'draft', 'pending']),
            'categories' => $this->faker->randomElements([
                'Architecture',
                'Modélisation 3D',
                'Rendu',
                'Animation',
                'Design d\'intérieur',
                'Paysagisme',
                'Urbanisme'
            ], $this->faker->numberBetween(1, 3)),
            'files' => null, // Pas de fichiers par défaut dans les tests
            'views' => $this->faker->numberBetween(0, 1000),
            'likes' => $this->faker->numberBetween(0, 100),
            'rating' => $this->faker->optional(0.7)->randomFloat(1, 1, 5), // 70% chance d'avoir une note
            'what_you_get' => $this->faker->optional(0.8)->paragraphs(2, true),
            'who_is_this_for' => $this->faker->optional(0.8)->paragraphs(1, true),
            'delivery_method' => $this->faker->optional(0.8)->sentence(),
            'why_choose_me' => $this->faker->optional(0.8)->paragraphs(2, true),
        ];
    }

    /**
     * Indicate that the service offer is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    /**
     * Indicate that the service offer is private.
     */
    public function private(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_private' => true,
        ]);
    }

    /**
     * Indicate that the service offer is public.
     */
    public function public(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_private' => false,
        ]);
    }
}
