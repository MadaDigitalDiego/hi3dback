<?php

namespace Database\Factories;

use App\Models\ProfessionalProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProfessionalProfile>
 */
class ProfessionalProfileFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ProfessionalProfile::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->optional()->phoneNumber(),
            'address' => fake()->optional()->address(),
            'city' => fake()->optional()->city(),
            'country' => fake()->optional()->country(),
            'bio' => fake()->optional()->paragraph(3),
            'avatar' => fake()->optional()->imageUrl(),
            'cover_photo' => fake()->optional()->imageUrl(),
            'title' => fake()->optional()->jobTitle(),
            'profession' => fake()->randomElement([
                'Développeur Full Stack',
                'Designer UI/UX',
                'Chef de projet',
                'Développeur Frontend',
                'Développeur Backend',
                'Architecte logiciel',
                'DevOps Engineer',
                'Data Scientist',
                'Product Manager',
                'Consultant IT'
            ]),
            'expertise' => fake()->randomElements([
                'Développement Web',
                'Développement Mobile',
                'Design UI/UX',
                'E-commerce',
                'API Development',
                'Database Design'
            ], fake()->numberBetween(1, 3)),
            'years_of_experience' => fake()->numberBetween(1, 15),
            'hourly_rate' => fake()->numberBetween(25, 150),
            'description' => fake()->optional()->paragraph(2),
            'skills' => fake()->randomElements([
                'PHP', 'Laravel', 'JavaScript', 'React', 'Vue.js', 'Node.js',
                'Python', 'Django', 'Java', 'Spring', 'C#', '.NET',
                'MySQL', 'PostgreSQL', 'MongoDB', 'Redis',
                'Docker', 'Kubernetes', 'AWS', 'Azure',
                'Git', 'CI/CD', 'Agile', 'Scrum'
            ], fake()->numberBetween(3, 8)),
            'portfolio' => fake()->optional()->randomElements([
                ['title' => 'Project 1', 'url' => fake()->url()],
                ['title' => 'Project 2', 'url' => fake()->url()],
                ['title' => 'Project 3', 'url' => fake()->url()],
                ['title' => 'Project 4', 'url' => fake()->url()]
            ], fake()->numberBetween(0, 3)),
            'availability_status' => fake()->randomElement(['available', 'busy', 'unavailable']),
            'languages' => fake()->randomElements(['Français', 'Anglais', 'Espagnol', 'Allemand'], fake()->numberBetween(1, 3)),
            'services_offered' => fake()->randomElements([
                'Développement Web',
                'Développement Mobile',
                'Consulting',
                'Formation',
                'Maintenance'
            ], fake()->numberBetween(1, 3)),
            'rating' => fake()->optional()->randomFloat(1, 1, 5),
            'social_links' => fake()->optional()->randomElements([
                'linkedin' => fake()->url(),
                'github' => fake()->url(),
                'website' => fake()->url()
            ], fake()->numberBetween(0, 3)),
            'completion_percentage' => fake()->numberBetween(0, 100),
        ];
    }

    /**
     * Indicate that the professional is available.
     */
    public function available(): static
    {
        return $this->state(fn (array $attributes) => [
            'availability_status' => 'available',
        ]);
    }

    /**
     * Indicate that the professional is busy.
     */
    public function busy(): static
    {
        return $this->state(fn (array $attributes) => [
            'availability_status' => 'busy',
        ]);
    }

    /**
     * Indicate that the professional is unavailable.
     */
    public function unavailable(): static
    {
        return $this->state(fn (array $attributes) => [
            'availability_status' => 'unavailable',
        ]);
    }

    /**
     * Create a senior professional (high experience and rate).
     */
    public function senior(): static
    {
        return $this->state(fn (array $attributes) => [
            'years_of_experience' => fake()->numberBetween(8, 15),
            'hourly_rate' => fake()->numberBetween(80, 150),
            'profession' => fake()->randomElement([
                'Architecte logiciel',
                'Lead Developer',
                'CTO',
                'Senior Full Stack Developer',
                'Principal Engineer'
            ]),
        ]);
    }

    /**
     * Create a junior professional (low experience and rate).
     */
    public function junior(): static
    {
        return $this->state(fn (array $attributes) => [
            'years_of_experience' => fake()->numberBetween(1, 3),
            'hourly_rate' => fake()->numberBetween(25, 50),
            'profession' => fake()->randomElement([
                'Junior Developer',
                'Stagiaire Développeur',
                'Assistant Designer',
                'Junior Frontend Developer',
                'Junior Backend Developer'
            ]),
        ]);
    }
}
