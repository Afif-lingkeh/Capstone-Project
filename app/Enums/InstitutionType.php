<?php
// app/Enums/InstitutionType.php

namespace App\Enums;

enum InstitutionType: string
{
    case Ptn = 'ptn';
    case Pts = 'pts';
    case International = 'international';
    case Kedinasan = 'kedinasan';

    public function label(): string
    {
        return match ($this) {
            self::Ptn => 'PTN',
            self::Pts => 'PTS',
            self::International => 'Universitas Luar Negeri',
            self::Kedinasan => 'Sekolah Kedinasan',
        };
    }

    // Label ringkas untuk chip filter dan tag kartu institusi.
    public function shortLabel(): string
    {
        return match ($this) {
            self::International => 'Luar Negeri',
            self::Kedinasan => 'Kedinasan',
            default => $this->label(),
        };
    }
}
