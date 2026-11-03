<?php
// routes/console.php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Fase 10: membuat akun Super Admin pertama di server production (seeder demo tidak berjalan di production).
Artisan::command('peduli:create-admin {username} {name} {email}', function (string $username, string $name, string $email) {
    $password = $this->secret('Password (min. 8 karakter, huruf dan angka)');
    $validator = validator(compact('username', 'email', 'password'), [
        'username' => ['required', 'alpha_dash', 'unique:users,username'],
        'email' => ['required', 'email', 'unique:users,email'],
        'password' => ['required', \Illuminate\Validation\Rules\Password::defaults()],
    ]);
    if ($validator->fails()) {
        $this->error($validator->errors()->first());

        return 1;
    }

    $this->call('db:seed', ['--class' => 'RolePermissionSeeder', '--force' => true]);
    \App\Models\User::create(['username' => $username, 'name' => $name, 'email' => $email, 'password' => $password, 'gender' => 'L', 'is_active' => true])
        ->assignRole(\App\Enums\Role::SuperAdmin->value);
    $this->info("Super Admin {$username} dibuat. Silakan masuk dan ganti data profil bila perlu.");
})->purpose('Buat akun Super Admin pertama');

// Setiap malam: cek tautan yang terakhir dicek lebih dari 7 hari lalu.
// Membutuhkan cron "php artisan schedule:run" setiap menit (lihat panduan deployment Fase 10).
Schedule::command('links:check')
    ->dailyAt('02:00')
    ->withoutOverlapping();
