<?php
// app/Models/Major.php

namespace App\Models;

use App\Enums\Pathway;
use Illuminate\Database\Eloquent\Model;

class Major extends Model
{
    protected $fillable = ['name', 'pathway', 'description', 'career_prospects'];

    protected function casts(): array
    {
        return ['pathway' => Pathway::class];
    }
}
