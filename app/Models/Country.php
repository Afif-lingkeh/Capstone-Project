<?php
// app/Models/Country.php

namespace App\Models;

use Database\Factories\CountryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends Model
{
    /** @use HasFactory<CountryFactory> */
    use HasFactory;

    protected $fillable = ['name', 'iso2'];

    /** Emoji bendera dari kode ISO2 (mis. JP → 🇯🇵). */
    public function flag(): string
    {
        return implode('', array_map(fn ($c) => mb_chr(0x1F1E6 + ord($c) - 65), str_split(strtoupper($this->iso2))));
    }

    public function programs(): HasMany
    {
        return $this->hasMany(Program::class);
    }

    public function institutions(): HasMany
    {
        return $this->hasMany(Institution::class);
    }
}
