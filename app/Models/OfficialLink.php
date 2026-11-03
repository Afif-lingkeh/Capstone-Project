<?php
// app/Models/OfficialLink.php

namespace App\Models;

use App\Enums\LinkCheckResult;
use App\Enums\LinkStatus;
use Database\Factories\OfficialLinkFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class OfficialLink extends Model
{
    /** @use HasFactory<OfficialLinkFactory> */
    use HasFactory;

    // Hanya data isian form. Status & data verifikasi diubah lewat forceFill()
    // di controller/service, sehingga tidak bisa "disisipkan" lewat request.
    protected $fillable = [
        'institution_id',
        'label',
        'url',
    ];

    protected function casts(): array
    {
        return [
            'status' => LinkStatus::class,
            'last_check_result' => LinkCheckResult::class,
            'verified_at' => 'datetime',
            'last_checked_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (OfficialLink $link) {
            if (! $link->isDirty('url')) {
                return;
            }

            $link->domain = static::domainFromUrl($link->url);

            // URL berubah = tautan baru. Verifikasi lama tidak berlaku lagi,
            // jadi tautan disembunyikan dari santri sampai diverifikasi ulang.
            if ($link->exists) {
                $link->status = LinkStatus::Pending;
                $link->verification_source = null;
                $link->verified_by = null;
                $link->verified_at = null;
                $link->last_checked_at = null;
                $link->last_check_result = null;
            }
        });
    }

    /** "https://www.UI.ac.id/pmb" → "ui.ac.id" */
    public static function domainFromUrl(string $url): string
    {
        $host = Str::lower((string) parse_url($url, PHP_URL_HOST));

        return Str::startsWith($host, 'www.') ? Str::after($host, 'www.') : $host;
    }

    public function scopeVerified(Builder $query): void
    {
        $query->where('status', LinkStatus::Verified);
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function checkLogs(): HasMany
    {
        return $this->hasMany(LinkCheckLog::class);
    }

    public function usesHttps(): bool
    {
        return Str::startsWith(Str::lower($this->url), 'https://');
    }
}
