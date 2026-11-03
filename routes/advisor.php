<?php
// routes/advisor.php

use App\Http\Controllers\Advisor\DashboardController;
use App\Http\Controllers\Advisor\SessionController;
use App\Http\Controllers\Advisor\SlotController;
use App\Http\Controllers\Advisor\StudentController;
use Illuminate\Support\Facades\Route;

Route::get('/', DashboardController::class)->name('dashboard');

// Fase 5: slot jadwal & riwayat sesi bimbingan
Route::get('/slot', [SlotController::class, 'index'])->name('slots.index');
Route::post('/slot', [SlotController::class, 'store'])->name('slots.store');
Route::delete('/slot/{slot}', [SlotController::class, 'destroy'])->name('slots.destroy');
Route::get('/sesi', [SessionController::class, 'index'])->name('sessions.index');
Route::patch('/sesi/{booking}', [SessionController::class, 'update'])->name('sessions.update');

// Fase 6: hasil tes santri binaan
Route::get('/santri', [StudentController::class, 'index'])->name('students.index');
Route::get('/santri/{student}', [StudentController::class, 'show'])->name('students.show');
