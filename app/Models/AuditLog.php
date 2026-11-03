<?php
// app/Models/AuditLog.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Hanya metadata akses. Log tidak bisa diubah lewat aplikasi (tidak ada route update/hapus).
class AuditLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['user_id', 'action', 'subject_type', 'subject_id', 'santri_id', 'ip_address'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function santri(): BelongsTo
    {
        return $this->belongsTo(User::class, 'santri_id');
    }

    public function actionLabel(): string
    {
        return [
            'request.view' => 'Membuka permintaan konseling',
            'request.accept' => 'Menerima permintaan konseling',
            'case.view' => 'Membuka catatan kasus',
            'session.create' => 'Menulis catatan sesi',
            'case.close' => 'Menutup kasus',
            'handover.request' => 'Mengajukan alih tangan',
            'handover.accept' => 'Menerima alih tangan',
            'handover.reject' => 'Menolak alih tangan',
            'alert.acknowledge' => 'Menindaklanjuti peringatan krisis',
            'consultation.moderate' => 'Meninjau konsultasi alumni',
            'consent.record' => 'Mencatat persetujuan wali',
            'consent.revoke' => 'Mencabut persetujuan wali',
        ][$this->action] ?? $this->action;
    }
}
