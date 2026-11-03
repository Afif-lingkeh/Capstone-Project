<?php
// app/Http/Controllers/Advisor/DashboardController.php

namespace App\Http\Controllers\Advisor;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Models\GuidanceBooking;
use Illuminate\Http\Request;
use Illuminate\View\View;

// Beranda pembimbing (desain "06 Pembimbing · Beranda"): sesi berikutnya, sesi yang belum dicatat, santri binaan.
class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $me = $request->user();
        $bookings = GuidanceBooking::whereHas('slot', fn ($q) => $q->where('advisor_id', $me->id))
            ->where('status', BookingStatus::Booked)
            ->with(['slot', 'santri.santriProfile'])
            ->get();

        $advisees = $me->advisees()->with('santriProfile')->withCount(['testAttempts as tests_done' => fn ($q) => $q->whereNotNull('submitted_at')])->orderBy('name')->get();

        return view('advisor.dashboard', [
            'advisees' => $advisees,
            'upcoming' => $bookings->filter(fn ($b) => $b->slot->starts_at->isFuture())->sortBy('slot.starts_at')->take(5),
            'toRecord' => $bookings->filter(fn ($b) => $b->slot->starts_at->isPast())->count(),
            'openSlots' => $me->guidanceSlots()->available()->count(),
        ]);
    }
}
