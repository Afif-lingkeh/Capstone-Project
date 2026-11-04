<?php
// app/Enums/DocumentStatus.php

namespace App\Enums;

enum DocumentStatus: string
{
    case Pending = 'pending';
    case Reviewed = 'reviewed';
    case NeedsFix = 'needs_fix';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Belum diperiksa',
            self::Reviewed => 'Sudah diperiksa',
            self::NeedsFix => 'Perlu diperbaiki',
        };
    }

    public function tone(): string
    {
        return match ($this) {
            self::Pending => 'gold',
            self::Reviewed => 'success',
            self::NeedsFix => 'danger',
        };
    }
}
