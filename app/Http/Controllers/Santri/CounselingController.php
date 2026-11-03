<?php
// app/Http/Controllers/Santri/CounselingController.php

namespace App\Http\Controllers\Santri;

use App\Enums\CounselingStatus;
use App\Enums\CounselingTopic;
use App\Enums\Role;
use App\Enums\Urgency;
use App\Http\Controllers\Controller;
use App\Models\CounselingRequest;
use App\Models\User;
use App\Notifications\InAppNotification;
use App\Services\CrisisResponder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

// Konseling dari sisi santri: ajukan permintaan dan pantau statusnya. Terkunci sampai persetujuan wali tercatat.
class CounselingController extends Controller
{
    public function __construct(private CrisisResponder $crisis) {}

    public function index(Request $request): View
    {
        $user = $request->user();

        return view('counseling.index', [
            'hasConsent' => $user->hasGuardianConsent(),
            'requests' => $user->counselingRequests()->with(['case.counselor', 'case.sessions'])->latest()->get(),
            'onDuty' => $this->crisis->onDutyNames(),
        ]);
    }

    public function create(Request $request): View|RedirectResponse
    {
        if (! $request->user()->hasGuardianConsent()) {
            return redirect()->route('counseling.index');
        }

        return view('counseling.create', ['topics' => CounselingTopic::cases(), 'urgencies' => Urgency::santriChoices()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user->hasGuardianConsent(), 403, 'Konseling terbuka setelah persetujuan orang tua atau wali tercatat.');

        $data = $request->validate([
            'topic' => ['required', Rule::enum(CounselingTopic::class)],
            'story' => ['required', 'string', 'min:10', 'max:5000'],
            'preferred_time' => ['nullable', 'string', 'max:150'],
            'urgency' => ['required', Rule::in(array_column(Urgency::santriChoices(), 'value'))],
        ], ['story.min' => 'Ceritakan sedikit lebih banyak agar konselor bisa bersiap.']);

        $isCrisis = $this->crisis->detect($data['story']);

        $counselingRequest = $user->counselingRequests()->create([
            ...$data,
            'status' => CounselingStatus::Pending,
            'is_crisis' => $isCrisis,
        ]);

        if ($isCrisis) {
            $this->crisis->raise($user, 'request', $counselingRequest->id);
        } else {
            Notification::send(
                User::role(Role::Counselor->value)->where('is_active', true)->get(),
                new InAppNotification(title: 'Permintaan konseling baru', body: 'Topik: '.$counselingRequest->topic->label(), url: route('counselor.requests.index'), icon: 'inbox'),
            );
        }

        return redirect()->route('counseling.index')
            ->with('success', 'Permintaanmu sudah terkirim. Konselor akan segera menghubungimu, insyaallah.')
            ->with('crisis', $isCrisis);
    }

    public function cancel(Request $request, CounselingRequest $counselingRequest): RedirectResponse
    {
        abort_unless($counselingRequest->santri_id === $request->user()->id && $counselingRequest->status === CounselingStatus::Pending, 403);

        $counselingRequest->update(['status' => CounselingStatus::Cancelled]);

        return back()->with('success', 'Permintaan dibatalkan. Kamu bisa mengajukan lagi kapan saja.');
    }
}
