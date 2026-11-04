<?php
// database/seeders/DemoUserSeeder.php

namespace Database\Seeders;

use App\Enums\Gender;
use App\Enums\Role;
use App\Enums\SantriLevel;
use App\Models\User;
use Illuminate\Database\Seeder;

// Akun contoh untuk demo. Semua nama di sini fiktif dan tidak merujuk orang sungguhan.
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
            // Konselor kedua: alih tangan kasus tidak bisa didemokan dengan satu konselor saja.
            ['konselor2', 'Ustadz Konselor', Role::Counselor, Gender::Male],
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
        // Empat santri agar setiap antrean (konseling, bimbingan, konsultasi) berisi lebih dari satu baris.
        // NIS 20250002 sengaja dihindari karena dipakai sebagai contoh di templat impor CSV.
        $santriList = [
            ['20250001', 'Santri Contoh', Gender::Male, SantriLevel::Smp, '7A', 2025, 'Wali Santri Contoh', 'Ingin kuliah di jurusan teknologi, tapi masih ragu antara Saintek dan Bahasa.'],
            ['20240002', 'Nabila Hasna', Gender::Female, SantriLevel::Sma, '11 IPA', 2024, 'Ibu Rahmawati', 'Bercita-cita menjadi apoteker dan ingin tahu jalur beasiswa yang tersedia.'],
            ['20230003', 'Ridho Maulana', Gender::Male, SantriLevel::Sma, '12 IPS', 2023, 'Bapak Sulaiman', 'Ingin bekerja dulu setelah lulus, sambil menabung untuk kuliah.'],
            ['20250004', 'Faris Abdullah', Gender::Male, SantriLevel::Sma, '10B', 2025, 'Bapak Hamzah', 'Masih mencari tahu minatnya; tertarik pada ilmu keagamaan.'],
        ];

        $santriIds = [];
        foreach ($santriList as [$nis, $name, $gender, $level, $class, $entryYear, $guardian, $aspiration]) {
            $santri = User::updateOrCreate(
                ['username' => $nis],
                ['name' => $name, 'email' => null, 'password' => config('peduli.demo_password'), 'gender' => $gender, 'is_active' => true],
            );
            $santri->syncRoles([Role::Santri->value]);
            $santri->santriProfile()->updateOrCreate([], [
                'nis' => $nis,
                'level' => $level,
                'class_name' => $class,
                'entry_year' => $entryYear,
                'guardian_name' => $guardian,
                'aspiration' => $aspiration,
            ]);
            $santriIds[] = $santri->id;
        }

        User::where('username', 'pembimbing')->firstOrFail()
            ->advisees()
            ->syncWithoutDetaching($santriIds);
    }
}
