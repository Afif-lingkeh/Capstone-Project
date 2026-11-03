<?php
// tests/Feature/LinkDirectory/AdminLinkManagementTest.php

use App\Enums\LinkStatus;
use App\Enums\Role;
use App\Models\Institution;
use App\Models\OfficialLink;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->admin = User::factory()->withRole(Role::PesantrenAdmin)->create();
});

test('santri dan konselor tidak dapat mengelola tautan', function (Role $role) {
    $user = User::factory()->withRole($role)->create();

    $this->actingAs($user)->get(route('admin.links.index'))->assertForbidden();
})->with([Role::Santri, Role::Counselor, Role::Alumni]);

test('tautan baru tersimpan dengan status menunggu verifikasi dan domain ternormalisasi', function () {
    $institution = Institution::factory()->create();

    $this->actingAs($this->admin)->post(route('admin.links.store'), [
        'institution_id' => $institution->id,
        'label' => 'Situs Utama',
        'url' => 'https://WWW.Contoh.ac.id/beranda',
    ])->assertSessionHasNoErrors();

    $link = OfficialLink::firstOrFail();

    expect($link->status)->toBe(LinkStatus::Pending)
        ->and($link->domain)->toBe('contoh.ac.id')
        ->and($link->created_by)->toBe($this->admin->id);
});

test('URL selain http/https ditolak', function () {
    $institution = Institution::factory()->create();

    $this->actingAs($this->admin)->post(route('admin.links.store'), [
        'institution_id' => $institution->id,
        'label' => 'Berbahaya',
        'url' => 'javascript:alert(1)',
    ])->assertSessionHasErrors('url');
});

test('status tidak dapat disisipkan lewat form tambah tautan', function () {
    $institution = Institution::factory()->create();

    $this->actingAs($this->admin)->post(route('admin.links.store'), [
        'institution_id' => $institution->id,
        'label' => 'Situs Utama',
        'url' => 'https://sisip.ac.id',
        'status' => 'verified',
    ]);

    expect(OfficialLink::firstOrFail()->status)->toBe(LinkStatus::Pending);
});

test('verifikasi wajib mencantumkan sumber', function () {
    $link = OfficialLink::factory()->create();

    $this->actingAs($this->admin)
        ->post(route('admin.links.verify', $link), ['verification_source' => ''])
        ->assertSessionHasErrors('verification_source');

    expect($link->fresh()->status)->toBe(LinkStatus::Pending);
});

test('verifikasi mencatat siapa dan kapan', function () {
    $link = OfficialLink::factory()->create();

    $this->actingAs($this->admin)->post(route('admin.links.verify', $link), [
        'verification_source' => 'Dicocokkan dengan profil PDDikti.',
    ])->assertSessionHasNoErrors();

    $link->refresh();

    expect($link->status)->toBe(LinkStatus::Verified)
        ->and($link->verified_by)->toBe($this->admin->id)
        ->and($link->verified_at)->not->toBeNull();
});

test('mengubah URL tautan terverifikasi membatalkan verifikasinya', function () {
    $link = OfficialLink::factory()->verified()->create(['url' => 'https://lama.ac.id']);

    $this->actingAs($this->admin)->put(route('admin.links.update', $link), [
        'institution_id' => $link->institution_id,
        'label' => $link->label,
        'url' => 'https://baru.ac.id',
    ])->assertSessionHasNoErrors();

    $link->refresh();

    expect($link->status)->toBe(LinkStatus::Pending)
        ->and($link->domain)->toBe('baru.ac.id')
        ->and($link->verified_at)->toBeNull();
});

test('mengubah nama tautan tanpa mengubah URL tidak membatalkan verifikasi', function () {
    $link = OfficialLink::factory()->verified()->create();

    $this->actingAs($this->admin)->put(route('admin.links.update', $link), [
        'institution_id' => $link->institution_id,
        'label' => 'Nama Baru',
        'url' => $link->url,
    ]);

    expect($link->fresh()->status)->toBe(LinkStatus::Verified);
});

test('penolakan wajib menyertakan alasan', function () {
    $link = OfficialLink::factory()->create();

    $this->actingAs($this->admin)
        ->post(route('admin.links.reject', $link), ['review_note' => ''])
        ->assertSessionHasErrors('review_note');

    $this->actingAs($this->admin)
        ->post(route('admin.links.reject', $link), ['review_note' => 'Domain tidak cocok dengan PDDikti.']);

    expect($link->fresh()->status)->toBe(LinkStatus::Rejected);
});

test('institusi yang masih memiliki tautan tidak dapat dihapus', function () {
    $link = OfficialLink::factory()->create();

    $this->actingAs($this->admin)
        ->delete(route('admin.institutions.destroy', $link->institution))
        ->assertSessionHasErrors('institution');

    expect(Institution::count())->toBe(1);
});

test('halaman kelola tautan dan institusi dapat dibuka admin', function () {
    $link = OfficialLink::factory()->verified()->create();

    $this->actingAs($this->admin)->get(route('admin.links.index'))->assertOk();
    $this->actingAs($this->admin)->get(route('admin.links.create'))->assertOk();
    $this->actingAs($this->admin)->get(route('admin.links.edit', $link))->assertOk();
    $this->actingAs($this->admin)->get(route('admin.institutions.index'))->assertOk();
    $this->actingAs($this->admin)->get(route('admin.institutions.create'))->assertOk();
});
