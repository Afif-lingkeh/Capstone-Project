<?php
// tests/Feature/ProfileTest.php

use App\Models\User;
use Illuminate\Support\Facades\Route;

test('halaman profil dapat dibuka', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('profile.edit'))->assertOk();
});

test('nama dan email dapat diperbarui', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->patch(route('profile.update'), ['name' => 'Nama Baru', 'email' => 'baru@peduli.test'])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('profile.edit'));

    $user->refresh();

    expect($user->name)->toBe('Nama Baru')
        ->and($user->email)->toBe('baru@peduli.test')
        ->and($user->email_verified_at)->toBeNull();
});

test('email boleh dikosongkan', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->patch(route('profile.update'), ['name' => $user->name, 'email' => ''])
        ->assertSessionHasNoErrors();

    expect($user->refresh()->email)->toBeNull();
});

test('username tidak dapat diubah lewat halaman profil', function () {
    $user = User::factory()->create(['username' => 'asli']);

    $this->actingAs($user)->patch(route('profile.update'), [
        'name' => $user->name,
        'email' => $user->email,
        'username' => 'diubah',
    ]);

    expect($user->refresh()->username)->toBe('asli');
});

test('fitur hapus akun mandiri tidak tersedia', function () {
    expect(Route::has('profile.destroy'))->toBeFalse();
});
