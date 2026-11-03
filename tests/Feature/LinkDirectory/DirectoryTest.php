<?php
// tests/Feature/LinkDirectory/DirectoryTest.php

use App\Enums\InstitutionType;
use App\Models\Institution;
use App\Models\OfficialLink;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

test('tamu dapat membuka direktori dan panduan situs palsu', function () {
    $this->get(route('links.index'))->assertOk();
    $this->get(route('links.guide'))->assertOk()->assertSee('Kenali Situs Kampus Palsu');
});

test('direktori hanya menampilkan tautan terverifikasi', function () {
    $institution = Institution::factory()->create(['name' => 'Universitas Uji']);

    OfficialLink::factory()->verified()->for($institution)->create(['url' => 'https://resmi.ac.id']);
    OfficialLink::factory()->for($institution)->create(['url' => 'https://menunggu.ac.id']);
    OfficialLink::factory()->needsReview()->for($institution)->create(['url' => 'https://bermasalah.ac.id']);
    OfficialLink::factory()->rejected()->for($institution)->create(['url' => 'https://palsu-kampus.com']);

    $this->get(route('links.index'))
        ->assertOk()
        ->assertSee('resmi.ac.id')
        ->assertDontSee('menunggu.ac.id')
        ->assertDontSee('bermasalah.ac.id')
        ->assertDontSee('palsu-kampus.com');
});

test('institusi tanpa tautan terverifikasi tidak ditampilkan', function () {
    $institution = Institution::factory()->create(['name' => 'Kampus Tanpa Verifikasi']);
    OfficialLink::factory()->for($institution)->create();

    $this->get(route('links.index'))->assertDontSee('Kampus Tanpa Verifikasi');
});

test('direktori dapat difilter berdasarkan kategori', function () {
    OfficialLink::factory()->verified()
        ->for(Institution::factory()->ofType(InstitutionType::Kedinasan)->state(['name' => 'Sekolah Kedinasan Uji']))
        ->create();
    OfficialLink::factory()->verified()
        ->for(Institution::factory()->ofType(InstitutionType::Pts)->state(['name' => 'Kampus Swasta Uji']))
        ->create();

    $this->get(route('links.index', ['type' => 'kedinasan']))
        ->assertSee('Sekolah Kedinasan Uji')
        ->assertDontSee('Kampus Swasta Uji');
});

test('santri yang login melihat direktori di dalam layout aplikasi', function () {
    $this->seed(RolePermissionSeeder::class);
    $santri = User::factory()->santri()->create();

    $this->actingAs($santri)->get(route('links.index'))
        ->assertOk()
        ->assertSee(route('santri.dashboard'), false);
});
