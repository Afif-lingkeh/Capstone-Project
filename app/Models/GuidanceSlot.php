<?php
// app/Models/GuidanceSlot.php

namespace App\Models;

use App\Enums\BookingStatus;
use App\Enums\SessionMode;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class GuidanceSlot extends Model
{
    use HasFactory;

    protected $fillable = ['advisor_id', 'starts_at', 'ends_at', 'mode', 'location'];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'mode' => SessionMode::class,
        ];
    }

    public function advisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'advisor_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(GuidanceBooking::class, 'slot_id');
    }

    /** Pemesanan yang masih berlaku (tidak dibatalkan). */
    public function activeBooking(): HasOne
    {
        return $this->hasOne(GuidanceBooking::class, 'slot_id')->where('status', '!=', BookingStatus::Cancelled);
    }

    /** Slot yang belum lewat dan belum dipesan siapa pun. */
    public function scopeAvailable(Builder $query): void
    {
        $query->where('starts_at', '>', now())->whereDoesntHave('activeBooking');
    }
}
