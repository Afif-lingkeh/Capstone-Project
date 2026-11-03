<?php
// app/Enums/CounselingStatus.php

namespace App\Enums;

// Dipakai untuk permintaan (pending/accepted/cancelled), kasus (open/closed), dan alih tangan (pending/accepted/rejected).
enum CounselingStatus: string
{
    case Pending = 'pending';
    case Accepted = 'accepted';
    case Rejected = 'rejected';
    case Cancelled = 'cancelled';
    case Open = 'open';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu',
            self::Accepted => 'Diterima',
            self::Rejected => 'Ditolak',
            self::Cancelled => 'Dibatalkan',
            self::Open => 'Aktif',
            self::Closed => 'Selesai',
        };
    }

    public function tone(): string
    {
        return match ($this) {
            self::Pending => 'gold',
            self::Accepted, self::Open => 'blue',
            self::Closed => 'success',
            self::Rejected, self::Cancelled => 'neutral',
        };
    }
}
