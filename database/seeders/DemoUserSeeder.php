<?php
// database/seeders/DemoUserSeeder.php

namespace Database\Seeders;

use App\Enums\Gender;
use App\Enums\Role;
use App\Enums\SantriLevel;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->isProduction() && ! config('peduli.allow_demo_seed')) {
            $this->command?->warn('DemoUserSeeder dilewati di production.');

            return;
        }

        $accounts = [
            ['superadmin', 'Super Admin', Role::SuperAdmin, Gender::Male],
            ['admin', 'Admin Pesantren', Role::PesantrenAdmin, Gender::Male],
            ['konselor', 'Ustadzah Konselor', Role::Counselor, Gender::Female],
            ['pembimbing', 'Ustadz Pembimbing', Role::AcademicAdvisor, Gender::Male],
            ['alumni', 'Alumni Contoh', Role::Alumni, Gender::Male],
        ];

        foreach ($accounts as [$username, $name, $role, $gender]) {
            User::updateOrCreate(
                ['username' => $username],
                [
                    'name' => $name,
                    'email' => "{$username}@peduli.test",
                    'password' => config('peduli.demo_password'),
                    'gender' => $gender,
                    'is_active' => true,
                ],
            )->syncRoles([$role->value]);
        }

        // Santri contoh tanpa email: login memakai NIS.
        $santri = User::updateOrCreate(
            ['username' => '20250001'],
            ['name' => 'Santri Contoh', 'email' => null, 'password' => config('peduli.demo_password'), 'gender' => Gender::Male, 'is_active' => true],
        );
        $santri->syncRoles([Role::Santri->value]);
        $santri->santriProfile()->updateOrCreate([], [
            'nis' => '20250001',
            'level' => SantriLevel::Smp,
            'class_name' => '7A',
            'entry_year' => 2025,
            'guardian_name' => 'Wali Santri Contoh',
        ]);

        User::where('username', 'pembimbing')->firstOrFail()
            ->advisees()
            ->syncWithoutDetaching([$santri->id]);
    }
}