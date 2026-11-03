<?php
// app/Enums/LinkStatus.php

namespace App\Enums;

enum LinkStatus: string
{
    case Pending = 'pending';
    case Verified = 'verified';
    case NeedsReview = 'needs_review';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu Verifikasi',
            self::Verified => 'Terverifikasi',
            self::NeedsReview => 'Perlu Ditinjau Ulang',
            self::Rejected => 'Ditolak',
        };
    }

    // Warna badge (komponen x-badge): Menunggu = emas, Terverifikasi = hijau, Perlu ditinjau = merah, Ditolak = abu-abu.
    public function tone(): string
    {
        return match ($this) {
            self::Pending => 'gold',
            self::Verified => 'success',
            self::NeedsReview => 'danger',
            self::Rejected => 'neutral',
        };
    }
}
