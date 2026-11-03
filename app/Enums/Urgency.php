<?php
// app/Enums/Urgency.php

namespace App\Enums;

enum Urgency: string
{
    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';
    case Crisis = 'crisis';

    public function label(): string
    {
        return match ($this) {
            self::Low => 'Urgensi rendah',
            self::Medium => 'Urgensi sedang',
            self::High => 'Urgensi tinggi',
            self::Crisis => 'Krisis',
        };
    }

    /** Label yang dipakai santri saat mengajukan (bahasa sehari-hari). */
    public function santriLabel(): string
    {
        return match ($this) {
            self::Low => 'Bisa menunggu beberapa hari',
            self::Medium => 'Ingin segera dalam minggu ini',
            self::High => 'Butuh bicara secepatnya',
            self::Crisis => 'Krisis',
        };
    }

    public function tone(): string
    {
        return match ($this) {
            self::Low => 'neutral',
            self::Medium => 'warning',
            self::High, self::Crisis => 'danger',
        };
    }

    /** @return list<self> */
    public static function santriChoices(): array
    {
        return [self::Low, self::Medium, self::High];
    }
}
