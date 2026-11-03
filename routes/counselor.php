<?php
// routes/counselor.php

use App\Http\Controllers\Counselor\AlertController;
use App\Http\Controllers\Counselor\CaseController;
use App\Http\Controllers\Counselor\DashboardController;
use App\Http\Controllers\Counselor\HandoverController;
use App\Http\Controllers\Counselor\RequestController;
use Illuminate\Support\Facades\Route;

Route::get('/', DashboardController::class)->name('dashboard');

// Fase 7: konseling & catatan kasus
Route::get('/permintaan', [RequestController::class, 'index'])->name('requests.index');
Route::get('/permintaan/{counselingRequest}', [RequestController::class, 'show'])->name('requests.show');
Route::post('/permintaan/{counselingRequest}/terima', [RequestController::class, 'accept'])->name('requests.accept');

Route::get('/kasus', [CaseController::class, 'index'])->name('cases.index');
Route::get('/kasus/{case}', [CaseController::class, 'show'])->name('cases.show');
Route::post('/kasus/{case}/sesi', [CaseController::class, 'storeSession'])->name('sessions.store');
Route::post('/kasus/{case}/tutup', [CaseController::class, 'close'])->name('cases.close');
Route::post('/kasus/{case}/alih-tangan', [CaseController::class, 'handover'])->name('cases.handover');

Route::get('/alih-tangan', [HandoverController::class, 'index'])->name('handovers.index');
Route::post('/alih-tangan/{handover}/terima', [HandoverController::class, 'accept'])->name('handovers.accept');
Route::post('/alih-tangan/{handover}/tolak', [HandoverController::class, 'reject'])->name('handovers.reject');

Route::get('/krisis', [AlertController::class, 'index'])->name('alerts.index');
Route::post('/krisis/{alert}/tangani', [AlertController::class, 'acknowledge'])->name('alerts.acknowledge');
Route::get('/piket', [AlertController::class, 'shifts'])->name('shifts.index');
