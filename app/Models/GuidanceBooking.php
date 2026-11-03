<?php
// app/Models/GuidanceBooking.php

namespace App\Models;

use App\Enums\BookingStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuidanceBooking extends Model
{
    protected $fillable = ['slot_id', 'santri_id', 'topic', 'message', 'status', 'cancelled_at', 'outcome_notes', 'next_steps'];

    protected function casts(): array
    {
        return [
            'status' => BookingStatus::class,
            'cancelled_at' => 'datetime',
        ];
    }

    public function slot(): BelongsTo
    {
        return $this->belongsTo(GuidanceSlot::class, 'slot_id');
    }

    public function santri(): BelongsTo
    {
        return $this->belongsTo(User::class, 'santri_id');
    }

    public function isUpcoming(): bool
    {
        return $this->status === BookingStatus::Booked && $this->slot->starts_at->isFuture();
    }
}
