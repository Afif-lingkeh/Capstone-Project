<?php
// app/Http/Controllers/ProfileController.php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user()->load('santriProfile'),
        ]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->fill($request->safe()->only(['name', 'email']));

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        // NIS, jenjang, kelas, dan tahun masuk tetap diatur admin; santri hanya mengisi cita-citanya.
        if ($user->santriProfile && $request->has('aspiration')) {
            $user->santriProfile->update(['aspiration' => $request->validated('aspiration')]);
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }
}
