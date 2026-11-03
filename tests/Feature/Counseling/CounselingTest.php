<?php
// tests/Feature/Counseling/CounselingTest.php

use App\Enums\CounselingStatus;
use App\Enums\Role;
use App\Models\AuditLog;
use App\Models\CounselingCase;
use App\Models\CounselingRequest;
use App\Models\CounselorShift;
use App\Models\CrisisAlert;
use App\Models\GuardianConsent;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->santri = User::factory()->withRole(Role::Santri)->create();
    $this->counselor = User::factory()->withRole(Role::Counselor)->create();
    $this->otherCounselor = User::factory()->withRole(Role::Counselor)->create();
    GuardianConsent::create(['santri_id' => $this->santri->id, 'guardian_name' => 'Ayah', 'relationship' => 'Ayah', 'method' => 'tertulis', 'consented_at' => now(), 'policy_version' => '1.0']);
});

function ajukan($santri, string $story = 'Saya sulit fokus belajar karena rindu rumah.')
{
    test()->actingAs($santri)->post(route('counseling.store'), ['topic' => 'pribadi', 'story' => $story, 'urgency' => 'medium']);

    return CounselingRequest::latest('id')->firstOrFail();
}

function bukaKasus($counselor, $request): CounselingCase
{
    test()->actingAs($counselor)->post(route('counselor.requests.accept', $request));

    return CounselingCase::latest('id')->firstOrFail();
}

test('konseling terkunci untuk santri tanpa persetujuan wali', function () {
    $noConsent = User::factory()->withRole(Role::Santri)->create();

    $this->actingAs($noConsent)->get(route('counseling.index'))->assertOk()->assertSee('Konseling belum dibuka');
    $this->actingAs($noConsent)->post(route('counseling.store'), ['topic' => 'pribadi', 'story' => 'Cerita yang cukup panjang.', 'urgency' => 'low'])->assertForbidden();
});

test('cerita santri tersimpan terenkripsi di database', function () {
    $request = ajukan($this->santri, 'Rahasia sangat pribadi tentang keluarga.');

    $raw = DB::table('counseling_requests')->where('id', $request->id)->value('story');

    expect($raw)->not->toContain('Rahasia')
        ->and($request->fresh()->story)->toBe('Rahasia sangat pribadi tentang keluarga.');
});

test('tanda krisis memberi tahu konselor piket saja bila ada yang piket', function () {
    Notification::fake();
    CounselorShift::create(['counselor_id' => $this->counselor->id, 'starts_at' => now()->subHour(), 'ends_at' => now()->addHour()]);

    $request = ajukan($this->santri, 'Aku merasa ingin mati saja, capek sekali.');

    expect($request->is_crisis)->toBeTrue()->and(CrisisAlert::count())->toBe(1);
    Notification::assertSentTo($this->counselor, \App\Notifications\InAppNotification::class);
    Notification::assertNotSentTo($this->otherCounselor, \App\Notifications\InAppNotification::class);
});

test('tanpa konselor piket, semua konselor diberi tahu saat krisis', function () {
    Notification::fake();

    ajukan($this->santri, 'Kadang terpikir menyakiti diri sendiri.');

    Notification::assertSentTo([$this->counselor, $this->otherCounselor], \App\Notifications\InAppNotification::class);
});

test('hanya konselor pemegang kasus yang dapat membuka kasus, dan aksesnya tercatat', function () {
    $case = bukaKasus($this->counselor, ajukan($this->santri));

    $this->actingAs($this->counselor)->get(route('counselor.cases.show', $case))->assertOk()->assertSee('Akses ini tercatat.');
    $this->actingAs($this->otherCounselor)->get(route('counselor.cases.show', $case))->assertForbidden();

    expect(AuditLog::where('action', 'case.view')->where('user_id', $this->counselor->id)->exists())->toBeTrue();
});

test('admin dan super admin tidak dapat membuka kasus konseling', function (Role $role) {
    $case = bukaKasus($this->counselor, ajukan($this->santri));
    $admin = User::factory()->withRole($role)->create();

    $this->actingAs($admin)->get(route('counselor.cases.show', $case))->assertForbidden();
    $this->actingAs($admin)->get(route('counselor.requests.index'))->assertForbidden();
})->with([Role::SuperAdmin, Role::PesantrenAdmin]);

test('permintaan yang sama tidak bisa diambil dua konselor', function () {
    $request = ajukan($this->santri);

    bukaKasus($this->counselor, $request);
    $this->actingAs($this->otherCounselor)->post(route('counselor.requests.accept', $request));

    expect(CounselingCase::count())->toBe(1)->and(CounselingCase::first()->counselor_id)->toBe($this->counselor->id);
});

