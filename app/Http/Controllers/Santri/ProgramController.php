<?php
// app/Http/Controllers/Santri/ProgramController.php

namespace App\Http\Controllers\Santri;

use App\Enums\ProgramStatus;
use App\Enums\ProgramType;
use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Partner;
use App\Models\Program;
use App\Models\User;
use App\Notifications\InAppNotification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;

class ProgramController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('browse', Program::class);

        $user = $request->user();
        $status = ProgramStatus::tryFrom((string) $request->query('status'));

        $programs = Program::query()
            ->published()
            ->with(['country', 'partner'])
            ->when($request->integer('country'), fn (Builder $q, int $id) => $q->where('country_id', $id))
            ->when($request->integer('partner'), fn (Builder $q, int $id) => $q->where('partner_id', $id))
            ->when(ProgramType::tryFrom((string) $request->query('type')), fn (Builder $q, ProgramType $type) => $q->where('type', $type))
            ->when($status, fn (Builder $q, ProgramStatus $status) => $q->where('status', $status))
            ->when($request->boolean('saved'), fn (Builder $q) => $q->whereHas('bookmarkedBy', fn (Builder $q) => $q->whereKey($user->id)))
            ->when($request->filled('q'), fn (Builder $q) => $q->where('title', 'like', '%'.$request->string('q')->trim().'%'))
            ->orderByRaw('status = ? desc', [ProgramStatus::Open->value])
            ->orderByRaw('deadline is null, deadline')
            ->paginate(9)
            ->withQueryString();

        return view('programs.index', [
            'programs' => $programs,
            'countries' => Country::whereHas('programs', fn (Builder $q) => $q->published())->orderBy('name')->get(),
            'partners' => Partner::whereHas('programs', fn (Builder $q) => $q->published())->orderBy('name')->get(),
            'bookmarkedIds' => $user->bookmarkedPrograms()->pluck('programs.id')->all(),
            'interestIds' => $user->programInterests()->pluck('program_id')->all(),
        ]);
    }

    public function show(Request $request, Program $program): View
    {
        Gate::authorize('view', $program);

        $program->load(['country', 'partner.country']);

        return view('programs.show', [
            'program' => $program,
            'bookmarked' => $request->user()->bookmarkedPrograms()->whereKey($program->id)->exists(),
            'interest' => $request->user()->programInterests()->where('program_id', $program->id)->first(),
        ]);
    }

    public function bookmark(Request $request, Program $program): RedirectResponse
    {
        Gate::authorize('view', $program);

        $result = $request->user()->bookmarkedPrograms()->toggle($program->id);

        return back()->with('success', $result['attached']
            ? 'Program disimpan. Lihat lagi lewat filter "Disimpan".'
            : 'Program dihapus dari simpanan.');
    }

    public function interest(Request $request, Program $program): RedirectResponse
    {
        Gate::authorize('expressInterest', $program);

        if (! $program->acceptsInterest()) {
            return back()->with('warning', 'Pendaftaran minat untuk program ini sudah ditutup.');
        }

        $data = $request->validate(['note' => ['nullable', 'string', 'max:500']]);

        $interest = $request->user()->programInterests()->firstOrCreate(
            ['program_id' => $program->id],
            ['note' => $data['note'] ?? null],
        );

        if ($interest->wasRecentlyCreated) {
            // Admin pesantren menindaklanjuti peminat; santri tidak dihubungi langsung oleh pihak luar.
            Notification::send(
                User::permission('programs.view-interests')->where('is_active', true)->get(),
                new InAppNotification(
                    title: 'Peminat baru: '.$program->title,
                    body: $request->user()->name.' menyatakan berminat.',
                    url: route('admin.programs.interests', $program),
                    icon: 'briefcase-business',
                ),
            );
        }

        return back()->with('success', 'Terima kasih! Admin pesantren akan menghubungimu untuk langkah berikutnya, insyaallah.');
    }

    public function withdraw(Request $request, Program $program): RedirectResponse
    {
        $request->user()->programInterests()->where('program_id', $program->id)->delete();

        return back()->with('success', 'Minatmu pada program ini dibatalkan.');
    }
}
