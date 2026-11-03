<?php
// app/Enums/CounselingTopic.php

namespace App\Enums;

enum CounselingTopic: string
{
    case Akademik = 'akademik';
    case Keluarga = 'keluarga';
    case Pertemanan = 'pertemanan';
    case Pribadi = 'pribadi';
    case Karier = 'karier';
    case Ibadah = 'ibadah';
    case Lainnya = 'lainnya';

    public function label(): string
    {
        return match ($this) {
            self::Akademik => 'Belajar & akademik',
            self::Keluarga => 'Keluarga',
            self::Pertemanan => 'Pertemanan & asrama',
            self::Pribadi => 'Perasaan & diri sendiri',
            self::Karier => 'Masa depan & karier',
            self::Ibadah => 'Ibadah & spiritual',
            self::Lainnya => 'Lainnya',
        };
    }
}
