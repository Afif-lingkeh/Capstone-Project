<?php
// app/Enums/TestDimension.php

namespace App\Enums;

enum TestDimension: string
{
    case Academic = 'academic';
    case Interest = 'interest';

    public function label(): string
    {
        return match ($this) {
            self::Academic => 'Kemampuan akademik',
            self::Interest => 'Minat & bakat',
        };
    }
}
