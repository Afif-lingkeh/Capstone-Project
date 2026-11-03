<?php

namespace Database\Factories;

use App\Enums\ArticleCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\Article> */
class ArticleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => 'Panduan '.fake()->unique()->words(3, true),
            'category' => ArticleCategory::Snbp,
            'excerpt' => fake()->sentence(15),
            'body' => fake()->paragraphs(3, true),
            'published_at' => now()->subDay(),
        ];
    }

    public function draft(): static
    {
        return $this->state(['published_at' => null]);
    }
}
