<?php

namespace Database\Factories;

use App\Models\Achievement;
use App\Models\ProfessionalProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Achievement>
 */
class AchievementFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Achievement::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'professional_profile_id' => ProfessionalProfile::factory(),
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->paragraphs(2, true),
            'category' => $this->faker->randomElement(['Architecture', 'Intérieur', 'Paysage', 'Autre']),
            'cover_photo' => $this->faker->imageUrl(640, 480, 'projects', true),
            'gallery_photos' => [$this->faker->imageUrl(640, 480, 'projects', true), $this->faker->imageUrl(640, 480, 'projects', true)],
            'youtube_link' => $this->faker->optional()->url(),
            'status' => $this->faker->randomElement(['active', 'inactive']),
        ];
    }

    /**
     * Indicate that the achievement has a certificate URL.
     */
    public function withUrl(): static
    {
        return $this->state(fn (array $attributes) => [
            'achievement_url' => $this->faker->url(),
        ]);
    }

    /**
     * Indicate that the achievement is recent (within last year).
     */
    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'date_obtained' => $this->faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
        ]);
    }

    /**
     * Indicate that the achievement is for a specific type of project.
     */
    public function projectType(string $type): static
    {
        $titles = [
            'architecture' => [
                'Villa Moderne - Projet Résidentiel',
                'Immeuble de Bureaux - Centre Ville',
                'Rénovation Patrimoine Historique',
                'Complexe Commercial - Zone Urbaine'
            ],
            'interior' => [
                'Aménagement Appartement Haussmannien',
                'Design Restaurant Gastronomique',
                'Bureaux Open Space - Startup Tech',
                'Showroom Automobile Premium'
            ],
            'landscape' => [
                'Parc Urbain - Aménagement Paysager',
                'Jardin Privé - Villa de Luxe',
                'Espace Vert Entreprise',
                'Place Publique - Réaménagement'
            ]
        ];

        return $this->state(fn (array $attributes) => [
            'title' => $this->faker->randomElement($titles[$type] ?? $titles['architecture']),
        ]);
    }
}
