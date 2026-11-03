<?php
// tests/Feature/Tests/PotentialTestTest.php

use App\Enums\Role;
use App\Models\PotentialTest;
use App\Models\TestAttempt;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->santri = User::factory()->withRole(Role::Santri)->create();

    // Tes kecil: 2 pernyataan Likert (Saintek, Bahasa) + 1 pilihan ganda berbobot.
    $this->test = PotentialTest::create(['title' => 'Minat Bakat', 'dimension' => 'interest', 'is_active' => true]);
    $this->likertSaintek = $this->test->questions()->create(['body' => 'Saya suka eksperimen.', 'type' => 'likert', 'pathway' => 'saintek', 'position' => 1]);
    $this->likertBahasa = $this->test->questions()->create(['body' => 'Saya suka menulis.', 'type' => 'likert', 'pathway' => 'bahasa', 'position' => 2]);
    $this->choice = $this->test->questions()->create(['body' => 'Kegiatan favorit?', 'type' => 'choice', 'position' => 3]);
    $this->optLab = $this->choice->options()->create(['label' => 'Laboratorium', 'weights' => ['saintek' => 4, 'soshum' => 0, 'bahasa' => 0, 'keagamaan' => 0]]);
    $this->optKajian = $this->choice->options()->create(['label' => 'Kajian kitab', 'weights' => ['saintek' => 0, 'soshum' => 0, 'bahasa' => 1, 'keagamaan' => 4]]);
});

function startAttempt($test, $santri): TestAttempt
{
    test()->actingAs($santri)->post(route('tests.start', $test));

    return TestAttempt::firstOrFail();
}

test('memulai tes dua kali melanjutkan percobaan yang sama', function () {
    $this->actingAs($this->santri)->post(route('tests.start', $this->test));
    $this->actingAs($this->santri)->post(route('tests.start', $this->test));

    expect(TestAttempt::count())->toBe(1);
});

test('jawaban tersimpan otomatis dan bisa diubah', function () {
    $attempt = startAttempt($this->test, $this->santri);

    $this->actingAs($this->santri)->postJson(route('attempts.answer', $attempt), ['question_id' => $this->likertSaintek->id, 'answer' => 3])->assertOk()->assertJson(['answered' => 1]);
    $this->actingAs($this->santri)->postJson(route('attempts.answer', $attempt), ['question_id' => $this->likertSaintek->id, 'answer' => 5])->assertOk();

    expect($attempt->answers()->count())->toBe(1)->and($attempt->answers()->first()->value)->toBe(5);
});

test('nilai Likert di luar 1–5 dan opsi dari soal lain ditolak', function () {
    $attempt = startAttempt($this->test, $this->santri);
    $other = PotentialTest::create(['title' => 'Lain', 'dimension' => 'academic'])->questions()->create(['body' => 'x', 'type' => 'choice'])
        ->options()->create(['label' => 'x', 'weights' => []]);

    $this->actingAs($this->santri)->postJson(route('attempts.answer', $attempt), ['question_id' => $this->likertSaintek->id, 'answer' => 9])->assertUnprocessable();
    $this->actingAs($this->santri)->postJson(route('attempts.answer', $attempt), ['question_id' => $this->choice->id, 'answer' => $other->id])->assertUnprocessable();
});

test('tes tidak bisa dikumpulkan sebelum semua soal dijawab', function () {
    $attempt = startAttempt($this->test, $this->santri);

    $this->actingAs($this->santri)->post(route('attempts.submit', $attempt), ['answers' => [$this->likertSaintek->id => 5]])
        ->assertRedirect(route('attempts.show', $attempt));

    expect($attempt->fresh()->isSubmitted())->toBeFalse();
});

test('hasil berupa profil persentase empat jalur', function () {
    $attempt = startAttempt($this->test, $this->santri);

    $this->actingAs($this->santri)->post(route('attempts.submit', $attempt), ['answers' => [
        $this->likertSaintek->id => 5,  // saintek +4 dari maks 4
        $this->likertBahasa->id => 3,   // bahasa +2 dari maks 4
        $this->choice->id => $this->optLab->id, // saintek +4 dari maks 4; bahasa +0 dari maks 1
    ]])->assertRedirect(route('attempts.result', $attempt));

    expect($attempt->fresh()->scores)->toBe(['saintek' => 100, 'soshum' => null, 'bahasa' => 40, 'keagamaan' => 0]);

    $this->actingAs($this->santri)->get(route('attempts.result', $attempt))->assertOk()->assertSee('Kuat di Saintek dan Bahasa');
});

test('santri tidak dapat membuka percobaan tes milik santri lain', function () {
    $attempt = startAttempt($this->test, $this->santri);
    $other = User::factory()->withRole(Role::Santri)->create();

    $this->actingAs($other)->get(route('attempts.show', $attempt))->assertForbidden();
    $this->actingAs($other)->postJson(route('attempts.answer', $attempt), ['question_id' => $this->likertSaintek->id, 'answer' => 1])->assertForbidden();
});

test('pembimbing hanya melihat hasil santri binaannya', function () {
    $advisor = User::factory()->withRole(Role::AcademicAdvisor)->create();
    $advisee = User::factory()->withRole(Role::Santri)->create();
    $advisor->advisees()->attach($advisee);

    $this->actingAs($advisor)->get(route('advisor.students.index'))->assertOk()->assertSee($advisee->name);
    $this->actingAs($advisor)->get(route('advisor.students.show', $advisee))->assertOk();
    $this->actingAs($advisor)->get(route('advisor.students.show', $this->santri))->assertForbidden();
});

test('admin mengelola soal; tes tanpa soal tidak bisa diaktifkan', function () {
    $admin = User::factory()->withRole(Role::PesantrenAdmin)->create();
    $empty = PotentialTest::create(['title' => 'Kosong', 'dimension' => 'academic']);

    $this->actingAs($admin)->put(route('admin.tests.update', $empty), ['title' => 'Kosong', 'dimension' => 'academic', 'estimated_minutes' => 10, 'is_active' => 1]);
    expect($empty->fresh()->is_active)->toBeFalse();

    $this->actingAs($admin)->post(route('admin.questions.store', $empty), [
        'type' => 'choice', 'body' => 'Pilih satu',
        'options' => [['label' => 'A', 'weights' => ['saintek' => 3]], ['label' => 'B', 'weights' => ['soshum' => 2]]],
    ])->assertSessionHasNoErrors();
    expect($empty->questions()->first()->options()->first()->weights)->toBe(['saintek' => 3, 'soshum' => 0, 'bahasa' => 0, 'keagamaan' => 0]);

    $this->actingAs($admin)->post(route('admin.questions.store', $empty), ['type' => 'likert', 'body' => 'Tanpa jalur'])->assertSessionHasErrors('pathway');

    foreach (['admin.tests.index', 'admin.tests.create'] as $page) {
        $this->actingAs($admin)->get(route($page))->assertOk();
    }
    $this->actingAs($admin)->get(route('admin.tests.edit', $empty))->assertOk();
    $this->actingAs($admin)->get(route('admin.questions.create', $empty))->assertOk();
    $this->actingAs($this->santri)->get(route('admin.tests.index'))->assertForbidden();
});

test('halaman tes santri dapat dibuka', function () {
    $attempt = startAttempt($this->test, $this->santri);

    $this->actingAs($this->santri)->get(route('tests.index'))->assertOk()->assertSee('Minat Bakat');
    $this->actingAs($this->santri)->get(route('attempts.show', $attempt))->assertOk()->assertSee('Saya suka eksperimen.');
});
