<?php
// database/factories/SantriProfileFactory.php

namespace Database\Factories;

use App\Enums\SantriLevel;
use App\Models\SantriProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SantriProfile>
 */
class SantriProfileFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'nis' => fake()->unique()->numerify('2025####'),
            'level' => fake()->randomElement(SantriLevel::cases()),
            'class_name' => fake()->randomElement(['7A', '8B', '9C', '10A', '11B', '12C']),
            'entry_year' => fake()->numberBetween(2021, 2026),
            'guardian_name' => fake()->name(),
        ];
    }
}