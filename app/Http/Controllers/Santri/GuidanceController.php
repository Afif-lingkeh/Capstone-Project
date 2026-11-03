<?php
// app/Http/Controllers/Santri/GuidanceController.php

namespace App\Http\Controllers\Santri;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Models\GuidanceBooking;
use App\Models\GuidanceSlot;
use App\Notifications\InAppNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

// Bimbingan kuliah dari sisi santri: lihat slot kosong, pesan, batalkan, dan riwayat.
class GuidanceController extends Controller
{
    /** Batas pesanan aktif agar slot terbagi merata. */
    public const MAX_UPCOMING = 2;

    public function index(Request $request): View
    {
        $user = $request->user();
        $myAdvisorIds = $user->advisors()->pluck('users.id');

        // Pembimbing santri sendiri ditampilkan lebih dulu, lalu yang sejenis kelamin (prioritas, bukan kunci).
        $slots = GuidanceSlot::query()
            ->available()
            ->where('starts_at', '<=', now()->addWeeks(4))
            ->with('advisor')
            ->orderBy('starts_at')
            ->get()
            ->sortBy(fn (GuidanceSlot $slot) => [
                $myAdvisorIds->contains($slot->advisor_id) ? 0 : 1,
                $slot->advisor->gender === $user->gender ? 0 : 1,
                $slot->starts_at->timestamp,
            ])
            ->groupBy(fn (GuidanceSlot $slot) => $slot->starts_at->toDateString())
            ->sortKeys();

        $bookings = $user->guidanceBookings()->with('slot.advisor')->latest()->get();

        return view('guidance.index', [
            'slotsByDate' => $slots,
            'upcoming' => $bookings->filter->isUpcoming()->sortBy('slot.starts_at'),
            'history' => $bookings->reject->isUpcoming(),
            'myAdvisorIds' => $myAdvisorIds,
            'canBook' => $bookings->filter->isUpcoming()->count() < self::MAX_UPCOMING,
        ]);
    }

    public function book(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'slot_id' => ['required', 'integer', 'exists:guidance_slots,id'],
            'topic' => ['required', 'string', 'max:150'],
            'message' => ['nullable', 'string', 'max:1000'],
        ], ['slot_id.required' => 'Pilih salah satu waktu yang tersedia.']);
        $user = $request->user();
        $slot = GuidanceSlot::with('advisor')->findOrFail($data['slot_id']);
        unset($data['slot_id']);

        $upcoming = $user->guidanceBookings()->where('status', BookingStatus::Booked)
            ->whereHas('slot', fn ($q) => $q->where('starts_at', '>', now()))->count();
        if ($upcoming >= self::MAX_UPCOMING) {
            return back()->with('warning', 'Kamu sudah punya '.self::MAX_UPCOMING.' jadwal yang akan datang. Selesaikan atau batalkan dulu salah satunya.');
        }

        // Kunci baris slot agar dua santri tidak memesan slot yang sama bersamaan.
        $booking = DB::transaction(function () use ($slot, $user, $data) {
            $slot = GuidanceSlot::whereKey($slot->id)->lockForUpdate()->available()->first();

            return $slot?->bookings()->create([...$data, 'santri_id' => $user->id, 'status' => BookingStatus::Booked]);
        });

        if (! $booking) {
            return back()->with('warning', 'Maaf, slot itu baru saja dipesan atau sudah lewat. Pilih waktu lain, ya.');
        }

        $slot->advisor->notify(new InAppNotification(
            title: 'Jadwal bimbingan baru',
            body: $user->name.' memesan '.$slot->starts_at->translatedFormat('l, j F H.i').' · '.$data['topic'],
            url: route('advisor.sessions.index'),
            icon: 'calendar-check',
        ));

        return redirect()->route('guidance.index')->with('success', 'Jadwal dipesan. Sampai jumpa di sesi bimbingan, insyaallah!');
    }

    public function cancel(Request $request, GuidanceBooking $booking): RedirectResponse
    {
        Gate::authorize('cancel', $booking);

        $booking->update(['status' => BookingStatus::Cancelled, 'cancelled_at' => now()]);

        $booking->slot->advisor->notify(new InAppNotification(
            title: 'Jadwal bimbingan dibatalkan',
            body: $request->user()->name.' membatalkan sesi '.$booking->slot->starts_at->translatedFormat('l, j F H.i').'. Slot dibuka kembali.',
            url: route('advisor.slots.index'),
            icon: 'calendar',
        ));

        return back()->with('success', 'Jadwal dibatalkan. Slotnya kini bisa dipesan teman lain.');
    }
}
