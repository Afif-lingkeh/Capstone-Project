<?php
// app/Models/LinkCheckLog.php

namespace App\Models;

use App\Enums\LinkCheckResult;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LinkCheckLog extends Model
{
    // Log hanya ditulis sekali; waktunya sudah dicatat di kolom checked_at.
    public $timestamps = false;

    protected $fillable = [
        'checked_at',
        'http_status',
        'final_url',
        'result',
        'response_ms',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'checked_at' => 'datetime',
            'result' => LinkCheckResult::class,
        ];
    }

    public function link(): BelongsTo
    {
        return $this->belongsTo(OfficialLink::class, 'official_link_id');
    }
}
