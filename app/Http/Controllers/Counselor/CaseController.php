<?php
// app/Http/Controllers/Counselor/CaseController.php

namespace App\Http\Controllers\Counselor;

use App\Enums\CounselingStatus;
use App\Enums\Role;
use App\Enums\SessionMode;
use App\Enums\Urgency;
use App\Http\Controllers\Controller;
use App\Models\CounselingCase;
use App\Models\User;
use App\Notifications\InAppNotification;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

// Kasus milik konselor: catatan sesi terstruktur, alih tangan, dan penutupan. Setiap akses tercatat.
class CaseController extends Controller
{
    public function index(Request $request): View
    {
        $cases = CounselingCase::where('counselor_id', $request->user()->id)
            ->with(['santri.santriProfile', 'pendingHandover'])
            ->withCount('sessions')
            ->latest('updated_at')
            ->get();

        return view('counselor.cases.index', [
            'open' => $cases->filter->isOpen(),
            'closed' => $cases->reject->isOpen(),
        ]);
    }

    public function show(Request $request, CounselingCase $case): View
    {
        Gate::authorize('view', $case);
        AuditLogger::record('case.view', $case, $case->santri_id);

        return view('counselor.cases.show', [
            'case' => $case->load(['santri.santriProfile', 'sessions.counselor', 'request', 'pendingHandover.to']),
            'modes' => SessionMode::cases(),
            'urgencies' => Urgency::cases(),
            'counselors' => User::role(Role::Counselor->value)->where('is_active', true)->whereKeyNot($request->user()->id)->orderBy('name')->get(),
            'openedAt' => now(),
        ]);
    }

    public function storeSession(Request $request, CounselingCase $case): RedirectResponse
    {
        Gate::authorize('update', $case);

        $data = $request->validate([
            'held_at' => ['required', 'date', 'before_or_equal:now'],
            'mode' => ['required', Rule::enum(SessionMode::class)],
            'chief_complaint' => ['required', 'string', 'max:5000'],
            'assessment' => ['required', 'string', 'max:5000'],
            'intervention' => ['required', 'string', 'max:5000'],
            'follow_up_plan' => ['required', 'string', 'max:5000'],
            'urgency' => ['required', Rule::enum(Urgency::class)],
            'next_session_at' => ['nullable', 'date', 'after:now'],
        ]);

        $session = $case->sessions()->create([...$data, 'counselor_id' => $request->user()->id]);
        $case->update(['urgency' => $data['urgency']]);
        AuditLogger::record('session.create', $session, $case->santri_id);

        if ($session->next_session_at) {
            $case->santri->notify(new InAppNotification(
                title: 'Sesi konseling berikutnya',
                body: $session->next_session_at->translatedFormat('l, j F Y H.i').' WIB bersama '.$request->user()->name.'.',
                url: route('counseling.index'),
                icon: 'calendar-check',
            ));
        }

        return back()->with('success', 'Catatan sesi tersimpan terenkripsi.');
    }

    public function close(Request $request, CounselingCase $case): RedirectResponse
    {
        Gate::authorize('update', $case);

        $data = $request->validate(['closing_summary' => ['required', 'string', 'max:5000']]);
        $case->update([...$data, 'status' => CounselingStatus::Closed, 'closed_at' => now()]);
        AuditLogger::record('case.close', $case, $case->santri_id);

        $case->santri->notify(new InAppNotification(
            title: 'Pendampingan konseling selesai',
            body: 'Semoga Allah mudahkan langkahmu. Kamu selalu boleh mengajukan konseling lagi.',
            url: route('counseling.index'),
            icon: 'heart-handshake',
        ));

        return redirect()->route('counselor.cases.index')->with('success', "Kasus {$case->code} ditutup.");
    }

    public function handover(Request $request, CounselingCase $case): RedirectResponse
    {
        Gate::authorize('update', $case);
        if ($case->pendingHandover()->exists()) {
            return back()->with('warning', 'Masih ada permintaan alih tangan yang menunggu jawaban.');
        }

        $data = $request->validate([
            'to_counselor_id' => ['required', 'integer', Rule::exists('users', 'id'), Rule::notIn([$request->user()->id])],
            'reason' => ['required', 'string', 'max:2000'],
        ]);
        $to = User::findOrFail($data['to_counselor_id']);
        abort_unless($to->hasRole(Role::Counselor->value) && $to->is_active, 422);

        $handover = $case->handovers()->create([...$data, 'from_counselor_id' => $request->user()->id, 'status' => CounselingStatus::Pending]);
        AuditLogger::record('handover.request', $handover, $case->santri_id);

        $to->notify(new InAppNotification(
            title: 'Permintaan alih tangan kasus '.$case->code,
            body: 'Dari '.$request->user()->name.'. Buka untuk menerima atau menolak.',
            url: route('counselor.handovers.index'),
            icon: 'arrow-left-right',
        ));

        return back()->with('success', 'Permintaan alih tangan dikirim. Kamu tetap memegang kasus ini sampai diterima.');
    }
}
