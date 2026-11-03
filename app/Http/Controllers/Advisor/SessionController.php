<?php
// app/Http/Controllers/Advisor/SessionController.php

namespace App\Http\Controllers\Advisor;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Models\GuidanceBooking;
use App\Notifications\InAppNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

// Riwayat sesi bimbingan dan pencatatan hasilnya.
class SessionController extends Controller
{
    public function index(Request $request): View
    {
        $bookings = GuidanceBooking::query()
            ->whereHas('slot', fn ($q) => $q->where('advisor_id', $request->user()->id))
            ->where('status', '!=', BookingStatus::Cancelled)
            ->with(['slot', 'santri.santriProfile'])
            ->get()
            ->sortByDesc('slot.starts_at');

        return view('advisor.sessions.index', [
            'upcoming' => $bookings->filter(fn ($b) => $b->slot->starts_at->isFuture())->sortBy('slot.starts_at'),
            'toRecord' => $bookings->filter(fn ($b) => $b->slot->starts_at->isPast() && $b->status === BookingStatus::Booked),
            'done' => $bookings->filter(fn ($b) => in_array($b->status, [BookingStatus::Completed, BookingStatus::NoShow], true)),
        ]);
    }

    public function update(Request $request, GuidanceBooking $booking): RedirectResponse
    {
        Gate::authorize('record', $booking);

        $data = $request->validate([
            'status' => ['required', Rule::in([BookingStatus::Completed->value, BookingStatus::NoShow->value])],
            'outcome_notes' => ['nullable', 'required_if:status,completed', 'string', 'max:5000'],
            'next_steps' => ['nullable', 'string', 'max:2000'],
        ]);

        $booking->update($data);

        if ($booking->status === BookingStatus::Completed) {
            $booking->santri->notify(new InAppNotification(
                title: 'Catatan bimbingan tersedia',
                body: 'Pembimbing sudah menuliskan hasil sesi "'.$booking->topic.'".',
                url: route('guidance.index'),
                icon: 'calendar-check',
            ));
        }

        return back()->with('success', 'Hasil sesi tersimpan.');
    }
}
