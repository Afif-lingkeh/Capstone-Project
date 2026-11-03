<?php
// app/Models/CounselingSession.php

namespace App\Models;

use App\Enums\SessionMode;
use App\Enums\Urgency;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Catatan sesi terstruktur. Semua isi catatan terenkripsi.
class CounselingSession extends Model
{
    protected $fillable = ['counseling_case_id', 'counselor_id', 'held_at', 'mode', 'chief_complaint', 'assessment', 'intervention', 'follow_up_plan', 'urgency', 'next_session_at'];

    protected $hidden = ['chief_complaint', 'assessment', 'intervention', 'follow_up_plan'];

    protected function casts(): array
    {
        return [
            'held_at' => 'datetime',
            'next_session_at' => 'datetime',
            'mode' => SessionMode::class,
            'urgency' => Urgency::class,
            'chief_complaint' => 'encrypted',
            'assessment' => 'encrypted',
            'intervention' => 'encrypted',
            'follow_up_plan' => 'encrypted',
        ];
    }

    public function case(): BelongsTo
    {
        return $this->belongsTo(CounselingCase::class, 'counseling_case_id');
    }

    public function counselor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'counselor_id');
    }
}
