<?php
// app/Enums/Role.php

namespace App\Enums;

enum Role: string
{
    // Urutan case = prioritas saat menentukan dashboard tujuan.
    case SuperAdmin = 'super-admin';
    case PesantrenAdmin = 'pesantren-admin';
    case Counselor = 'counselor';
    case AcademicAdvisor = 'academic-advisor';
    case Alumni = 'alumni';
    case Santri = 'santri';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Admin',
            self::PesantrenAdmin => 'Admin Pesantren',
            self::Counselor => 'Konselor',
            self::AcademicAdvisor => 'Pembimbing Akademik',
            self::Alumni => 'Alumni',
            self::Santri => 'Santri',
        };
    }

    public function dashboardRoute(): string
    {
        return match ($this) {
            self::SuperAdmin, self::PesantrenAdmin => 'admin.dashboard',
            self::Counselor => 'counselor.dashboard',
            self::AcademicAdvisor => 'advisor.dashboard',
            self::Alumni => 'alumni.dashboard',
            self::Santri => 'santri.dashboard',
        };
    }
}