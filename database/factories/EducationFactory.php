<?php

namespace Database\Factories;

use App\Models\Education;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Education>
 */
class EducationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'school' => fake()->randomElement(['Université de Douala', 'ENSPY', 'Certifications en ligne']),
            'degree' => fake()->sentence(3),
            'description' => fake()->paragraph(1),
            'start_date' => fake()->date('Y-m-d', '-6 years'),
            'end_date' => fake()->date('Y-m-d', '-3 years'),
            'sort_order' => 0,
        ];
    }
}
