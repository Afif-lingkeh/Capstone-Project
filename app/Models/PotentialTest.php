<?php
// app/Models/PotentialTest.php

namespace App\Models;

use App\Enums\TestDimension;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class PotentialTest extends Model
{
    protected $fillable = ['title', 'dimension', 'description', 'estimated_minutes', 'is_active'];

    protected function casts(): array
    {
        return [
            'dimension' => TestDimension::class,
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (PotentialTest $test) {
            $test->slug ??= Str::slug($test->title).'-'.Str::lower(Str::random(4));
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->orderBy('position')->orderBy('id');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(TestAttempt::class);
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }
}
