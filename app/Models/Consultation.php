<?php
// app/Models/Consultation.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// Utas konsultasi santri ↔ alumni. Dapat ditinjau Admin Pesantren (keputusan Fase 1 no. 7).
class Consultation extends Model
{
    protected $fillable = ['santri_id', 'alumni_id', 'subject', 'status', 'last_message_at'];

    protected function casts(): array
    {
        return ['last_message_at' => 'datetime'];
    }

    public function santri(): BelongsTo
    {
        return $this->belongsTo(User::class, 'santri_id');
    }

    public function alumni(): BelongsTo
    {
        return $this->belongsTo(User::class, 'alumni_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ConsultationMessage::class)->oldest();
    }

    public function reports(): HasMany
    {
        return $this->hasMany(ConsultationReport::class);
    }

    public function involves(User $user): bool
    {
        return in_array($user->id, [$this->santri_id, $this->alumni_id], true);
    }

    public function otherParty(User $user): User
    {
        return $user->id === $this->santri_id ? $this->alumni : $this->santri;
    }

    public function statusLabel(): string
    {
        return ['pending' => 'Menunggu jawaban', 'accepted' => 'Berlangsung', 'declined' => 'Ditolak', 'closed' => 'Selesai'][$this->status];
    }

    public function statusTone(): string
    {
        return ['pending' => 'gold', 'accepted' => 'blue', 'declined' => 'neutral', 'closed' => 'success'][$this->status];
    }
}
