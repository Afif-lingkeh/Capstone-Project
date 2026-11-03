<?php
// app/Enums/SantriLevel.php

namespace App\Enums;

enum SantriLevel: string
{
    case Smp = 'smp';
    case Sma = 'sma';

    public function label(): string
    {
        return match ($this) {
            self::Smp => 'SMP',
            self::Sma => 'SMA',
        };
    }
}