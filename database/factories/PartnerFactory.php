<?php

namespace Database\Factories;

use App\Models\Country;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\Partner> */
class PartnerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => 'LPK '.fake()->unique()->company(),
            'country_id' => Country::factory(),
            'description' => fake()->sentence(),
            'website' => 'https://'.fake()->domainName(),
            'contact_email' => fake()->companyEmail(),
        ];
    }
}
