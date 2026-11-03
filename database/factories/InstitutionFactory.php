<?php
// database/factories/InstitutionFactory.php

namespace Database\Factories;

use App\Enums\InstitutionType;
use App\Models\Country;
use App\Models\Institution;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Institution>
 */
class InstitutionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => 'Universitas '.fake()->unique()->lastName(),
            'type' => fake()->randomElement(InstitutionType::cases()),
            'country_id' => Country::factory(),
            'city' => fake()->city(),
            'description' => null,
        ];
    }

    public function ofType(InstitutionType $type): static
    {
        return $this->state(fn () => ['type' => $type]);
    }
}
