<?php
// app/Models/CrisisAlert.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Sinyal krisis dari permintaan konseling atau Asisten AI. Tidak menyimpan isi pesan.
class CrisisAlert extends Model
{
    protected $fillable = ['santri_id', 'source', 'source_id', 'status', 'acknowledged_by', 'acknowledged_at'];

    protected function casts(): array
    {
        return ['acknowledged_at' => 'datetime'];
    }

    public function santri(): BelongsTo
    {
        return $this->belongsTo(User::class, 'santri_id');
    }

    public function acknowledger(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acknowledged_by');
    }

    public function sourceLabel(): string
    {
        return $this->source === 'chat' ? 'Asisten AI' : 'Permintaan konseling';
    }
}
