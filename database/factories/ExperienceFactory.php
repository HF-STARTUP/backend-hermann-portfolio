<?php

namespace Database\Factories;

use App\Models\Experience;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Experience>
 */
class ExperienceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company' => fake()->company(),
            'position' => fake()->jobTitle(),
            'description' => fake()->paragraph(2),
            'location' => fake()->city(),
            'start_date' => fake()->date('Y-m-d', '-3 years'),
            'end_date' => fake()->date('Y-m-d', '-1 year'),
            'sort_order' => 0,
        ];
    }
}
