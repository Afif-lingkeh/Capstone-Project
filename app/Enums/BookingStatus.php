<?php
// app/Enums/BookingStatus.php

namespace App\Enums;

enum BookingStatus: string
{
    case Booked = 'booked';
    case Completed = 'completed';
    case NoShow = 'no_show';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Booked => 'Terjadwal',
            self::Completed => 'Selesai',
            self::NoShow => 'Tidak hadir',
            self::Cancelled => 'Dibatalkan',
        };
    }

    public function tone(): string
    {
        return match ($this) {
            self::Booked => 'blue',
            self::Completed => 'success',
            self::NoShow => 'warning',
            self::Cancelled => 'neutral',
        };
    }
}
