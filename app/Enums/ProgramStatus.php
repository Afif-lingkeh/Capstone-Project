<?php
// app/Enums/ProgramStatus.php

namespace App\Enums;

enum ProgramStatus: string
{
    case Open = 'open';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Buka',
            self::Closed => 'Tutup',
        };
    }

    public function tone(): string
    {
        return match ($this) {
            self::Open => 'blue',
            self::Closed => 'neutral',
        };
    }
}
