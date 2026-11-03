<?php
// routes/santri.php

use App\Http\Controllers\Santri\ChatController;
use App\Http\Controllers\Santri\CounselingController;
use App\Http\Controllers\Santri\DashboardController;
use App\Http\Controllers\Santri\GuidanceController;
use App\Http\Controllers\Santri\TestController;
use App\Http\Controllers\Santri\ProgramController;
use Illuminate\Support\Facades\Route;

Route::get('/beranda', DashboardController::class)->name('santri.dashboard');

// Fase 4: kerja & magang luar negeri
Route::get('/program', [ProgramController::class, 'index'])->name('programs.index');
Route::get('/program/{program}', [ProgramController::class, 'show'])->name('programs.show');
Route::post('/program/{program}/simpan', [ProgramController::class, 'bookmark'])->name('programs.bookmark');
Route::post('/program/{program}/minat', [ProgramController::class, 'interest'])->name('programs.interest');
Route::delete('/program/{program}/minat', [ProgramController::class, 'withdraw'])->name('programs.withdraw');

// Fase 5: bimbingan kuliah
Route::get('/bimbingan', [GuidanceController::class, 'index'])->name('guidance.index');
Route::post('/bimbingan/pesan', [GuidanceController::class, 'book'])->name('guidance.book');
Route::patch('/bimbingan/{booking}/batal', [GuidanceController::class, 'cancel'])->name('guidance.cancel');

// Fase 6: tes potensi & pemetaan jalur
Route::get('/tes', [TestController::class, 'index'])->name('tests.index');
Route::post('/tes/{test}/mulai', [TestController::class, 'start'])->name('tests.start');
Route::get('/tes/kerjakan/{attempt}', [TestController::class, 'show'])->name('attempts.show');
Route::post('/tes/kerjakan/{attempt}/jawab', [TestController::class, 'answer'])->name('attempts.answer');
Route::post('/tes/kerjakan/{attempt}/kumpulkan', [TestController::class, 'submit'])->name('attempts.submit');
Route::get('/tes/hasil/{attempt}', [TestController::class, 'result'])->name('attempts.result');

// Fase 7: konseling
Route::get('/konseling', [CounselingController::class, 'index'])->name('counseling.index');
Route::get('/konseling/ajukan', [CounselingController::class, 'create'])->name('counseling.create');
Route::post('/konseling', [CounselingController::class, 'store'])->name('counseling.store');
Route::patch('/konseling/{counselingRequest}/batal', [CounselingController::class, 'cancel'])->name('counseling.cancel');

// Fase 9: Asisten AI
Route::get('/asisten', [ChatController::class, 'index'])->name('chat.index');
Route::get('/asisten/{conversation}', [ChatController::class, 'index'])->name('chat.show');
Route::post('/asisten', [ChatController::class, 'send'])->middleware('throttle:10,1')->name('chat.send');
Route::post('/asisten/pesan/{message}/nilai', [ChatController::class, 'rate'])->name('chat.rate');
