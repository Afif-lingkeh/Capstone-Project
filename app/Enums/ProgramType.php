<?php
// app/Enums/ProgramType.php

namespace App\Enums;

enum ProgramType: string
{
    case Work = 'work';
    case Internship = 'internship';

    public function label(): string
    {
        return match ($this) {
            self::Work => 'Kerja',
            self::Internship => 'Magang',
        };
    }
}
