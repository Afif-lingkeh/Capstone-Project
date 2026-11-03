<?php
// app/Http/Controllers/Admin/ProgramController.php

namespace App\Http\Controllers\Admin;

use App\Enums\ProgramStatus;
use App\Enums\ProgramType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProgramRequest;
use App\Models\Country;
use App\Models\Partner;
use App\Models\Program;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProgramController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Program::class);

        $programs = Program::query()
            ->with(['country', 'partner'])
            ->withCount('interests')
            ->when(ProgramStatus::tryFrom((string) $request->query('status')), fn (Builder $q, ProgramStatus $s) => $q->where('status', $s))
            ->when($request->filled('q'), fn (Builder $q) => $q->where('title', 'like', '%'.$request->string('q')->trim().'%'))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.programs.index', [
            'programs' => $programs,
            'partnerCount' => Partner::count(),
            'stats' => [
                'open' => Program::published()->where('status', ProgramStatus::Open)->count(),
                'draft' => Program::whereNull('published_at')->count(),
                'interests' => \App\Models\ProgramInterest::whereNull('followed_up_at')->count(),
            ],
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', Program::class);

        return view('admin.programs.form', $this->formData(new Program(['status' => ProgramStatus::Open])));
    }

    public function store(ProgramRequest $request): RedirectResponse
    {
        $program = Program::create([...$request->programData(), 'created_by' => $request->user()->id]);

        return redirect()->route('admin.programs.index')->with('success', "Program {$program->title} disimpan.");
    }

    public function edit(Program $program): View
    {
        Gate::authorize('update', $program);

        return view('admin.programs.form', $this->formData($program));
    }

    public function update(ProgramRequest $request, Program $program): RedirectResponse
    {
        $program->update($request->programData($program));

        return redirect()->route('admin.programs.index')->with('success', "Program {$program->title} diperbarui.");
    }

    public function destroy(Program $program): RedirectResponse
    {
        if (Gate::denies('delete', $program)) {
            return back()->with('error', 'Program yang sudah punya peminat tidak dapat dihapus. Ubah statusnya menjadi Tutup.');
        }

        $program->delete();

        return redirect()->route('admin.programs.index')->with('success', 'Program dihapus.');
    }

    // Rekap peminat per program (+ unduh CSV).
    public function interests(Request $request, Program $program): View|StreamedResponse
    {
        Gate::authorize('viewInterests', $program);

        $interests = $program->interests()->with('user.santriProfile')->latest()->get();

        if ($request->query('format') === 'csv') {
            return response()->streamDownload(function () use ($interests) {
                $out = fopen('php://output', 'w');
                fputcsv($out, ['Nama', 'NIS', 'Jenjang', 'Kelas', 'Catatan', 'Tanggal minat', 'Sudah ditindaklanjuti']);
                foreach ($interests as $interest) {
                    $profile = $interest->user->santriProfile;
                    fputcsv($out, [
                        $interest->user->name, $profile?->nis, $profile?->level->label(), $profile?->class_name,
                        $interest->note, $interest->created_at->format('Y-m-d H:i'), $interest->followed_up_at ? 'Ya' : 'Belum',
                    ]);
                }
                fclose($out);
            }, 'peminat-'.$program->slug.'.csv', ['Content-Type' => 'text/csv']);
        }

        return view('admin.programs.interests', ['program' => $program, 'interests' => $interests]);
    }

    public function followUp(Program $program, int $interest): RedirectResponse
    {
        Gate::authorize('viewInterests', $program);

        $record = $program->interests()->findOrFail($interest);
        $record->update(['followed_up_at' => $record->followed_up_at ? null : now()]);

        return back()->with('success', 'Status tindak lanjut diperbarui.');
    }

    private function formData(Program $program): array
    {
        return [
            'program' => $program,
            'partners' => Partner::with('country')->orderBy('name')->get(),
            'countries' => Country::orderBy('name')->get(),
            'types' => ProgramType::cases(),
            'statuses' => ProgramStatus::cases(),
        ];
    }
}
