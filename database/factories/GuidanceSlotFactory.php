<?php

namespace Database\Factories;

use App\Enums\Role;
use App\Enums\SessionMode;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\GuidanceSlot> */
class GuidanceSlotFactory extends Factory
{
    public function definition(): array
    {
        $start = now()->addDays(fake()->numberBetween(1, 10))->setTime(9, 0);

        return [
            'advisor_id' => User::factory()->withRole(Role::AcademicAdvisor),
            'starts_at' => $start,
            'ends_at' => $start->copy()->addMinutes(30),
            'mode' => SessionMode::Online,
            'location' => 'https://meet.google.com/[kode]',
        ];
    }

    public function past(): static
    {
        return $this->state(fn () => ['starts_at' => now()->subDay(), 'ends_at' => now()->subDay()->addMinutes(30)]);
    }
}
