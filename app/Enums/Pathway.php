<?php
// app/Enums/Pathway.php

namespace App\Enums;

// Empat jalur pemetaan minat (Fase 6). Hasil tes selalu berupa profil skor keempatnya, bukan satu label.
enum Pathway: string
{
    case Saintek = 'saintek';
    case Soshum = 'soshum';
    case Bahasa = 'bahasa';
    case Keagamaan = 'keagamaan';

    public function label(): string
    {
        return match ($this) {
            self::Saintek => 'Saintek',
            self::Soshum => 'Soshum',
            self::Bahasa => 'Bahasa',
            self::Keagamaan => 'Keagamaan',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Saintek => 'Sains, teknologi, kesehatan, dan rekayasa.',
            self::Soshum => 'Sosial, ekonomi, hukum, dan humaniora.',
            self::Bahasa => 'Bahasa, sastra, komunikasi, dan penerjemahan.',
            self::Keagamaan => 'Ilmu-ilmu keislaman, dakwah, dan pendidikan agama.',
        };
    }

    /** Warna batang skor (desain "Score/Bar"). */
    public function barClass(): string
    {
        return match ($this) {
            self::Saintek => 'bg-blue',
            self::Soshum => 'bg-gold',
            self::Bahasa => 'bg-teal',
            self::Keagamaan => 'bg-[#7C5CC4]',
        };
    }
}
