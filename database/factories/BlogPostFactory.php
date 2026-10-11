<?php

namespace Database\Factories;

use App\Models\BlogPost;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BlogPost>
 */
class BlogPostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->sentence(6);

        return [
            'slug' => str($title)->slug(),
            'title' => $title,
            'excerpt' => fake()->paragraph(2),
            'cover_image_url' => null,
            'cover_type' => 'image',
            'cover_video_url' => null,
            'cover_youtube_url' => null,
            'tags' => ['React'],
            'published_at' => fake()->date('Y-m-d', '-1 month'),
            'read_time_minutes' => fake()->numberBetween(1, 20),
            'content' => [
                ['type' => 'paragraph', 'text' => fake()->paragraph(4)],
            ],
        ];
    }
}
