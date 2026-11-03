<?php
// app/Http/Controllers/Advisor/SlotController.php

namespace App\Http\Controllers\Advisor;

use App\Enums\SessionMode;
use App\Http\Controllers\Controller;
use App\Models\GuidanceSlot;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

// Pembimbing membuka slot jadwal bimbingan (daring/luring), bisa diulang mingguan.
class SlotController extends Controller
{
    public function index(Request $request): View
    {
        return view('advisor.slots.index', [
            'slots' => $request->user()->guidanceSlots()
                ->where('ends_at', '>', now())
                ->with('activeBooking.santri')
                ->orderBy('starts_at')
                ->get()
                ->groupBy(fn (GuidanceSlot $slot) => $slot->starts_at->toDateString()),
            'modes' => SessionMode::cases(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'duration' => ['required', 'integer', Rule::in([30, 45, 60, 90])],
            'count' => ['required', 'integer', 'min:1', 'max:8'],       // jumlah slot berurutan di hari itu
            'repeat_weeks' => ['required', 'integer', 'min:0', 'max:8'], // ulangi di hari yang sama minggu berikutnya
            'mode' => ['required', Rule::enum(SessionMode::class)],
            'location' => ['nullable', 'string', 'max:255'],
        ]);

        $created = 0;
        $skipped = 0;
        $advisor = $request->user();

        for ($week = 0; $week <= $data['repeat_weeks']; $week++) {
            $start = Carbon::parse($data['date'].' '.$data['start_time'])->addWeeks($week);

            for ($i = 0; $i < $data['count']; $i++) {
                $end = $start->copy()->addMinutes($data['duration']);

                // Lewati waktu yang sudah lewat atau bertabrakan dengan slot lain milik pembimbing ini.
                $clash = $advisor->guidanceSlots()->where('starts_at', '<', $end)->where('ends_at', '>', $start)->exists();
                if ($start->isPast() || $clash) {
                    $skipped++;
                } else {
                    $advisor->guidanceSlots()->create([
                        'starts_at' => $start, 'ends_at' => $end,
                        'mode' => $data['mode'], 'location' => $data['location'] ?? null,
                    ]);
                    $created++;
                }

                $start = $end;
            }
        }

        return back()->with($created ? 'success' : 'warning', "{$created} slot dibuka".($skipped ? ", {$skipped} dilewati karena bertabrakan atau sudah lewat." : '.'));
    }

    public function destroy(Request $request, GuidanceSlot $slot): RedirectResponse
    {
        abort_unless($slot->advisor_id === $request->user()->id, 403);

        if ($slot->activeBooking()->exists()) {
            return back()->with('error', 'Slot yang sudah dipesan tidak dapat dihapus. Hubungi santrinya untuk menjadwal ulang.');
        }

        $slot->delete();

        return back()->with('success', 'Slot dihapus.');
    }
}
