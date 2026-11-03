<?php
// app/Services/AuditLogger.php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

// Mencatat siapa membuka/mengubah data sensitif, kapan, dan dari mana. Tanpa isi data.
class AuditLogger
{
    public static function record(string $action, ?Model $subject = null, ?int $santriId = null): void
    {
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'subject_type' => $subject ? class_basename($subject) : null,
            'subject_id' => $subject?->getKey(),
            'santri_id' => $santriId,
            'ip_address' => request()->ip(),
        ]);
    }
}
