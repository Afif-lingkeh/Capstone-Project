<?php
// app/Http/Controllers/Counselor/RequestController.php

namespace App\Http\Controllers\Counselor;

use App\Enums\CounselingStatus;
use App\Enums\Urgency;
use App\Http\Controllers\Controller;
use App\Models\CounselingCase;
use App\Models\CounselingRequest;
use App\Notifications\InAppNotification;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

// Antrean permintaan konseling. Krisis paling atas, lalu santri sejenis kelamin (prioritas, tidak dikunci).
class RequestController extends Controller
{
    public function index(Request $request): View
    {
        $me = $request->user();

        $pending = CounselingRequest::where('status', CounselingStatus::Pending)
            ->with('santri.santriProfile')
            ->oldest()
            ->get()
            ->sortBy(fn (CounselingRequest $r) => [
                $r->is_crisis ? 0 : 1,
                $r->urgency === Urgency::High ? 0 : 1,
                $r->santri->gender === $me->gender ? 0 : 1,
                $r->created_at->timestamp,
            ]);

        return view('counselor.requests.index', ['requests' => $pending]);
    }

    public function show(CounselingRequest $counselingRequest): View
    {
        abort_unless($counselingRequest->status === CounselingStatus::Pending, 404);
        AuditLogger::record('request.view', $counselingRequest, $counselingRequest->santri_id);

        return view('counselor.requests.show', ['counselingRequest' => $counselingRequest->load('santri.santriProfile')]);
    }

    public function accept(Request $request, CounselingRequest $counselingRequest): RedirectResponse
    {
        $case = DB::transaction(function () use ($request, $counselingRequest) {
            $locked = CounselingRequest::whereKey($counselingRequest->id)->lockForUpdate()->first();
            if ($locked->status !== CounselingStatus::Pending) {
                return null;
            }

            $case = CounselingCase::create([
                'santri_id' => $locked->santri_id,
                'counselor_id' => $request->user()->id,
                'status' => CounselingStatus::Open,
                'urgency' => $locked->is_crisis ? Urgency::Crisis : $locked->urgency,
            ]);
            $locked->update(['status' => CounselingStatus::Accepted, 'counseling_case_id' => $case->id, 'accepted_at' => now()]);

            return $case;
        });

        if (! $case) {
            return redirect()->route('counselor.requests.index')->with('warning', 'Permintaan ini sudah diambil konselor lain.');
        }

        AuditLogger::record('request.accept', $case, $case->santri_id);
        $case->santri->notify(new InAppNotification(
            title: 'Permintaan konselingmu diterima',
            body: 'Konselor '.$request->user()->name.' akan mendampingimu.',
            url: route('counseling.index'),
            icon: 'heart-handshake',
        ));

        return redirect()->route('counselor.cases.show', $case)->with('success', "Kasus {$case->code} dibuka. Hubungi santri untuk menjadwalkan sesi pertama.");
    }
}
