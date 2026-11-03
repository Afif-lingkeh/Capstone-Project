<?php
// app/Models/ProgramInterest.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgramInterest extends Model
{
    protected $fillable = ['program_id', 'user_id', 'note', 'followed_up_at'];

    protected function casts(): array
    {
        return ['followed_up_at' => 'datetime'];
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
