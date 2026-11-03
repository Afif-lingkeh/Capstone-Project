<?php
// tests/Feature/Guidance/GuidanceTest.php

use App\Enums\BookingStatus;
use App\Enums\Role;
use App\Models\Article;
use App\Models\GuidanceSlot;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->santri = User::factory()->withRole(Role::Santri)->create();
    $this->advisor = User::factory()->withRole(Role::AcademicAdvisor)->create();
});

test('artikel draf tidak tampil dan tidak bisa dibuka santri', function () {
    Article::factory()->create(['title' => 'Panduan SNBP Terbit']);
    $draft = Article::factory()->draft()->create(['title' => 'Draf Rahasia']);

    $this->actingAs($this->santri)->get(route('articles.index'))->assertOk()->assertSee('Panduan SNBP Terbit')->assertDontSee('Draf Rahasia');
    $this->actingAs($this->santri)->get(route('articles.show', $draft))->assertForbidden();
});

test('isi artikel dirender sebagai markdown tanpa HTML mentah', function () {
    $article = Article::factory()->create(['body' => "## Jadwal\n\n**Penting**\n\n<script>alert(1)</script>"]);

    $this->actingAs($this->santri)->get(route('articles.show', $article))
        ->assertSee('<h2>Jadwal</h2>', false)
        ->assertSee('<strong>Penting</strong>', false)
        ->assertDontSee('<script>alert(1)</script>', false);
});

test('pembimbing membuka slot berulang dan slot bertabrakan dilewati', function () {
    $date = now()->addDays(2)->toDateString();
    $payload = ['date' => $date, 'start_time' => '09:00', 'duration' => 30, 'count' => 3, 'repeat_weeks' => 1, 'mode' => 'online'];

    $this->actingAs($this->advisor)->post(route('advisor.slots.store'), $payload)->assertSessionHasNoErrors();
    expect($this->advisor->guidanceSlots()->count())->toBe(6);

    $this->actingAs($this->advisor)->post(route('advisor.slots.store'), $payload);
    expect($this->advisor->guidanceSlots()->count())->toBe(6);
});

test('santri memesan slot kosong dan pembimbing diberi tahu', function () {
    $slot = GuidanceSlot::factory()->for($this->advisor, 'advisor')->create();

    $this->actingAs($this->santri)->post(route('guidance.book'), ['slot_id' => $slot->id, 'topic' => 'Persiapan SNBP'])
        ->assertRedirect(route('guidance.index'));

    expect($slot->activeBooking()->first()->santri_id)->toBe($this->santri->id)
        ->and($this->advisor->notifications()->count())->toBe(1);
});

test('slot yang sudah dipesan tidak bisa dipesan santri lain', function () {
    $slot = GuidanceSlot::factory()->for($this->advisor, 'advisor')->create();
    $other = User::factory()->withRole(Role::Santri)->create();

    $this->actingAs($this->santri)->post(route('guidance.book'), ['slot_id' => $slot->id, 'topic' => 'A']);
    $this->actingAs($other)->post(route('guidance.book'), ['slot_id' => $slot->id, 'topic' => 'B']);

    expect($slot->bookings()->count())->toBe(1);
});

test('slot yang sudah lewat tidak bisa dipesan', function () {
    $slot = GuidanceSlot::factory()->past()->for($this->advisor, 'advisor')->create();

    $this->actingAs($this->santri)->post(route('guidance.book'), ['slot_id' => $slot->id, 'topic' => 'A']);

    expect($slot->bookings()->count())->toBe(0);
});

test('santri membatalkan pesanannya sendiri, bukan milik orang lain', function () {
    $slot = GuidanceSlot::factory()->for($this->advisor, 'advisor')->create();
    $booking = $slot->bookings()->create(['santri_id' => $this->santri->id, 'topic' => 'A', 'status' => BookingStatus::Booked]);
    $other = User::factory()->withRole(Role::Santri)->create();

    $this->actingAs($other)->patch(route('guidance.cancel', $booking))->assertForbidden();
    $this->actingAs($this->santri)->patch(route('guidance.cancel', $booking));

    expect($booking->fresh()->status)->toBe(BookingStatus::Cancelled)
        ->and(GuidanceSlot::available()->count())->toBe(1);
});

test('hanya pembimbing pemilik slot yang mencatat hasil sesi', function () {
    $slot = GuidanceSlot::factory()->past()->for($this->advisor, 'advisor')->create();
    $booking = $slot->bookings()->create(['santri_id' => $this->santri->id, 'topic' => 'A', 'status' => BookingStatus::Booked]);
    $otherAdvisor = User::factory()->withRole(Role::AcademicAdvisor)->create();

    $this->actingAs($otherAdvisor)->patch(route('advisor.sessions.update', $booking), ['status' => 'completed', 'outcome_notes' => 'x'])->assertForbidden();
    $this->actingAs($this->advisor)->patch(route('advisor.sessions.update', $booking), ['status' => 'completed', 'outcome_notes' => 'Fokus SNBT Saintek.']);

    expect($booking->fresh()->status)->toBe(BookingStatus::Completed)
        ->and($this->santri->notifications()->count())->toBe(1);
});

test('halaman fase 5 dapat dibuka', function () {
    $admin = User::factory()->withRole(Role::PesantrenAdmin)->create();
    GuidanceSlot::factory()->for($this->advisor, 'advisor')->create();

    $this->actingAs($this->santri)->get(route('guidance.index'))->assertOk();
    $this->actingAs($this->advisor)->get(route('advisor.slots.index'))->assertOk();
    $this->actingAs($this->advisor)->get(route('advisor.sessions.index'))->assertOk();
    $this->actingAs($admin)->get(route('admin.articles.index'))->assertOk();
    $this->actingAs($admin)->get(route('admin.articles.create'))->assertOk();
    $this->actingAs($admin)->get(route('admin.majors.index'))->assertOk();
    $this->actingAs($this->santri)->get(route('admin.majors.index'))->assertForbidden();
});
