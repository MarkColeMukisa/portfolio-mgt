<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\User;
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
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(3),
            'image_url' => fake()->imageUrl(1280, 800, 'technics'),
            'image_public_id' => 'project_images/test/'.fake()->unique()->slug(),
            'thumbnail_url' => fake()->imageUrl(960, 600, 'technics'),
            'thumbnail_public_id' => 'project_thumbnails/test/'.fake()->unique()->slug(),
        ];
    }
}
