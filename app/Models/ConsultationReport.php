<?php
// app/Models/ConsultationReport.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsultationReport extends Model
{
    protected $fillable = ['consultation_id', 'reporter_id', 'reason', 'status', 'resolved_by', 'resolved_at', 'resolution_note'];

    protected function casts(): array
    {
        return ['resolved_at' => 'datetime'];
    }

    public function consultation(): BelongsTo
    {
        return $this->belongsTo(Consultation::class);
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }
}
