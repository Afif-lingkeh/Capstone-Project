<?php
// app/Models/Question.php

namespace App\Models;

use App\Enums\Pathway;
use App\Enums\QuestionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    protected $fillable = ['potential_test_id', 'body', 'type', 'pathway', 'position'];

    protected function casts(): array
    {
        return [
            'type' => QuestionType::class,
            'pathway' => Pathway::class,
        ];
    }

    public function test(): BelongsTo
    {
        return $this->belongsTo(PotentialTest::class, 'potential_test_id');
    }

    public function options(): HasMany
    {
        return $this->hasMany(QuestionOption::class)->orderBy('position')->orderBy('id');
    }
}