test('catatan sesi terenkripsi dan setelah alih tangan diterima konselor lama kehilangan akses', function () {
    $case = bukaKasus($this->counselor, ajukan($this->santri));

    $this->actingAs($this->counselor)->post(route('counselor.sessions.store', $case), [
        'held_at' => now()->subHour()->format('Y-m-d H:i'), 'mode' => 'offline', 'urgency' => 'medium',
        'chief_complaint' => 'Keluhan rahasia', 'assessment' => 'A', 'intervention' => 'B', 'follow_up_plan' => 'C',
    ])->assertSessionHasNoErrors();
    expect(DB::table('counseling_sessions')->value('chief_complaint'))->not->toContain('Keluhan rahasia');

    $this->actingAs($this->counselor)->post(route('counselor.cases.handover', $case), ['to_counselor_id' => $this->otherCounselor->id, 'reason' => 'Gender sama']);
    $handover = $case->handovers()->firstOrFail();

    // Selama belum diterima, konselor lama masih memegang kasus.
    $this->actingAs($this->counselor)->get(route('counselor.cases.show', $case))->assertOk();

    $this->actingAs($this->otherCounselor)->post(route('counselor.handovers.accept', $handover));

    $this->actingAs($this->counselor)->get(route('counselor.cases.show', $case))->assertForbidden();
    $this->actingAs($this->otherCounselor)->get(route('counselor.cases.show', $case))->assertOk()->assertSee('Keluhan rahasia');
});

test('hanya penerima yang dapat menjawab alih tangan', function () {
    $case = bukaKasus($this->counselor, ajukan($this->santri));
    $this->actingAs($this->counselor)->post(route('counselor.cases.handover', $case), ['to_counselor_id' => $this->otherCounselor->id, 'reason' => 'x']);
    $handover = $case->handovers()->firstOrFail();

    $this->actingAs($this->counselor)->post(route('counselor.handovers.accept', $handover))->assertForbidden();
    expect($case->fresh()->counselor_id)->toBe($this->counselor->id);
});

test('kasus yang ditutup tidak bisa ditambah catatan', function () {
    $case = bukaKasus($this->counselor, ajukan($this->santri));
    $this->actingAs($this->counselor)->post(route('counselor.cases.close', $case), ['closing_summary' => 'Membaik.']);

    expect($case->fresh()->status)->toBe(CounselingStatus::Closed);
    $this->actingAs($this->counselor)->post(route('counselor.sessions.store', $case), [
        'held_at' => now()->format('Y-m-d H:i'), 'mode' => 'offline', 'urgency' => 'low',
        'chief_complaint' => 'x', 'assessment' => 'x', 'intervention' => 'x', 'follow_up_plan' => 'x',
    ])->assertForbidden();
});

test('log audit hanya untuk super admin dan tidak memuat isi catatan', function () {
    $case = bukaKasus($this->counselor, ajukan($this->santri, 'Isi cerita yang tidak boleh bocor.'));
    $this->actingAs($this->counselor)->get(route('counselor.cases.show', $case));

    $super = User::factory()->withRole(Role::SuperAdmin)->create();
    $admin = User::factory()->withRole(Role::PesantrenAdmin)->create();

    $this->actingAs($super)->get(route('admin.audit.index'))->assertOk()->assertSee('Membuka catatan kasus')->assertDontSee('Isi cerita yang tidak boleh bocor.');
    $this->actingAs($admin)->get(route('admin.audit.index'))->assertForbidden();
});

test('admin mencatat dan mencabut persetujuan wali', function () {
    $admin = User::factory()->withRole(Role::PesantrenAdmin)->create();
    $santri = User::factory()->withRole(Role::Santri)->create();

    $this->actingAs($admin)->post(route('admin.consents.store'), [
        'santri_id' => $santri->id, 'guardian_name' => 'Ibu Fatimah', 'relationship' => 'Ibu', 'method' => 'tertulis', 'consented_at' => now()->toDateString(),
    ])->assertSessionHasNoErrors();
    expect($santri->hasGuardianConsent())->toBeTrue();

    $this->actingAs($admin)->patch(route('admin.consents.revoke', $santri->guardianConsents()->first()));
    expect($santri->hasGuardianConsent())->toBeFalse();
});

test('halaman fase 7 dapat dibuka', function () {
    $case = bukaKasus($this->counselor, ajukan($this->santri));
    $admin = User::factory()->withRole(Role::PesantrenAdmin)->create();

    $this->actingAs($this->santri)->get(route('counseling.index'))->assertOk()->assertSee('Status permintaanmu');
    $this->actingAs($this->santri)->get(route('counseling.create'))->assertOk();
    foreach (['counselor.dashboard', 'counselor.requests.index', 'counselor.cases.index', 'counselor.handovers.index', 'counselor.alerts.index', 'counselor.shifts.index'] as $page) {
        $this->actingAs($this->counselor)->get(route($page))->assertOk();
    }
    $this->actingAs($admin)->get(route('admin.consents.index', ['santri' => $this->santri->id]))->assertOk();
    $this->actingAs($admin)->get(route('admin.shifts.index'))->assertOk();
});
