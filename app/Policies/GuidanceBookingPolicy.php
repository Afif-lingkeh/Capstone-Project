<?php
// app/Policies/GuidanceBookingPolicy.php

namespace App\Policies;

use App\Enums\BookingStatus;
use App\Models\GuidanceBooking;
use App\Models\User;

class GuidanceBookingPolicy
{
    // Santri hanya bisa membatalkan pesanannya sendiri yang belum berlangsung.
    public function cancel(User $user, GuidanceBooking $booking): bool
    {
        return $booking->santri_id === $user->id
            && $booking->status === BookingStatus::Booked
            && $booking->slot->starts_at->isFuture();
    }

    // Hanya pembimbing pemilik slot yang mencatat hasil bimbingan.
    public function record(User $user, GuidanceBooking $booking): bool
    {
        return $booking->slot->advisor_id === $user->id
            && $user->checkPermissionTo('guidance.manage-slots')
            && $booking->status !== BookingStatus::Cancelled;
    }
}
