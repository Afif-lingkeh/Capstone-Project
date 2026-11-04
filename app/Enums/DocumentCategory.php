<?php
// app/Enums/DocumentCategory.php

namespace App\Enums;

enum DocumentCategory: string
{
    case Rapor = 'rapor';
    case Sertifikat = 'sertifikat';
    case Surat = 'surat';
    case Portofolio = 'portofolio';
    case Pendaftaran = 'pendaftaran';
    case Lainnya = 'lainnya';

    public function label(): string
    {
        return match ($this) {
            self::Rapor => 'Rapor',
            self::Sertifikat => 'Sertifikat & prestasi',
            self::Surat => 'Surat keterangan',
            self::Portofolio => 'Portofolio & karya',
            self::Pendaftaran => 'Berkas pendaftaran',
            self::Lainnya => 'Lainnya',
        };
    }

    public function hint(): string
    {
        return match ($this) {
            self::Rapor => 'Rapor per semester, untuk SNBP dan beasiswa.',
            self::Sertifikat => 'Piagam lomba, tahfidz, organisasi.',
            self::Surat => 'Surat rekomendasi, keterangan aktif, dan sejenisnya.',
            self::Portofolio => 'Karya tulis, desain, proyek.',
            self::Pendaftaran => 'Bukti daftar kampus atau program.',
            self::Lainnya => 'Berkas lain yang perlu disimpan.',
        };
    }
}
