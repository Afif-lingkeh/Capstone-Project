<?php
// tests/Feature/RoleAccessTest.php

use App\Enums\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('/dashboard mengarahkan setiap peran ke dashboard-nya', function (Role $role) {
    $user = User::factory()->withRole($role)->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertRedirect(route($role->dashboardRoute()));
})->with(Role::cases());

test('setiap peran dapat membuka dashboard-nya', function (Role $role) {
    $factory = $role === Role::Santri ? User::factory()->santri() : User::factory()->withRole($role);

    $this->actingAs($factory->create())
        ->get(route($role->dashboardRoute()))
        ->assertOk();
})->with(Role::cases());

test('santri tidak dapat membuka area admin', function () {
    $santri = User::factory()->withRole(Role::Santri)->create();

    $this->actingAs($santri)->get(route('admin.dashboard'))->assertForbidden();
});

test('konselor tidak dapat membuka area pembimbing', function () {
    $counselor = User::factory()->withRole(Role::Counselor)->create();

    $this->actingAs($counselor)->get(route('advisor.dashboard'))->assertForbidden();
});

test('admin pesantren tidak dapat mengelola pengguna', function () {
    $admin = User::factory()->withRole(Role::PesantrenAdmin)->create();

    $this->actingAs($admin)->get(route('admin.users.index'))->assertForbidden();
});

test('super admin tidak memiliki permission konseling', function () {
    $super = User::factory()->withRole(Role::SuperAdmin)->create();

    expect($super->checkPermissionTo('counseling.handle'))->toBeFalse();
});

test('super admin dapat membuat akun santri tanpa email beserta profilnya', function () {
    $super = User::factory()->withRole(Role::SuperAdmin)->create();

    $this->actingAs($super)->post(route('admin.users.store'), [
        'name' => 'Ahmad Fauzi',
        'username' => '20240012',
        'email' => '',
        'gender' => 'L',
        'phone' => '',
        'role' => 'santri',
        'nis' => '20240012',
        'level' => 'smp',
        'class_name' => '8B',
        'entry_year' => 2024,
        'guardian_name' => 'Budi Santoso',
        'password' => 'rahasia123',
        'password_confirmation' => 'rahasia123',
    ])->assertSessionHasNoErrors()->assertRedirect(route('admin.users.index'));

    $user = User::where('username', '20240012')->firstOrFail();

    expect($user->hasRole('santri'))->toBeTrue()
        ->and($user->email)->toBeNull()
        ->and($user->santriProfile->nis)->toBe('20240012');
});

test('NIS wajib diisi untuk peran santri', function () {
    $super = User::factory()->withRole(Role::SuperAdmin)->create();

    $this->actingAs($super)->post(route('admin.users.store'), [
        'name' => 'Tanpa NIS',
        'username' => 'tanpanis',
        'gender' => 'P',
        'role' => 'santri',
        'password' => 'rahasia123',
        'password_confirmation' => 'rahasia123',
    ])->assertSessionHasErrors(['nis', 'level', 'class_name', 'entry_year']);
});

test('super admin tidak dapat menonaktifkan akunnya sendiri', function () {
    $super = User::factory()->withRole(Role::SuperAdmin)->create();

    $this->actingAs($super)
        ->patch(route('admin.users.status', $super))
        ->assertForbidden();
});

test('super admin tidak dapat menurunkan perannya sendiri', function () {
    $super = User::factory()->withRole(Role::SuperAdmin)->create(['gender' => 'L']);

    $this->actingAs($super)->put(route('admin.users.update', $super), [
        'name' => $super->name,
        'username' => $super->username,
        'email' => $super->email,
        'gender' => 'L',
        'role' => 'counselor',
    ])->assertSessionHasErrors('role');
});

test('pengguna yang dinonaktifkan dikeluarkan dari sesi', function () {
    $user = User::factory()->withRole(Role::Santri)->create();

    $this->actingAs($user);
    $user->update(['is_active' => false]);

    $this->get(route('santri.dashboard'))->assertRedirect(route('login'));
    $this->assertGuest();
});
