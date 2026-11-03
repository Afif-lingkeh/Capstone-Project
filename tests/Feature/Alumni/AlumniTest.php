<?php
// tests/Feature/Alumni/AlumniTest.php

use App\Enums\Role;
use App\Models\AlumniProfile;
use App\Models\Consultation;
use App\Models\Country;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->country = Country::factory()->create(['name' => 'Slovakia', 'iso2' => 'SK']);
    $this->santri = User::factory()->withRole(Role::Santri)->create();
});

function alumnus(bool $verified = true, array $attrs = []): User
{
    $user = User::factory()->withRole(Role::Alumni)->create(['name' => 'Rizki Maulana']);
    $profile = $user->alumniProfile()->create([
        'graduation_year' => 2019, 'institution_name' => 'Comenius University', 'country_id' => test()->country->id,
        'whatsapp' => '081234567890', 'public_email' => 'rizki@contoh.id', 'show_email' => false, ...$attrs,
    ]);
    if ($verified) {
        $profile->forceFill(['verification_status' => 'verified', 'verified_at' => now()])->save();
    }

    return $user->fresh('alumniProfile');
}

test('alumni mendaftar sendiri dan menunggu verifikasi', function () {
    $admin = User::factory()->withRole(Role::PesantrenAdmin)->create();

    $this->post(route('alumni.register'), [
        'name' => 'Aisyah Rahma', 'email' => 'aisyah@contoh.id', 'username' => 'aisyah', 'gender' => 'P',
        'graduation_year' => 2020, 'institution_name' => 'Universitas Al-Azhar', 'country_id' => $this->country->id,
        'password' => 'rahasia123', 'password_confirmation' => 'rahasia123',
    ])->assertRedirect(route('alumni.dashboard'));

    $user = User::where('username', 'aisyah')->firstOrFail();
    expect($user->hasRole('alumni'))->toBeTrue()
        ->and($user->alumniProfile->verification_status)->toBe('pending')
        ->and($admin->notifications()->count())->toBe(1);
});

test('direktori hanya menampilkan alumni terverifikasi dengan nama singkat', function () {
    alumnus(true);
    $pending = alumnus(false, ['institution_name' => 'Kampus Belum Diverifikasi']);

    $this->actingAs($this->santri)->get(route('alumni.index'))
        ->assertOk()
        ->assertSee('Rizki M.')
        ->assertDontSee('Kampus Belum Diverifikasi');
    $this->actingAs($this->santri)->get(route('alumni.show', $pending->alumniProfile))->assertNotFound();
});

test('nomor WhatsApp tidak pernah tampil ke santri dan email hanya bila diizinkan', function () {
    $alumni = alumnus(true);

    $this->actingAs($this->santri)->get(route('alumni.show', $alumni->alumniProfile))
        ->assertOk()->assertDontSee('081234567890')->assertDontSee('rizki@contoh.id');

    $alumni->alumniProfile->update(['show_email' => true]);
    $this->actingAs($this->santri)->get(route('alumni.show', $alumni->alumniProfile))->assertSee('rizki@contoh.id');
});

test('santri mengajukan konsultasi, alumni menerima, dan hanya kedua pihak yang dapat membaca', function () {
    $alumni = alumnus(true);
    $other = User::factory()->withRole(Role::Santri)->create();

    $this->actingAs($this->santri)->post(route('consultations.store', $alumni->alumniProfile), ['subject' => 'Beasiswa ke Eropa', 'body' => 'Kak, bagaimana persiapan bahasanya?']);
    $consultation = Consultation::firstOrFail();

    expect($consultation->status)->toBe('pending')->and($alumni->notifications()->count())->toBe(1);

    // Belum diterima: santri belum bisa mengirim pesan lanjutan.
    $this->actingAs($this->santri)->post(route('consultations.message', $consultation), ['body' => 'Halo?'])->assertForbidden();

    $this->actingAs($alumni)->post(route('consultations.respond', $consultation), ['decision' => 'accepted']);
    $this->actingAs($alumni)->post(route('consultations.message', $consultation), ['body' => 'Mulai dari IELTS, ya.']);

    expect($consultation->messages()->count())->toBe(2);
    $this->actingAs($this->santri)->get(route('consultations.show', $consultation))->assertOk()->assertSee('Mulai dari IELTS');
    $this->actingAs($other)->get(route('consultations.show', $consultation))->assertForbidden();
});

test('alumni yang belum terverifikasi tidak bisa diajak konsultasi', function () {
    $pending = alumnus(false);

    $this->actingAs($this->santri)->post(route('consultations.store', $pending->alumniProfile), ['subject' => 'x', 'body' => 'Pertanyaan yang cukup panjang.'])->assertForbidden();
});

test('laporan diteruskan ke admin dan admin yang meninjau memberi tahu kedua pihak', function () {
    $alumni = alumnus(true);
    $admin = User::factory()->withRole(Role::PesantrenAdmin)->create();
    $consultation = Consultation::create(['santri_id' => $this->santri->id, 'alumni_id' => $alumni->id, 'subject' => 'Tanya', 'status' => 'accepted']);

    $this->actingAs($this->santri)->post(route('consultations.report', $consultation), ['reason' => 'Meminta nomor HP pribadi.']);
    expect($admin->notifications()->count())->toBe(1);

    $this->actingAs($admin)->get(route('admin.consultations.show', $consultation))->assertOk()->assertSee('Meminta nomor HP pribadi.');
    expect($this->santri->notifications()->where('data->title', 'Percakapan ditinjau Admin Pesantren')->count())->toBe(1)
        ->and($alumni->notifications()->where('data->title', 'Percakapan ditinjau Admin Pesantren')->count())->toBe(1);

    $this->actingAs($admin)->post(route('admin.consultations.resolve', $consultation->reports()->first()), ['resolution_note' => 'Diberi peringatan.', 'close_thread' => 1]);
    expect($consultation->fresh()->status)->toBe('closed');
});

test('admin memverifikasi atau menolak alumni dengan alasan', function () {
    $admin = User::factory()->withRole(Role::PesantrenAdmin)->create();
    $alumni = alumnus(false);

    $this->actingAs($admin)->post(route('admin.alumni.decide', $alumni->alumniProfile), ['decision' => 'rejected'])->assertSessionHasErrors('rejection_note');
    $this->actingAs($admin)->post(route('admin.alumni.decide', $alumni->alumniProfile), ['decision' => 'verified']);

    expect($alumni->alumniProfile->fresh()->isVerified())->toBeTrue();
});

test('halaman fase 8 dapat dibuka', function () {
    $alumni = alumnus(true);
    $admin = User::factory()->withRole(Role::PesantrenAdmin)->create();

    $this->get(route('alumni.register'))->assertOk();
    $this->actingAs($alumni)->get(route('alumni.dashboard'))->assertOk();
    $this->actingAs($alumni)->get(route('alumni.profile.edit'))->assertOk();
    $this->actingAs($alumni)->get(route('consultations.index'))->assertOk();
    $this->actingAs($this->santri)->get(route('consultations.index'))->assertOk();
    $this->actingAs($admin)->get(route('admin.alumni.index'))->assertOk();
    $this->actingAs($admin)->get(route('admin.consultations.index'))->assertOk();
    $this->actingAs($this->santri)->get(route('admin.alumni.index'))->assertForbidden();
});
