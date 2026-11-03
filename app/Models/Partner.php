<?php
// app/Models/Partner.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Partner extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'country_id', 'description', 'website', 'contact_email', 'contact_phone'];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function programs(): HasMany
    {
        return $this->hasMany(Program::class);
    }
}
