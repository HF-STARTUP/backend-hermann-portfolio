<?php

namespace Database\Factories;

use App\Models\TechCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TechCategory>
 */
class TechCategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement(['Frontend', 'Backend', 'Mobile', 'Bases de données', 'DevOps & Cloud', 'Outils']),
        ];
    }
}
