<?php

namespace Database\Factories;

use App\Models\SocialLink;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SocialLink>
 */
class SocialLinkFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'label' => fake()->unique()->randomElement(['GitHub', 'LinkedIn', 'Twitter', 'Email']),
            'url' => fake()->url(),
            'icon_name' => 'Github',
            'sort_order' => 0,
        ];
    }
}
