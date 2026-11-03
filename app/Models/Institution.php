<?php
// app/Models/Institution.php

namespace App\Models;

use App\Enums\InstitutionType;
use App\Enums\LinkStatus;
use Database\Factories\InstitutionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Institution extends Model
{
    /** @use HasFactory<InstitutionFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'country_id',
        'city',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'type' => InstitutionType::class,
        ];
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function links(): HasMany
    {
        return $this->hasMany(OfficialLink::class);
    }

    public function verifiedLinks(): HasMany
    {
        return $this->links()->where('status', LinkStatus::Verified);
    }
}
