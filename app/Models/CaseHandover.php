<?php
// app/Models/CaseHandover.php

namespace App\Models;

use App\Enums\CounselingStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CaseHandover extends Model
{
    protected $fillable = ['counseling_case_id', 'from_counselor_id', 'to_counselor_id', 'reason', 'status', 'responded_at'];

    protected $hidden = ['reason'];

    protected function casts(): array
    {
        return [
            'reason' => 'encrypted',
            'status' => CounselingStatus::class,
            'responded_at' => 'datetime',
        ];
    }

    public function case(): BelongsTo
    {
        return $this->belongsTo(CounselingCase::class, 'counseling_case_id');
    }

    public function from(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_counselor_id');
    }

    public function to(): BelongsTo
    {
        return $this->belongsTo(User::class, 'to_counselor_id');
    }
}
