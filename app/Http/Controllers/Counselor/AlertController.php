<?php
// app/Http/Controllers/Counselor/AlertController.php

namespace App\Http\Controllers\Counselor;

use App\Enums\CounselingStatus;
use App\Http\Controllers\Controller;
use App\Models\CounselingRequest;
use App\Models\CounselorShift;
use App\Models\CrisisAlert;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AlertController extends Controller
{
    public function index(): View
    {
        $alerts = CrisisAlert::with(['santri.santriProfile', 'acknowledger'])->latest()->limit(50)->get();

        return view('counselor.alerts.index', [
            'open' => $alerts->where('status', 'open'),
            'handled' => $alerts->where('status', 'acknowledged'),
            // Permintaan krisis yang belum diambil siapa pun, untuk tombol "Ambil permintaan".
            'pendingRequestIds' => CounselingRequest::where('status', CounselingStatus::Pending)->where('is_crisis', true)->pluck('id')->all(),
        ]);
    }

    public function acknowledge(Request $request, CrisisAlert $alert): RedirectResponse
    {
        abort_unless($alert->status === 'open', 409);

        $alert->update(['status' => 'acknowledged', 'acknowledged_by' => $request->user()->id, 'acknowledged_at' => now()]);
        AuditLogger::record('alert.acknowledge', $alert, $alert->santri_id);

        return back()->with('success', 'Peringatan ditandai sedang ditangani oleh Anda.');
    }

    public function shifts(Request $request): View
    {
        return view('counselor.shifts.index', [
            'mine' => CounselorShift::where('counselor_id', $request->user()->id)->where('ends_at', '>', now())->orderBy('starts_at')->get(),
            'onDuty' => CounselorShift::onDuty()->with('counselor')->get(),
        ]);
    }
}
