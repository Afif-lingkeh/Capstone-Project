<?php
// app/Listeners/RecordLastLogin.php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;

// Ditemukan otomatis oleh Laravel (event discovery) karena type-hint Login.
class RecordLastLogin
{
    public function handle(Login $event): void
    {
        $event->user->forceFill(['last_login_at' => now()])->saveQuietly();
    }
}
