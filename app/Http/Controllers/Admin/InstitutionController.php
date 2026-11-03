<?php
// app/Http/Controllers/Admin/InstitutionController.php

namespace App\Http\Controllers\Admin;

use App\Enums\InstitutionType;
use App\Enums\LinkStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\InstitutionRequest;
use App\Models\Country;
use App\Models\Institution;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class InstitutionController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Institution::class);

        $institutions = Institution::query()
            ->with('country')
            ->withCount([
                'links',
                'links as verified_links_count' => fn (Builder $q) => $q->where('status', LinkStatus::Verified),
            ])
            ->when($request->filled('q'), fn (Builder $q) => $q->where('name', 'like', '%'.$request->string('q')->trim().'%'))
            ->when(InstitutionType::tryFrom((string) $request->query('type')), fn (Builder $q, InstitutionType $type) => $q->where('type', $type))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.institutions.index', [
            'institutions' => $institutions,
            'types' => InstitutionType::cases(),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', Institution::class);

        return view('admin.institutions.create', $this->formData(new Institution));
    }

    public function store(InstitutionRequest $request): RedirectResponse
    {
        $institution = Institution::create($request->validated());

        return redirect()->route('admin.links.create', ['institution' => $institution->id])
            ->with('success', "Institusi {$institution->name} ditambahkan. Sekarang tambahkan tautan resminya.");
    }

    public function edit(Institution $institution): View
    {
        Gate::authorize('update', $institution);

        return view('admin.institutions.edit', $this->formData($institution));
    }

    public function update(InstitutionRequest $request, Institution $institution): RedirectResponse
    {
        $institution->update($request->validated());

        return redirect()->route('admin.institutions.index')
            ->with('success', "Institusi {$institution->name} diperbarui.");
    }

    public function destroy(Institution $institution): RedirectResponse
    {
        Gate::authorize('update', $institution);

        if (Gate::denies('delete', $institution)) {
            return back()->withErrors(['institution' => 'Institusi yang masih memiliki tautan tidak dapat dihapus. Hapus tautannya terlebih dahulu.']);
        }

        $institution->delete();

        return redirect()->route('admin.institutions.index')
            ->with('success', "Institusi {$institution->name} dihapus.");
    }

    private function formData(Institution $institution): array
    {
        return [
            'institution' => $institution,
            'types' => InstitutionType::cases(),
            'countries' => Country::orderBy('name')->get(),
        ];
    }
}
