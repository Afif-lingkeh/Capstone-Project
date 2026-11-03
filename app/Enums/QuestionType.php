<?php
// app/Enums/QuestionType.php

namespace App\Enums;

enum QuestionType: string
{
    // Pilihan ganda: setiap opsi punya bobot per jalur.
    case Choice = 'choice';
    // Skala Likert 1–5: pernyataan mengukur satu jalur; skor = nilai jawaban − 1 (0–4).
    case Likert = 'likert';

    public function label(): string
    {
        return match ($this) {
            self::Choice => 'Pilihan ganda',
            self::Likert => 'Skala Likert (1–5)',
        };
    }

    /** @return array<int, string> */
    public static function likertScale(): array
    {
        return [1 => 'Sangat tidak setuju', 2 => 'Tidak setuju', 3 => 'Netral', 4 => 'Setuju', 5 => 'Sangat setuju'];
    }
}
