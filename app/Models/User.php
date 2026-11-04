<?php
// app/Models/User.php

namespace App\Models;

use App\Enums\Gender;
use App\Enums\Role;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'gender',
        'phone',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'gender' => Gender::class,
        ];
    }

    public function santriProfile(): HasOne
    {
        return $this->hasOne(SantriProfile::class);
    }

    /** Santri binaan (dilihat dari sisi pembimbing). */
    public function advisees(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'advisor_assignments', 'advisor_id', 'santri_id')
            ->withTimestamps();
    }

    /** Pembimbing (dilihat dari sisi santri). */
    public function advisors(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'advisor_assignments', 'santri_id', 'advisor_id')
            ->withTimestamps();
    }

    /** Berkas milik santri (rapor, sertifikat, dsb.). */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'owner_id');
    }

    /** URL foto profil (lewat route yang memeriksa hak akses), atau null bila belum ada. */
    public function avatarUrl(): ?string
    {
        return $this->avatar_path ? route('avatars.show', ['user' => $this->id, 'v' => substr(md5($this->avatar_path), 0, 8)]) : null;
    }

    public function initials(): string
    {
        return collect(preg_split('/\s+/', trim($this->name)))->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->join('');
    }

    public function chatConversations(): HasMany
    {
        return $this->hasMany(ChatConversation::class);
    }

    public function alumniProfile(): HasOne
    {
        return $this->hasOne(AlumniProfile::class);
    }

    public function guardianConsents(): HasMany
    {
        return $this->hasMany(GuardianConsent::class, 'santri_id');
    }

    /** Konseling & Asisten AI hanya terbuka bila persetujuan wali tercatat dan belum dicabut. */
    public function hasGuardianConsent(): bool
    {
        return $this->guardianConsents()->active()->exists();
    }

    public function counselingRequests(): HasMany
    {
        return $this->hasMany(CounselingRequest::class, 'santri_id');
    }

    public function testAttempts(): HasMany
    {
        return $this->hasMany(TestAttempt::class);
    }

    /** Pemesanan bimbingan milik santri. */
    public function guidanceBookings(): HasMany
    {
        return $this->hasMany(GuidanceBooking::class, 'santri_id');
    }

    /** Slot bimbingan yang dibuka pembimbing. */
    public function guidanceSlots(): HasMany
    {
        return $this->hasMany(GuidanceSlot::class, 'advisor_id');
    }

    public function bookmarkedPrograms(): BelongsToMany
    {
        return $this->belongsToMany(Program::class, 'program_bookmarks')->withTimestamps();
    }

    public function programInterests(): HasMany
    {
        return $this->hasMany(ProgramInterest::class);
    }

    /** Satu akun = satu peran. Jika ada lebih dari satu, ambil yang prioritasnya tertinggi. */
    public function primaryRole(): ?Role
    {
        foreach (Role::cases() as $role) {
            if ($this->hasRole($role->value)) {
                return $role;
            }
        }

        return null;
    }

    public function dashboardRoute(): string
    {
        return $this->primaryRole()?->dashboardRoute() ?? 'profile.edit';
    }
}