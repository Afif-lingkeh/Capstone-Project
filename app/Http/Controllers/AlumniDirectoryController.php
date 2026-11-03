<?php
// app/Http/Controllers/AlumniDirectoryController.php

namespace App\Http\Controllers;

use App\Enums\Pathway;
use App\Models\AlumniProfile;
use App\Models\Country;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

// Direktori alumni terverifikasi (dibaca santri dan peran lain yang login).
class AlumniDirectoryController extends Controller
{
    public function index(Request $request): View
    {
        $profiles = AlumniProfile::verified()
            ->with(['user', 'country'])
            ->whereHas('user', fn (Builder $q) => $q->where('is_active', true))
            ->when($request->integer('negara'), fn (Builder $q, int $id) => $q->where('country_id', $id))
            ->when(Pathway::tryFrom((string) $request->query('jalur')), fn (Builder $q, Pathway $p) => $q->where('pathway', $p))
            ->when($request->filled('q'), function (Builder $q) use ($request) {
                $term = '%'.$request->string('q')->trim().'%';
                $q->where(fn (Builder $q) => $q->where('institution_name', 'like', $term)->orWhere('field', 'like', $term)->orWhere('city', 'like', $term));
            })
            ->orderByDesc('graduation_year')
            ->paginate(12)
            ->withQueryString();

        return view('alumni.index', [
            'profiles' => $profiles,
            'countries' => Country::whereIn('id', AlumniProfile::verified()->select('country_id'))->orderBy('name')->get(),
            'pathways' => Pathway::cases(),
        ]);
    }

    public function show(Request $request, AlumniProfile $alumniProfile): View
    {
        abort_unless($alumniProfile->isVerified() && $alumniProfile->user->is_active, 404);

        $user = $request->user();

        return view('alumni.show', [
            'profile' => $alumniProfile->load(['user', 'country']),
            'canConsult' => $user->hasRole('santri') && $alumniProfile->accepts_consultations,
            'existing' => $user->hasRole('santri')
                ? \App\Models\Consultation::where('santri_id', $user->id)->where('alumni_id', $alumniProfile->user_id)->whereIn('status', ['pending', 'accepted'])->first()
                : null,
        ]);
    }
}
