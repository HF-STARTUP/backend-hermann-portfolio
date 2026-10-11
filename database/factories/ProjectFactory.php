<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\ProjectCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->words(asText: true);

        return [
            'project_category_id' => ProjectCategory::factory(),
            'slug' => str($title)->slug(),
            'title' => ucwords($title),
            'role' => fake()->randomElement(['Développeur Frontend', 'Développeur Fullstack', 'UI Engineer']),
            'description' => fake()->sentence(12),
            'long_description' => null,
            'image_url' => null,
            'tech_stack' => ['React', 'TypeScript'],
            'demo_url' => null,
            'github_url' => null,
            'featured' => false,
            'start_date' => fake()->date('Y-m-d', '-1 year'),
            'end_date' => null,
        ];
    }

    /**
     * Projet rattaché à une catégorie donnée.
     */
    public function forCategory(ProjectCategory $category): static
    {
        return $this->state(fn () => [
            'project_category_id' => $category->id,
        ]);
    }
}
