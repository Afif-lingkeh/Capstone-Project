<?php
// app/Http/Controllers/Admin/ConsentController.php

namespace App\Http\Controllers\Admin;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\GuardianConsent;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

// Persetujuan orang tua/wali per santri (keputusan Fase 1 no. 3).
class ConsentController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [new Middleware('permission:consents.manage')];
    }

    public function index(Request $request): View
    {
        $santri = User::role(Role::Santri->value)
            ->with(['santriProfile', 'guardianConsents' => fn ($q) => $q->latest('consented_at')])
            ->when($request->filled('q'), function (Builder $q) use ($request) {
                $term = '%'.$request->string('q')->trim().'%';
                $q->where(fn (Builder $q) => $q->where('name', 'like', $term)->orWhereHas('santriProfile', fn ($q) => $q->where('nis', 'like', $term)));
            })
            ->when($request->query('status') === 'belum', fn (Builder $q) => $q->whereDoesntHave('guardianConsents', fn ($q) => $q->whereNull('revoked_at')))
            ->when($request->query('status') === 'sudah', fn (Builder $q) => $q->whereHas('guardianConsents', fn ($q) => $q->whereNull('revoked_at')))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.consents.index', [
            'santri' => $santri,
            'selected' => $request->integer('santri') ? User::role(Role::Santri->value)->with('santriProfile')->find($request->integer('santri')) : null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'santri_id' => ['required', 'integer', Rule::exists('users', 'id')],
            'guardian_name' => ['required', 'string', 'max:255'],
            'relationship' => ['required', 'string', 'max:50'],
            'method' => ['required', Rule::in(['tertulis', 'lisan', 'daring'])],
            'consented_at' => ['required', 'date', 'before_or_equal:today'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);
        $santri = User::findOrFail($data['santri_id']);
        abort_unless($santri->hasRole(Role::Santri->value), 422);

        $consent = GuardianConsent::create([...$data, 'policy_version' => config('peduli.privacy_policy_version'), 'recorded_by' => $request->user()->id]);
        AuditLogger::record('consent.record', $consent, $santri->id);

        return redirect()->route('admin.consents.index')->with('success', "Persetujuan wali untuk {$santri->name} tercatat. Konseling & Asisten AI kini terbuka.");
    }

    public function revoke(Request $request, GuardianConsent $consent): RedirectResponse
    {
        $consent->update(['revoked_at' => now()]);
        AuditLogger::record('consent.revoke', $consent, $consent->santri_id);

        return back()->with('success', 'Persetujuan dicabut. Konseling & Asisten AI santri ini kembali terkunci.');
    }
}
