<?php
// database/factories/OfficialLinkFactory.php

namespace Database\Factories;

use App\Enums\LinkStatus;
use App\Models\Institution;
use App\Models\OfficialLink;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OfficialLink>
 */
class OfficialLinkFactory extends Factory
{
    public function definition(): array
    {
        return [
            'institution_id' => Institution::factory(),
            'label' => 'Situs Utama',
            'url' => 'https://www.'.fake()->unique()->domainWord().'.ac.id',
            'status' => LinkStatus::Pending,
        ];
    }

    public function verified(): static
    {
        return $this->state(fn () => [
            'status' => LinkStatus::Verified,
            'verification_source' => 'Dicocokkan dengan PDDikti (data uji).',
            'verified_at' => now(),
        ]);
    }

    public function needsReview(): static
    {
        return $this->verified()->state(fn () => ['status' => LinkStatus::NeedsReview]);
    }

    public function rejected(): static
    {
        return $this->state(fn () => ['status' => LinkStatus::Rejected, 'review_note' => 'Bukan domain resmi.']);
    }
}
