<?php
// app/Models/CounselingCase.php

namespace App\Models;

use App\Enums\CounselingStatus;
use App\Enums\Urgency;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CounselingCase extends Model
{
    protected $fillable = ['code', 'santri_id', 'counselor_id', 'status', 'urgency', 'closed_at', 'closing_summary'];

    protected $hidden = ['closing_summary'];

    protected function casts(): array
    {
        return [
            'status' => CounselingStatus::class,
            'urgency' => Urgency::class,
            'closed_at' => 'datetime',
            'closing_summary' => 'encrypted',
        ];
    }

    protected static function booted(): void
    {
        // Kode kasus berurutan per tahun, mis. K-2026-014.
        static::creating(function (CounselingCase $case) {
            $year = now()->year;
            $next = static::where('code', 'like', "K-{$year}-%")->count() + 1;
            $case->code ??= sprintf('K-%d-%03d', $year, $next);
        });
    }

    public function santri(): BelongsTo
    {
        return $this->belongsTo(User::class, 'santri_id');
    }

    public function counselor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'counselor_id');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(CounselingSession::class)->orderBy('held_at');
    }

    public function request(): HasOne
    {
        return $this->hasOne(CounselingRequest::class);
    }

    public function handovers(): HasMany
    {
        return $this->hasMany(CaseHandover::class);
    }

    public function pendingHandover(): HasOne
    {
        return $this->hasOne(CaseHandover::class)->where('status', CounselingStatus::Pending);
    }

    public function isOpen(): bool
    {
        return $this->status === CounselingStatus::Open;
    }
}
