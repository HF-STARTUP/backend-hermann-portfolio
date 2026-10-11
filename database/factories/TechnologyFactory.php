<?php

namespace Database\Factories;

use App\Models\TechCategory;
use App\Models\Technology;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Technology>
 */
class TechnologyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tech_category_id' => TechCategory::factory(),
            'name' => fake()->unique()->word(),
            'image_url' => null,
        ];
    }
}
