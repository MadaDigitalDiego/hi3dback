<?php

namespace Database\Factories;

use App\Models\OpenOffer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OpenOffer>
 */
class OpenOfferFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = OpenOffer::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(6),
            'description' => fake()->paragraphs(3, true),
            'categories' => fake()->randomElements([
                'Développement Web',
                'Développement Mobile',
                'Design UI/UX',
                'E-commerce',
                'Marketing Digital',
                'SEO/SEA',
                'Rédaction',
                'Traduction',
                'Consulting',
                'Formation'
            ], fake()->numberBetween(1, 3)),
            'budget' => fake()->randomElement([
                '500-1000',
                '1000-2500',
                '2500-5000',
                '5000-10000',
                '10000-25000',
                '25000+'
            ]),
            'deadline' => fake()->dateTimeBetween('+1 week', '+6 months')->format('Y-m-d'),
            'company' => fake()->company(),
            'website' => fake()->optional()->url(),
            'recruitment_type' => fake()->randomElement(['freelance', 'company', 'both']),
            'status' => 'open',
            'open_to_applications' => true,
            'auto_invite' => fake()->boolean(30), // 30% chance d'être true
            'location' => fake()->optional()->city(),
            'remote_work' => fake()->boolean(70), // 70% chance d'être true
            'required_skills' => fake()->randomElements([
                'PHP', 'Laravel', 'JavaScript', 'React', 'Vue.js', 'Node.js',
                'Python', 'Django', 'Java', 'Spring', 'C#', '.NET',
                'MySQL', 'PostgreSQL', 'MongoDB', 'Redis',
                'Docker', 'Kubernetes', 'AWS', 'Azure',
                'Git', 'CI/CD', 'Agile', 'Scrum'
            ], fake()->numberBetween(2, 6)),
            'experience_level' => fake()->randomElement(['junior', 'intermediate', 'senior', 'expert']),
        ];
    }

    /**
     * Indicate that the offer is open.
     */
    public function open(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'open',
            'open_to_applications' => true,
        ]);
    }

    /**
     * Indicate that the offer is in progress.
     */
    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'in_progress',
            'open_to_applications' => false,
        ]);
    }

    /**
     * Indicate that the offer is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'open_to_applications' => false,
        ]);
    }

    /**
     * Indicate that the offer is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
            'open_to_applications' => false,
        ]);
    }

    /**
     * Create a high-budget offer.
     */
    public function highBudget(): static
    {
        return $this->state(fn (array $attributes) => [
            'budget' => fake()->randomElement(['10000-25000', '25000+']),
            'experience_level' => fake()->randomElement(['senior', 'expert']),
        ]);
    }

    /**
     * Create a low-budget offer.
     */
    public function lowBudget(): static
    {
        return $this->state(fn (array $attributes) => [
            'budget' => fake()->randomElement(['500-1000', '1000-2500']),
            'experience_level' => fake()->randomElement(['junior', 'intermediate']),
        ]);
    }

    /**
     * Create an urgent offer (short deadline).
     */
    public function urgent(): static
    {
        return $this->state(fn (array $attributes) => [
            'deadline' => fake()->dateTimeBetween('+1 day', '+2 weeks')->format('Y-m-d'),
            'title' => 'URGENT - ' . fake()->sentence(5),
        ]);
    }

    /**
     * Create a remote-only offer.
     */
    public function remoteOnly(): static
    {
        return $this->state(fn (array $attributes) => [
            'remote_work' => true,
            'location' => null,
        ]);
    }

    /**
     * Create an on-site only offer.
     */
    public function onSiteOnly(): static
    {
        return $this->state(fn (array $attributes) => [
            'remote_work' => false,
            'location' => fake()->city(),
        ]);
    }
}
