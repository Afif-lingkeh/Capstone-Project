<?php
// app/Models/CounselingRequest.php

namespace App\Models;

use App\Enums\CounselingStatus;
use App\Enums\CounselingTopic;
use App\Enums\Urgency;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CounselingRequest extends Model
{
    protected $fillable = ['santri_id', 'topic', 'story', 'preferred_time', 'urgency', 'status', 'is_crisis', 'counseling_case_id', 'accepted_at'];

    protected $hidden = ['story'];

    protected function casts(): array
    {
        return [
            'topic' => CounselingTopic::class,
            'story' => 'encrypted',
            'urgency' => Urgency::class,
            'status' => CounselingStatus::class,
            'is_crisis' => 'boolean',
            'accepted_at' => 'datetime',
        ];
    }

    public function santri(): BelongsTo
    {
        return $this->belongsTo(User::class, 'santri_id');
    }

    public function case(): BelongsTo
    {
        return $this->belongsTo(CounselingCase::class, 'counseling_case_id');
    }
}
