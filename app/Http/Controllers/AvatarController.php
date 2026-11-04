<?php
// app/Http/Controllers/AvatarController.php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

// Foto profil. Disimpan di disk privat karena sebagian besar santri di bawah umur:
// foto santri hanya bisa dilihat santri itu sendiri dan staf pesantren (bukan santri lain atau alumni).
class AvatarController extends Controller
{
    private const DISK = 'local';

    public function show(Request $request, User $user): StreamedResponse
    {
        $viewer = $request->user();
        $isStaff = ! $viewer->hasAnyRole([Role::Santri->value, Role::Alumni->value]);
        abort_unless($user->avatar_path && ($viewer->is($user) || $isStaff || ! $user->hasRole(Role::Santri->value)), 404);
        abort_unless(Storage::disk(self::DISK)->exists($user->avatar_path), 404);

        return Storage::disk(self::DISK)->response($user->avatar_path, null, [
            'Cache-Control' => 'private, max-age=86400',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048', 'dimensions:min_width=100,min_height=100,max_width=4000,max_height=4000'],
        ], [
            'avatar.image' => 'File harus berupa foto (JPG, PNG, atau WebP).',
            'avatar.max' => 'Ukuran foto maksimal 2 MB.',
            'avatar.dimensions' => 'Foto minimal 100×100 piksel dan maksimal 4000×4000 piksel.',
        ]);

        $user = $request->user();
        $old = $user->avatar_path;
        $path = $request->file('avatar')->store('avatars', self::DISK);
        $user->forceFill(['avatar_path' => $path])->save();

        if ($old) {
            Storage::disk(self::DISK)->delete($old);
        }

        return back()->with('success', 'Foto profil diperbarui.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();
        if ($user->avatar_path) {
            Storage::disk(self::DISK)->delete($user->avatar_path);
            $user->forceFill(['avatar_path' => null])->save();
        }

        return back()->with('success', 'Foto profil dihapus.');
    }
}
