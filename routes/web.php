<?php
// routes/web.php

use App\Http\Controllers\AlumniDirectoryController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\AvatarController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ConsultationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Public\OfficialLinkDirectoryController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'public.home')->name('home');
Route::view('/kebijakan-privasi', 'public.privacy')->name('privacy');

// Direktori tautan resmi sengaja publik: santri bisa mengecek kapan saja, bahkan sebelum login.
Route::get('/tautan-resmi', [OfficialLinkDirectoryController::class, 'index'])->name('links.index');
Route::view('/tautan-resmi/kenali-situs-palsu', 'links.guide')->name('links.guide');

Route::middleware('auth')->group(function () {
    // Satu pintu: diarahkan ke dashboard sesuai peran.
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/profil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profil', [ProfileController::class, 'update'])->name('profile.update');

    // Fase 5: info kampus dapat dibaca semua pengguna yang login.
    Route::get('/info-kampus', [ArticleController::class, 'index'])->name('articles.index');
    Route::get('/info-kampus/{article}', [ArticleController::class, 'show'])->name('articles.show');

    // Fase 8: direktori & konsultasi alumni
    Route::get('/direktori-alumni', [AlumniDirectoryController::class, 'index'])->name('alumni.index');
    Route::get('/direktori-alumni/{alumniProfile}', [AlumniDirectoryController::class, 'show'])->name('alumni.show');
    Route::middleware('role:santri|alumni')->group(function () {
        Route::get('/konsultasi', [ConsultationController::class, 'index'])->name('consultations.index');
        Route::post('/direktori-alumni/{alumniProfile}/konsultasi', [ConsultationController::class, 'store'])->name('consultations.store');
        Route::get('/konsultasi/{consultation}', [ConsultationController::class, 'show'])->name('consultations.show');
        Route::post('/konsultasi/{consultation}/pesan', [ConsultationController::class, 'message'])->name('consultations.message');
        Route::post('/konsultasi/{consultation}/jawab', [ConsultationController::class, 'respond'])->name('consultations.respond');
        Route::post('/konsultasi/{consultation}/selesai', [ConsultationController::class, 'close'])->name('consultations.close');
        Route::post('/konsultasi/{consultation}/laporkan', [ConsultationController::class, 'report'])->name('consultations.report');
    });

    // Foto profil & berkas (file di disk privat, akses diperiksa di controller/policy).
    Route::get('/foto-profil/{user}', [AvatarController::class, 'show'])->name('avatars.show');
    Route::post('/profil/foto', [AvatarController::class, 'update'])->middleware('throttle:10,1')->name('avatar.update');
    Route::delete('/profil/foto', [AvatarController::class, 'destroy'])->name('avatar.destroy');
    Route::get('/berkas/{document}/unduh', [DocumentController::class, 'download'])->name('documents.download');
    Route::delete('/berkas/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');

    Route::get('/notifikasi', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifikasi/baca-semua', [NotificationController::class, 'readAll'])->name('notifications.read-all');
    Route::get('/notifikasi/{id}', [NotificationController::class, 'show'])->name('notifications.show');
});

require __DIR__.'/auth.php';
