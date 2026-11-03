<?php
// app/Models/QuestionOption.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionOption extends Model
{
    protected $fillable = ['question_id', 'label', 'weights', 'position'];

    protected function casts(): array
    {
        return ['weights' => 'array'];
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function weightFor(string $pathway): int
    {
        return (int) ($this->weights[$pathway] ?? 0);
    }
}
