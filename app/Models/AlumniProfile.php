<?php
// app/Models/AlumniProfile.php

namespace App\Models;

use App\Enums\Pathway;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class AlumniProfile extends Model
{
    protected $fillable = [
        'graduation_year', 'institution_name', 'field', 'pathway', 'city', 'country_id', 'activity', 'bio',
        'linkedin_url', 'public_email', 'show_linkedin', 'show_email', 'whatsapp', 'accepts_consultations',
    ];

    // Nomor WhatsApp tidak pernah ikut terserialisasi ke santri.
    protected $hidden = ['whatsapp'];

    protected function casts(): array
    {
        return [
            'pathway' => Pathway::class,
            'show_linkedin' => 'boolean',
            'show_email' => 'boolean',
            'accepts_consultations' => 'boolean',
            'verified_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function scopeVerified(Builder $query): void
    {
        $query->where('verification_status', 'verified');
    }

    public function isVerified(): bool
    {
        return $this->verification_status === 'verified';
    }

    public function isAbroad(): bool
    {
        return $this->country && $this->country->iso2 !== 'ID';
    }

    /** Nama yang ditampilkan ke santri: nama depan + inisial, mis. "Rizki M.". */
    public function publicName(): string
    {
        $parts = preg_split('/\s+/', trim($this->user->name));

        return count($parts) > 1 ? $parts[0].' '.Str::upper(Str::substr(end($parts), 0, 1)).'.' : $parts[0];
    }

    public function initials(): string
    {
        return collect(preg_split('/\s+/', trim($this->user->name)))->take(2)->map(fn ($w) => Str::upper(Str::substr($w, 0, 1)))->join('');
    }

    public function location(): string
    {
        return collect([$this->city, $this->country?->name])->filter()->join(', ');
    }

    public function activityLabel(): string
    {
        return ['studying' => 'Kuliah', 'working' => 'Bekerja', 'other' => 'Lainnya'][$this->activity ?? 'studying'] ?? 'Lainnya';
    }
}
