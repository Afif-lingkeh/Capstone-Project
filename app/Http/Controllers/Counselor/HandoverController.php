<?php
// app/Http/Controllers/Counselor/HandoverController.php

namespace App\Http\Controllers\Counselor;

use App\Enums\CounselingStatus;
use App\Http\Controllers\Controller;
use App\Models\CaseHandover;
use App\Notifications\InAppNotification;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class HandoverController extends Controller
{
    public function index(Request $request): View
    {
        $me = $request->user()->id;

        return view('counselor.handovers.index', [
            'incoming' => CaseHandover::where('to_counselor_id', $me)->where('status', CounselingStatus::Pending)->with(['case.santri', 'from'])->latest()->get(),
            'outgoing' => CaseHandover::where('from_counselor_id', $me)->with(['case', 'to'])->latest()->limit(20)->get(),
        ]);
    }

    // Diterima: kasus pindah ke konselor baru, konselor lama tidak bisa membaca kasus lagi.
    public function accept(Request $request, CaseHandover $handover): RedirectResponse
    {
        $this->authorizeRecipient($request, $handover);

        DB::transaction(function () use ($handover) {
            $handover->update(['status' => CounselingStatus::Accepted, 'responded_at' => now()]);
            $handover->case->update(['counselor_id' => $handover->to_counselor_id]);
        });
        AuditLogger::record('handover.accept', $handover, $handover->case->santri_id);

        $handover->from?->notify(new InAppNotification(
            title: 'Alih tangan '.$handover->case->code.' diterima',
            body: $request->user()->name.' kini mendampingi kasus ini. Akses Anda ke catatannya sudah ditutup.',
            url: route('counselor.handovers.index'),
            icon: 'arrow-left-right',
        ));

        return redirect()->route('counselor.cases.show', $handover->case)->with('success', 'Kasus kini Anda pegang.');
    }

    public function reject(Request $request, CaseHandover $handover): RedirectResponse
    {
        $this->authorizeRecipient($request, $handover);

        $handover->update(['status' => CounselingStatus::Rejected, 'responded_at' => now()]);
        AuditLogger::record('handover.reject', $handover, $handover->case->santri_id);

        $handover->from?->notify(new InAppNotification(
            title: 'Alih tangan '.$handover->case->code.' ditolak',
            body: $request->user()->name.' belum dapat menerima kasus ini.',
            url: route('counselor.cases.show', $handover->case),
            icon: 'arrow-left-right',
        ));

        return back()->with('success', 'Permintaan alih tangan ditolak.');
    }

    private function authorizeRecipient(Request $request, CaseHandover $handover): void
    {
        abort_unless($handover->to_counselor_id === $request->user()->id && $handover->status === CounselingStatus::Pending, 403);
    }
}
