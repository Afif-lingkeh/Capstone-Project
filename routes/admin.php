<?php
// routes/admin.php

use App\Http\Controllers\Admin\AlumniVerificationController;
use App\Http\Controllers\Admin\ArticleController;
use App\Http\Controllers\Admin\ChatLogController;
use App\Http\Controllers\Admin\ConsultationModerationController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\ConsentController;
use App\Http\Controllers\Admin\SantriImportController;
use App\Http\Controllers\Admin\ShiftController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MajorController;
use App\Http\Controllers\Admin\InstitutionController;
use App\Http\Controllers\Admin\OfficialLinkController;
use App\Http\Controllers\Admin\PartnerController;
use App\Http\Controllers\Admin\PotentialTestController;
use App\Http\Controllers\Admin\ProgramController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', DashboardController::class)->name('dashboard');

// Fase 2: pengguna (+ Fase 10: impor santri dari CSV)
Route::get('pengguna/impor', [SantriImportController::class, 'create'])->name('users.import');
Route::get('pengguna/impor/templat', [SantriImportController::class, 'template'])->name('users.import.template');
Route::post('pengguna/impor', [SantriImportController::class, 'store'])->name('users.import.store');
Route::resource('pengguna', UserController::class)
    ->except(['show', 'destroy'])
    ->parameters(['pengguna' => 'user'])
    ->names('users');
Route::patch('pengguna/{user}/status', [UserController::class, 'toggleStatus'])->name('users.status');

// Fase 3: direktori tautan resmi
Route::resource('institusi', InstitutionController::class)
    ->except('show')
    ->parameters(['institusi' => 'institution'])
    ->names('institutions');

Route::resource('tautan', OfficialLinkController::class)
    ->except('show')
    ->parameters(['tautan' => 'link'])
    ->names('links');
Route::post('tautan/{link}/verifikasi', [OfficialLinkController::class, 'verify'])->name('links.verify');
Route::post('tautan/{link}/tolak', [OfficialLinkController::class, 'reject'])->name('links.reject');
Route::post('tautan/{link}/cek', [OfficialLinkController::class, 'check'])->name('links.check');

// Fase 4: kerja & magang luar negeri
Route::resource('mitra', PartnerController::class)
    ->except('show')
    ->parameters(['mitra' => 'partner'])
    ->names('partners');
Route::resource('program', ProgramController::class)
    ->except('show')
    ->parameters(['program' => 'program'])
    ->names('programs');
Route::get('program/{program}/peminat', [ProgramController::class, 'interests'])->name('programs.interests');
Route::patch('program/{program}/peminat/{interest}', [ProgramController::class, 'followUp'])->name('programs.follow-up');

// Fase 8: verifikasi alumni & moderasi konsultasi
Route::get('alumni', [AlumniVerificationController::class, 'index'])->name('alumni.index');
Route::post('alumni/{profile}/keputusan', [AlumniVerificationController::class, 'decide'])->name('alumni.decide');
Route::get('konsultasi', [ConsultationModerationController::class, 'index'])->name('consultations.index');
Route::get('konsultasi/{consultation}', [ConsultationModerationController::class, 'show'])->name('consultations.show');
Route::post('laporan-konsultasi/{report}/selesai', [ConsultationModerationController::class, 'resolve'])->name('consultations.resolve');

// Fase 7: persetujuan wali, piket konselor, log audit
Route::get('persetujuan-wali', [ConsentController::class, 'index'])->name('consents.index');
Route::post('persetujuan-wali', [ConsentController::class, 'store'])->name('consents.store');
Route::patch('persetujuan-wali/{consent}/cabut', [ConsentController::class, 'revoke'])->name('consents.revoke');
Route::get('piket-konselor', [ShiftController::class, 'index'])->name('shifts.index');
Route::post('piket-konselor', [ShiftController::class, 'store'])->name('shifts.store');
Route::delete('piket-konselor/{shift}', [ShiftController::class, 'destroy'])->name('shifts.destroy');
Route::get('log-audit', [AuditLogController::class, 'index'])->name('audit.index');

// Fase 9: evaluasi Asisten AI (Super Admin)
Route::get('evaluasi-asisten', [ChatLogController::class, 'index'])->name('chat-logs.index');

// Fase 6: bank soal tes potensi
Route::resource('bank-soal', PotentialTestController::class)
    ->except('show')
    ->parameters(['bank-soal' => 'test'])
    ->names('tests');
Route::get('bank-soal/{test}/soal/baru', [PotentialTestController::class, 'createQuestion'])->name('questions.create');
Route::post('bank-soal/{test}/soal', [PotentialTestController::class, 'storeQuestion'])->name('questions.store');
Route::get('bank-soal/{test}/soal/{question}', [PotentialTestController::class, 'editQuestion'])->name('questions.edit');
Route::put('bank-soal/{test}/soal/{question}', [PotentialTestController::class, 'updateQuestion'])->name('questions.update');
Route::delete('bank-soal/{test}/soal/{question}', [PotentialTestController::class, 'destroyQuestion'])->name('questions.destroy');

// Fase 5: info kampus & jurusan
Route::resource('artikel', ArticleController::class)
    ->except('show')
    ->parameters(['artikel' => 'article'])
    ->names('articles');
Route::resource('jurusan', MajorController::class)
    ->only(['index', 'store', 'update', 'destroy'])
    ->parameters(['jurusan' => 'major'])
    ->names('majors');
