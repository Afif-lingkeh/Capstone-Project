<?php
// app/Models/SantriProfile.php

namespace App\Models;

use App\Enums\SantriLevel;
use Database\Factories\SantriProfileFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SantriProfile extends Model
{
    /** @use HasFactory<SantriProfileFactory> */
    use HasFactory;

    protected $fillable = [
        'nis',
        'level',
        'class_name',
        'entry_year',
        'guardian_name',
    ];

    protected function casts(): array
    {
        return [
            'level' => SantriLevel::class,
            'entry_year' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}