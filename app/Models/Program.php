<?php
// app/Models/Program.php

namespace App\Models;

use App\Enums\ProgramStatus;
use App\Enums\ProgramType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Program extends Model
{
    use HasFactory;

    protected $fillable = [
        'partner_id', 'country_id', 'title', 'type', 'summary', 'requirements', 'costs',
        'min_education', 'deadline', 'official_contact', 'official_url', 'status', 'published_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => ProgramType::class,
            'status' => ProgramStatus::class,
            'deadline' => 'date',
            'published_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Program $program) {
            $program->slug ??= Str::slug($program->title).'-'.Str::lower(Str::random(5));
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function interests(): HasMany
    {
        return $this->hasMany(ProgramInterest::class);
    }

    public function bookmarkedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'program_bookmarks')->withTimestamps();
    }

    /** Hanya program yang sudah dipublikasikan yang boleh dilihat santri. */
    public function scopePublished(Builder $query): void
    {
        $query->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    public function isPublished(): bool
    {
        return $this->published_at !== null && $this->published_at->isPast();
    }

    /** Program "buka" tapi tenggatnya sudah lewat diperlakukan sebagai tutup. */
    public function acceptsInterest(): bool
    {
        return $this->status === ProgramStatus::Open && ($this->deadline === null || ! $this->deadline->endOfDay()->isPast());
    }
}
