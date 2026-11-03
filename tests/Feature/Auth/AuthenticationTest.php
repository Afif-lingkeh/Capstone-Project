<?php
// tests/Feature/Auth/AuthenticationTest.php

use App\Models\User;

test('halaman login dapat dibuka', function () {
    $this->get('/login')->assertOk();
});

test('pengguna dapat login dengan email', function () {
    $user = User::factory()->create();

    $this->post('/login', ['login' => $user->email, 'password' => 'password'])
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticatedAs($user);
});

test('santri dapat login dengan NIS/username tanpa email', function () {
    $user = User::factory()->create(['username' => '20250099', 'email' => null]);

    $this->post('/login', ['login' => '20250099', 'password' => 'password']);

    $this->assertAuthenticatedAs($user);
});

test('login gagal jika password salah', function () {
    $user = User::factory()->create();

    $this->post('/login', ['login' => $user->username, 'password' => 'salah-password']);

    $this->assertGuest();
});

test('akun nonaktif tidak dapat login', function () {
    $user = User::factory()->inactive()->create();

    $this->post('/login', ['login' => $user->username, 'password' => 'password']);

    $this->assertGuest();
});

test('waktu login terakhir tercatat', function () {
    $user = User::factory()->create();

    $this->post('/login', ['login' => $user->username, 'password' => 'password']);

    expect($user->fresh()->last_login_at)->not->toBeNull();
});

test('pengguna dapat logout', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post('/logout')->assertRedirect('/');

    $this->assertGuest();
});
