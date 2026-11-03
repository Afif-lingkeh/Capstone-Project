<?php
// app/Http/Controllers/Admin/PartnerController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PartnerRequest;
use App\Models\Country;
use App\Models\Partner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PartnerController extends Controller
{
    public function index(): View
    {
        Gate::authorize('viewAny', Partner::class);

        return view('admin.partners.index', [
            'partners' => Partner::with('country')->withCount('programs')->orderBy('name')->paginate(20),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', Partner::class);

        return view('admin.partners.form', ['partner' => new Partner, 'countries' => Country::orderBy('name')->get()]);
    }

    public function store(PartnerRequest $request): RedirectResponse
    {
        $partner = Partner::create($request->validated());

        return redirect()->route('admin.partners.index')->with('success', "Mitra {$partner->name} ditambahkan.");
    }

    public function edit(Partner $partner): View
    {
        Gate::authorize('update', $partner);

        return view('admin.partners.form', ['partner' => $partner, 'countries' => Country::orderBy('name')->get()]);
    }

    public function update(PartnerRequest $request, Partner $partner): RedirectResponse
    {
        $partner->update($request->validated());

        return redirect()->route('admin.partners.index')->with('success', "Mitra {$partner->name} diperbarui.");
    }

    public function destroy(Partner $partner): RedirectResponse
    {
        if (Gate::denies('delete', $partner)) {
            return back()->with('error', 'Mitra yang masih punya program tidak dapat dihapus.');
        }

        $partner->delete();

        return redirect()->route('admin.partners.index')->with('success', 'Mitra dihapus.');
    }
}
