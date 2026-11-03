<?php
// app/Http/Controllers/Admin/AlumniVerificationController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AlumniProfile;
use App\Notifications\InAppNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AlumniVerificationController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [new Middleware('permission:alumni.verify')];
    }

    public function index(Request $request): View
    {
        $status = in_array($request->query('status'), ['pending', 'verified', 'rejected'], true) ? $request->query('status') : 'pending';

        return view('admin.alumni.index', [
            'profiles' => AlumniProfile::where('verification_status', $status)->with(['user', 'country'])->latest()->paginate(20)->withQueryString(),
            'status' => $status,
            'counts' => AlumniProfile::selectRaw('verification_status, count(*) as total')->groupBy('verification_status')->pluck('total', 'verification_status'),
        ]);
    }

    public function decide(Request $request, AlumniProfile $profile): RedirectResponse
    {
        $data = $request->validate([
            'decision' => ['required', Rule::in(['verified', 'rejected'])],
            'rejection_note' => ['nullable', 'required_if:decision,rejected', 'string', 'max:500'],
        ], ['rejection_note.required_if' => 'Tuliskan alasan agar alumni bisa memperbaiki datanya.']);

        $profile->forceFill([
            'verification_status' => $data['decision'],
            'verified_at' => $data['decision'] === 'verified' ? now() : null,
            'verified_by' => $request->user()->id,
            'rejection_note' => $data['rejection_note'] ?? null,
        ])->save();

        $profile->user->notify(new InAppNotification(
            title: $data['decision'] === 'verified' ? 'Profil alumni terverifikasi' : 'Verifikasi alumni belum disetujui',
            body: $data['decision'] === 'verified' ? 'Profilmu kini tampil di direktori alumni. Jazakallahu khairan!' : $data['rejection_note'],
            url: route('alumni.profile.edit'),
            icon: 'user-check',
        ));

        return back()->with('success', $data['decision'] === 'verified' ? 'Alumni diverifikasi dan tampil di direktori.' : 'Verifikasi ditolak, alumni diberi tahu alasannya.');
    }
}
