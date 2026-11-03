<?php
// routes/alumni.php

use App\Http\Controllers\Alumni\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ProfileController::class, 'dashboard'])->name('dashboard');

// Fase 8: profil alumni (pilih kontak yang ditampilkan)
Route::get('/profil', [ProfileController::class, 'edit'])->name('profile.edit');
Route::patch('/profil', [ProfileController::class, 'update'])->name('profile.update');
