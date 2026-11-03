<?php
// app/Enums/LinkCheckResult.php

namespace App\Enums;

enum LinkCheckResult: string
{
    case Ok = 'ok';
    case Dead = 'dead';                    // 404 / 410: halaman sudah tidak ada
    case DomainChanged = 'domain_changed'; // dialihkan ke domain lain
    case Error = 'error';                  // timeout, 5xx, diblokir: bisa jadi sementara

    public function label(): string
    {
        return match ($this) {
            self::Ok => 'Normal',
            self::Dead => 'Tidak ditemukan',
            self::DomainChanged => 'Domain berubah',
            self::Error => 'Gagal diakses',
        };
    }

    public function tone(): string
    {
        return match ($this) {
            self::Ok => 'success',
            self::Dead, self::DomainChanged => 'danger',
            self::Error => 'warning',
        };
    }

    /** Hasil yang langsung menandai tautan untuk ditinjau ulang, tanpa menunggu pengecekan berikutnya. */
    public function requiresImmediateReview(): bool
    {
        return in_array($this, [self::Dead, self::DomainChanged], true);
    }
}
