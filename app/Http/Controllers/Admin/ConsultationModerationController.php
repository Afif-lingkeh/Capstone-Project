<?php
// app/Http/Controllers/Admin/ConsultationModerationController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Consultation;
use App\Models\ConsultationReport;
use App\Notifications\InAppNotification;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

// Moderasi konsultasi alumni (keputusan Fase 1 no. 7): admin bisa membaca, kedua pihak diberi tahu.
class ConsultationModerationController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [new Middleware('permission:consultations.moderate')];
    }

    public function index(): View
    {
        return view('admin.consultations.index', [
            'reports' => ConsultationReport::where('status', 'open')->with(['consultation.santri', 'consultation.alumni', 'reporter'])->latest()->get(),
            'consultations' => Consultation::with(['santri', 'alumni'])->withCount('messages')->latest('updated_at')->paginate(20),
        ]);
    }

    public function show(Request $request, Consultation $consultation): View
    {
        AuditLogger::record('consultation.moderate', $consultation, $consultation->santri_id);

        // Beri tahu kedua pihak, paling sering sekali sehari per utas agar tidak berulang-ulang.
        $key = 'moderation-notified-'.$consultation->id;
        if (! cache()->has($key)) {
            foreach ([$consultation->santri, $consultation->alumni] as $party) {
                $party?->notify(new InAppNotification(
                    title: 'Percakapan ditinjau Admin Pesantren',
                    body: 'Utas "'.$consultation->subject.'" sedang ditinjau untuk menjaga keamanan bersama.',
                    url: route('consultations.show', $consultation),
                    icon: 'shield',
                ));
            }
            cache()->put($key, true, now()->addDay());
        }

        return view('admin.consultations.show', ['consultation' => $consultation->load(['messages.sender', 'santri.santriProfile', 'alumni.alumniProfile', 'reports.reporter'])]);
    }

    public function resolve(Request $request, ConsultationReport $report): RedirectResponse
    {
        $data = $request->validate([
            'resolution_note' => ['required', 'string', 'max:1000'],
            'close_thread' => ['boolean'],
        ]);

        $report->update(['status' => 'resolved', 'resolved_by' => $request->user()->id, 'resolved_at' => now(), 'resolution_note' => $data['resolution_note']]);
        if ($request->boolean('close_thread')) {
            $report->consultation->update(['status' => 'closed']);
        }

        return back()->with('success', 'Laporan ditandai selesai.');
    }
}
