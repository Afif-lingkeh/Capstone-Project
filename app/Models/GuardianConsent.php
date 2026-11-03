<?php
// app/Models/GuardianConsent.php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Persetujuan orang tua/wali. Konseling & Asisten AI terkunci sampai ada persetujuan yang belum dicabut.
class GuardianConsent extends Model
{
    protected $fillable = ['santri_id', 'guardian_name', 'relationship', 'method', 'consented_at', 'policy_version', 'notes', 'recorded_by', 'revoked_at'];

    protected function casts(): array
    {
        return ['consented_at' => 'date', 'revoked_at' => 'datetime'];
    }

    public function santri(): BelongsTo
    {
        return $this->belongsTo(User::class, 'santri_id');
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function scopeActive(Builder $query): void
    {
        $query->whereNull('revoked_at');
    }
}
