<?php
// app/Http/Controllers/Auth/AlumniRegistrationController.php

namespace App\Http\Controllers\Auth;

use App\Enums\Gender;
use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\User;
use App\Notifications\InAppNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

// Alumni mendaftar sendiri, lalu diverifikasi admin sebelum tampil di direktori (keputusan Fase 1 no. 2).
class AlumniRegistrationController extends Controller
{
    public function create(): View
    {
        return view('auth.register-alumni', ['countries' => Country::orderBy('name')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')],
            'username' => ['required', 'string', 'min:4', 'max:50', 'alpha_dash', Rule::unique('users')],
            'gender' => ['required', Rule::enum(Gender::class)],
            'graduation_year' => ['required', 'integer', 'min:1990', 'max:'.now()->year],
            'institution_name' => ['required', 'string', 'max:255'],
            'country_id' => ['required', 'integer', Rule::exists('countries', 'id')],
            'whatsapp' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'], 'email' => $data['email'], 'username' => $data['username'],
                'gender' => $data['gender'], 'password' => $data['password'], 'is_active' => true,
            ]);
            $user->assignRole(Role::Alumni->value);
            $user->alumniProfile()->create([
                'graduation_year' => $data['graduation_year'], 'institution_name' => $data['institution_name'],
                'country_id' => $data['country_id'], 'whatsapp' => $data['whatsapp'] ?? null,
            ]);

            return $user;
        });

        Notification::send(
            User::permission('alumni.verify')->where('is_active', true)->get(),
            new InAppNotification(title: 'Pendaftaran alumni baru', body: $user->name.' (lulus '.$data['graduation_year'].') menunggu verifikasi.', url: route('admin.alumni.index'), icon: 'user-check'),
        );

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('alumni.dashboard')->with('success', 'Jazakallahu khairan. Profil Anda sedang diverifikasi admin pesantren. Sambil menunggu, silakan lengkapi profil Anda.');
    }
}
