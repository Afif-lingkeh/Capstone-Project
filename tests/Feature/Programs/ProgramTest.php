<?php
// tests/Feature/Programs/ProgramTest.php

use App\Enums\Role;
use App\Models\Country;
use App\Models\Partner;
use App\Models\Program;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->santri = User::factory()->withRole(Role::Santri)->create();
    $this->admin = User::factory()->withRole(Role::PesantrenAdmin)->create();
});

test('santri hanya melihat program yang sudah dipublikasikan', function () {
    $published = Program::factory()->create(['title' => 'Magang Kaigo Terbit']);
    $draft = Program::factory()->draft()->create(['title' => 'Program Draf Rahasia']);

    $this->actingAs($this->santri)->get(route('programs.index'))
        ->assertOk()
        ->assertSee('Magang Kaigo Terbit')
        ->assertDontSee('Program Draf Rahasia');

    $this->actingAs($this->santri)->get(route('programs.show', $draft))->assertForbidden();
    $this->actingAs($this->santri)->get(route('programs.show', $published))->assertOk();
});

test('filter negara hanya menampilkan program negara itu', function () {
    $japan = Country::factory()->create(['name' => 'Jepang', 'iso2' => 'JP']);
    $russia = Country::factory()->create(['name' => 'Rusia', 'iso2' => 'RU']);
    Program::factory()->for(Partner::factory()->for($japan))->create(['country_id' => $japan->id, 'title' => 'Program Tokyo']);
    Program::factory()->for(Partner::factory()->for($russia))->create(['country_id' => $russia->id, 'title' => 'Program Kazan']);

    $this->actingAs($this->santri)->get(route('programs.index', ['country' => $japan->id]))
        ->assertSee('Program Tokyo')
        ->assertDontSee('Program Kazan');
});

test('santri dapat menyimpan dan menghapus simpanan program', function () {
    $program = Program::factory()->create();

    $this->actingAs($this->santri)->post(route('programs.bookmark', $program));
    expect($this->santri->bookmarkedPrograms()->count())->toBe(1);

    $this->actingAs($this->santri)->post(route('programs.bookmark', $program));
    expect($this->santri->bookmarkedPrograms()->count())->toBe(0);
});

test('menyatakan minat tercatat sekali dan admin diberi tahu', function () {
    $program = Program::factory()->create();

    $this->actingAs($this->santri)->post(route('programs.interest', $program), ['note' => 'Apakah perlu sertifikat bahasa?']);
    $this->actingAs($this->santri)->post(route('programs.interest', $program));

    expect($program->interests()->count())->toBe(1)
        ->and($this->admin->notifications()->count())->toBe(1);
});

test('program tutup atau lewat tenggat tidak menerima peminat', function () {
    $closed = Program::factory()->closed()->create();
    $expired = Program::factory()->create(['deadline' => now()->subDays(2)]);

    $this->actingAs($this->santri)->post(route('programs.interest', $closed));
    $this->actingAs($this->santri)->post(route('programs.interest', $expired));

    expect($this->santri->programInterests()->count())->toBe(0);
});

test('admin dapat membuat program sebagai draf lalu mempublikasikannya', function () {
    $partner = Partner::factory()->create();

    $this->actingAs($this->admin)->post(route('admin.programs.store'), [
        'partner_id' => $partner->id,
        'country_id' => $partner->country_id,
        'title' => 'Kerja Pengolahan Makanan',
        'type' => 'work',
        'summary' => 'Ringkasan.',
        'requirements' => 'Lulus SMA',
        'costs' => 'Rp [..]',
        'official_contact' => 'Admin pesantren',
        'status' => 'open',
        'publish' => '0',
    ])->assertSessionHasNoErrors();

    $program = Program::firstOrFail();
    expect($program->isPublished())->toBeFalse();

    $this->actingAs($this->admin)->put(route('admin.programs.update', $program), [
        ...$program->only(['partner_id', 'country_id', 'title', 'summary', 'requirements', 'costs', 'official_contact']),
        'type' => 'work', 'status' => 'open', 'publish' => '1',
    ])->assertSessionHasNoErrors();

    expect($program->fresh()->isPublished())->toBeTrue();
});

test('rekap peminat dapat diunduh sebagai CSV dan program berpeminat tidak bisa dihapus', function () {
    $program = Program::factory()->create();
    $program->interests()->create(['user_id' => $this->santri->id]);

    $this->actingAs($this->admin)->get(route('admin.programs.interests', [$program, 'format' => 'csv']))
        ->assertOk()
        ->assertHeader('content-type', 'text/csv; charset=UTF-8');

    $this->actingAs($this->admin)->delete(route('admin.programs.destroy', $program));
    expect(Program::count())->toBe(1);
});

test('santri tidak dapat membuka pengelolaan program', function () {
    $this->actingAs($this->santri)->get(route('admin.programs.index'))->assertForbidden();
});
