<?php
// app/Http/Controllers/ConsultationController.php

namespace App\Http\Controllers;

use App\Models\AlumniProfile;
use App\Models\Consultation;
use App\Models\User;
use App\Notifications\InAppNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

// Konsultasi santri ↔ alumni. Hanya kedua pihak (dan Admin Pesantren lewat moderasi) yang dapat membaca utasnya.
class ConsultationController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $column = $user->hasRole('alumni') ? 'alumni_id' : 'santri_id';

        $consultations = Consultation::where($column, $user->id)
            ->with(['santri.santriProfile', 'alumni.alumniProfile', 'messages' => fn ($q) => $q->latest()->limit(1)])
            ->latest('updated_at')
            ->get();

        return view('consultations.index', [
            'pending' => $consultations->where('status', 'pending'),
            'active' => $consultations->where('status', 'accepted'),
            'past' => $consultations->whereIn('status', ['declined', 'closed']),
            'isAlumni' => $user->hasRole('alumni'),
        ]);
    }

    public function store(Request $request, AlumniProfile $alumniProfile): RedirectResponse
    {
        $santri = $request->user();
        abort_unless($santri->hasRole('santri') && $alumniProfile->isVerified() && $alumniProfile->accepts_consultations, 403);

        $data = $request->validate([
            'subject' => ['required', 'string', 'max:150'],
            'body' => ['required', 'string', 'min:10', 'max:2000'],
        ]);

        $open = Consultation::where('santri_id', $santri->id)->where('alumni_id', $alumniProfile->user_id)->whereIn('status', ['pending', 'accepted'])->first();
        if ($open) {
            return redirect()->route('consultations.show', $open)->with('warning', 'Kamu masih memiliki konsultasi yang berjalan dengan alumni ini.');
        }

        $consultation = DB::transaction(function () use ($santri, $alumniProfile, $data) {
            $c = Consultation::create(['santri_id' => $santri->id, 'alumni_id' => $alumniProfile->user_id, 'subject' => $data['subject'], 'status' => 'pending', 'last_message_at' => now()]);
            $c->messages()->create(['sender_id' => $santri->id, 'body' => $data['body']]);

            return $c;
        });

        $alumniProfile->user->notify(new InAppNotification(
            title: 'Permintaan konsultasi: '.$data['subject'],
            body: 'Dari santri '.($santri->santriProfile ? $santri->santriProfile->level->label().' kelas '.$santri->santriProfile->class_name : '').'.',
            url: route('consultations.show', $consultation),
            icon: 'message-circle',
        ));

        return redirect()->route('consultations.show', $consultation)->with('success', 'Pertanyaanmu terkirim. Alumni akan menjawab setelah menerima permintaanmu.');
    }

    public function show(Request $request, Consultation $consultation): View
    {
        abort_unless($consultation->involves($request->user()), 403);

        return view('consultations.show', [
            'consultation' => $consultation->load(['messages.sender', 'santri.santriProfile', 'alumni.alumniProfile', 'reports']),
            'isAlumni' => $request->user()->id === $consultation->alumni_id,
        ]);
    }

    public function message(Request $request, Consultation $consultation): RedirectResponse
    {
        $user = $request->user();
        abort_unless($consultation->involves($user) && $consultation->status === 'accepted', 403);

        $data = $request->validate(['body' => ['required', 'string', 'max:2000']]);
        $consultation->messages()->create(['sender_id' => $user->id, 'body' => $data['body']]);
        $consultation->update(['last_message_at' => now()]);

        $consultation->otherParty($user)->notify(new InAppNotification(
            title: 'Pesan baru: '.$consultation->subject,
            url: route('consultations.show', $consultation),
            icon: 'message-circle',
        ));

        return back();
    }

    public function respond(Request $request, Consultation $consultation): RedirectResponse
    {
        abort_unless($request->user()->id === $consultation->alumni_id && $consultation->status === 'pending', 403);

        $data = $request->validate(['decision' => ['required', Rule::in(['accepted', 'declined'])]]);
        $consultation->update(['status' => $data['decision']]);

        $consultation->santri->notify(new InAppNotification(
            title: $data['decision'] === 'accepted' ? 'Alumni menerima permintaan konsultasimu' : 'Konsultasi belum bisa diterima',
            body: $data['decision'] === 'accepted' ? 'Silakan lanjutkan percakapan.' : 'Silakan ajukan pertanyaan kepada alumni lain.',
            url: route('consultations.show', $consultation),
            icon: 'message-circle',
        ));

        return back()->with('success', $data['decision'] === 'accepted' ? 'Konsultasi diterima.' : 'Konsultasi ditolak.');
    }

    public function close(Request $request, Consultation $consultation): RedirectResponse
    {
        abort_unless($consultation->involves($request->user()) && $consultation->status === 'accepted', 403);
        $consultation->update(['status' => 'closed']);

        return back()->with('success', 'Konsultasi ditandai selesai. Jazakallahu khairan.');
    }

    public function report(Request $request, Consultation $consultation): RedirectResponse
    {
        abort_unless($consultation->involves($request->user()), 403);

        $data = $request->validate(['reason' => ['required', 'string', 'max:1000']]);
        $consultation->reports()->create([...$data, 'reporter_id' => $request->user()->id]);

        Notification::send(
            User::permission('consultations.moderate')->where('is_active', true)->get(),
            new InAppNotification(title: 'Laporan konsultasi', body: 'Ada laporan pada utas "'.$consultation->subject.'".', url: route('admin.consultations.show', $consultation), icon: 'flag', tone: 'danger'),
        );

        return back()->with('success', 'Laporan terkirim ke Admin Pesantren. Terima kasih sudah menjaga keamanan bersama.');
    }
}
