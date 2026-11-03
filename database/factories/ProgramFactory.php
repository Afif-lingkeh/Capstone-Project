<?php

namespace Database\Factories;

use App\Enums\ProgramStatus;
use App\Enums\ProgramType;
use App\Models\Partner;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\Program> */
class ProgramFactory extends Factory
{
    public function definition(): array
    {
        return [
            'partner_id' => Partner::factory(),
            'country_id' => fn (array $attributes) => Partner::find($attributes['partner_id'])->country_id,
            'title' => 'Program '.fake()->unique()->words(3, true),
            'type' => ProgramType::Internship,
            'summary' => fake()->sentence(12),
            'requirements' => "Lulus SMA/sederajat\nSehat jasmani dan rohani",
            'costs' => 'Rincian biaya resmi dari mitra.',
            'min_education' => 'Lulus SMA/sederajat',
            'deadline' => now()->addMonth(),
            'official_contact' => 'Admin pesantren',
            'status' => ProgramStatus::Open,
            'published_at' => now()->subDay(),
        ];
    }

    public function draft(): static
    {
        return $this->state(['published_at' => null]);
    }

    public function closed(): static
    {
        return $this->state(['status' => ProgramStatus::Closed]);
    }
}
