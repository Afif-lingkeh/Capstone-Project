<?php
// app/Http/Controllers/Alumni/ProfileController.php

namespace App\Http\Controllers\Alumni;

use App\Enums\Pathway;
use App\Http\Controllers\Controller;
use App\Models\Consultation;
use App\Models\Country;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function dashboard(Request $request): View
    {
        $user = $request->user();
        $profile = $user->alumniProfile()->with('country')->firstOrCreate([], ['graduation_year' => now()->year, 'institution_name' => '[Nama kampus/tempat kerja]', 'activity' => 'studying']);

        return view('alumni.dashboard', [
            'profile' => $profile,
            'completion' => $this->completion($profile),
            'pending' => Consultation::where('alumni_id', $user->id)->where('status', 'pending')->with(['santri.santriProfile', 'messages'])->latest()->get(),
            'activeCount' => Consultation::where('alumni_id', $user->id)->where('status', 'accepted')->count(),
        ]);
    }

    public function edit(Request $request): View
    {
        return view('alumni.profile', [
            'profile' => $request->user()->alumniProfile()->with('country')->firstOrFail(),
            'countries' => Country::orderBy('name')->get(),
            'pathways' => Pathway::cases(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'graduation_year' => ['required', 'integer', 'min:1990', 'max:'.now()->year],
            'institution_name' => ['required', 'string', 'max:255'],
            'field' => ['nullable', 'string', 'max:150'],
            'pathway' => ['nullable', Rule::enum(Pathway::class)],
            'city' => ['nullable', 'string', 'max:100'],
            'country_id' => ['required', 'integer', Rule::exists('countries', 'id')],
            'activity' => ['required', Rule::in(['studying', 'working', 'other'])],
            'bio' => ['nullable', 'string', 'max:600'],
            'linkedin_url' => ['nullable', 'url:https', 'max:255', 'regex:/linkedin\.com/i'],
            'public_email' => ['nullable', 'email', 'max:255'],
            'show_linkedin' => ['boolean'],
            'show_email' => ['boolean'],
            'whatsapp' => ['nullable', 'string', 'max:20'],
            'accepts_consultations' => ['boolean'],
        ], ['linkedin_url.regex' => 'Masukkan tautan profil LinkedIn (linkedin.com).']);

        foreach (['show_linkedin', 'show_email', 'accepts_consultations'] as $flag) {
            $data[$flag] = $request->boolean($flag);
        }

        $request->user()->alumniProfile->update($data);

        return back()->with('success', 'Profil alumni diperbarui.');
    }

    private function completion($profile): int
    {
        $fields = ['institution_name', 'field', 'pathway', 'city', 'country_id', 'bio'];
        // Nilai placeholder "[...]" dari pembuatan otomatis tidak dihitung sebagai terisi.
        $filled = collect($fields)->filter(fn ($f) => filled($profile->$f) && ! (is_string($profile->$f) && str_starts_with($profile->$f, '[')))->count();
        $filled += ($profile->show_linkedin && $profile->linkedin_url) || ($profile->show_email && $profile->public_email) ? 1 : 0;

        return (int) round($filled / (count($fields) + 1) * 100);
    }
}
