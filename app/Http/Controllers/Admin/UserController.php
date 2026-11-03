<?php
// app/Http/Controllers/Admin/UserController.php

namespace App\Http\Controllers\Admin;

use App\Enums\Gender;
use App\Enums\Role;
use App\Enums\SantriLevel;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', User::class);

        $users = User::query()
            ->with('roles')
            ->when($request->filled('q'), function (Builder $query) use ($request) {
                $term = '%'.$request->string('q')->trim().'%';
                $query->where(fn (Builder $q) => $q
                    ->where('name', 'like', $term)
                    ->orWhere('username', 'like', $term)
                    ->orWhere('email', 'like', $term));
            })
            ->when(Role::tryFrom((string) $request->query('role')), fn (Builder $query, Role $role) => $query->role($role->value))
            ->when(in_array($request->query('status'), ['active', 'inactive'], true), fn (Builder $query) => $query->where('is_active', $request->query('status') === 'active'))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'roles' => Role::cases(),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', User::class);

        return view('admin.users.create', $this->formData(new User, Role::Santri->value));
    }

    public function store(UserRequest $request): RedirectResponse
    {
        // Transaksi: akun, peran, dan profil santri tersimpan semua atau tidak sama sekali.
        $user = DB::transaction(function () use ($request) {
            $user = User::create($request->userAttributes());
            $user->assignRole($request->validated('role'));
            $this->syncSantriProfile($user, $request);

            return $user;
        });

        return redirect()->route('admin.users.index')
            ->with('success', "Akun {$user->name} berhasil dibuat.");
    }

    public function edit(User $user): View
    {
        Gate::authorize('update', $user);

        $user->load('roles', 'santriProfile');

        return view('admin.users.edit', $this->formData($user, $user->primaryRole()?->value));
    }

    public function update(UserRequest $request, User $user): RedirectResponse
    {
        DB::transaction(function () use ($request, $user) {
            $user->update($request->userAttributes());
            $user->syncRoles([$request->validated('role')]);
            $this->syncSantriProfile($user, $request);
        });

        return redirect()->route('admin.users.index')
            ->with('success', "Akun {$user->name} berhasil diperbarui.");
    }

    public function toggleStatus(User $user): RedirectResponse
    {
        Gate::authorize('toggleStatus', $user);

        $user->update(['is_active' => ! $user->is_active]);

        return back()->with('success', $user->is_active
            ? "Akun {$user->name} diaktifkan."
            : "Akun {$user->name} dinonaktifkan.");
    }

    // Profil santri tidak dihapus saat peran berubah (mis. santri lulus menjadi alumni),
    // agar riwayatnya tetap ada.
    private function syncSantriProfile(User $user, UserRequest $request): void
    {
        if ($request->isSantri()) {
            $user->santriProfile()->updateOrCreate([], $request->santriAttributes());
        }
    }

    private function formData(User $user, ?string $currentRole): array
    {
        return [
            'user' => $user,
            'currentRole' => $currentRole,
            'roles' => Role::cases(),
            'genders' => Gender::cases(),
            'levels' => SantriLevel::cases(),
        ];
    }
}
