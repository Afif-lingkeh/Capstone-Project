<?php
// tests/Feature/LinkDirectory/LinkCheckerTest.php

use App\Enums\LinkCheckResult;
use App\Enums\LinkStatus;
use App\Models\OfficialLink;
use App\Services\LinkChecker\LinkCheckerService;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    // Tidak boleh ada request sungguhan ke internet saat test.
    Http::preventStrayRequests();
    config(['peduli.link_check.delay_ms' => 0]);
});

function checkLink(OfficialLink $link): App\Models\LinkCheckLog
{
    return app(LinkCheckerService::class)->check($link);
}

test('situs yang normal tetap terverifikasi', function () {
    Http::fake(['*' => Http::response('<html>ok</html>', 200)]);
    $link = OfficialLink::factory()->verified()->create(['url' => 'https://www.normal.ac.id']);

    $log = checkLink($link);

    expect($log->result)->toBe(LinkCheckResult::Ok)
        ->and($log->http_status)->toBe(200)
        ->and($link->fresh()->status)->toBe(LinkStatus::Verified)
        ->and($link->fresh()->last_checked_at)->not->toBeNull();
});

test('halaman 404 langsung ditandai perlu ditinjau', function () {
    Http::fake(['*' => Http::response('', 404)]);
    $link = OfficialLink::factory()->verified()->create(['url' => 'https://mati.ac.id']);

    expect(checkLink($link)->result)->toBe(LinkCheckResult::Dead)
        ->and($link->fresh()->status)->toBe(LinkStatus::NeedsReview);
});

test('redirect ke domain lain ditandai domain berubah', function () {
    Http::fake([
        'kampus.ac.id*' => Http::response('', 301, ['Location' => 'https://judi-online.example/promo']),
        'judi-online.example*' => Http::response('promo', 200),
    ]);
    $link = OfficialLink::factory()->verified()->create(['url' => 'https://kampus.ac.id']);

    $log = checkLink($link);

    expect($log->result)->toBe(LinkCheckResult::DomainChanged)
        ->and($log->final_url)->toBe('https://judi-online.example/promo')
        ->and($link->fresh()->status)->toBe(LinkStatus::NeedsReview);
});

test('redirect di dalam domain yang sama dianggap normal', function () {
    Http::fake([
        'http://kampus.ac.id*' => Http::response('', 301, ['Location' => 'https://www.kampus.ac.id/']),
        'https://www.kampus.ac.id/' => Http::response('', 302, ['Location' => '/beranda']),
        'https://www.kampus.ac.id/beranda' => Http::response('ok', 200),
    ]);
    $link = OfficialLink::factory()->verified()->create(['url' => 'http://kampus.ac.id']);

    $log = checkLink($link);

    expect($log->result)->toBe(LinkCheckResult::Ok)
        ->and($log->final_url)->toBe('https://www.kampus.ac.id/beranda')
        ->and($link->fresh()->status)->toBe(LinkStatus::Verified);
});

test('gangguan sekali tidak langsung menyembunyikan tautan, dua kali berturut-turut iya', function () {
    Http::fake(['*' => Http::response('', 503)]);
    $link = OfficialLink::factory()->verified()->create(['url' => 'https://lambat.ac.id']);

    expect(checkLink($link)->result)->toBe(LinkCheckResult::Error)
        ->and($link->fresh()->status)->toBe(LinkStatus::Verified);

    checkLink($link->fresh());

    expect($link->fresh()->status)->toBe(LinkStatus::NeedsReview);
});

test('situs yang tidak bisa dihubungi dicatat sebagai gagal diakses', function () {
    Http::fake(['*' => Http::failedConnection()]);
    $link = OfficialLink::factory()->verified()->create(['url' => 'https://tidak-ada.ac.id']);

    $log = checkLink($link);

    expect($log->result)->toBe(LinkCheckResult::Error)
        ->and($log->http_status)->toBeNull()
        ->and($log->error_message)->not->toBeNull();
});

test('perintah links:check hanya mengecek tautan terverifikasi yang sudah waktunya', function () {
    Http::fake(['*' => Http::response('ok', 200)]);

    $due = OfficialLink::factory()->verified()->create(['last_checked_at' => now()->subDays(8)]);
    $neverChecked = OfficialLink::factory()->verified()->create(['last_checked_at' => null]);
    $recent = OfficialLink::factory()->verified()->create(['last_checked_at' => now()->subDay()]);
    $pending = OfficialLink::factory()->create();

    $this->artisan('links:check')->assertSuccessful();

    expect($due->checkLogs()->count())->toBe(1)
        ->and($neverChecked->checkLogs()->count())->toBe(1)
        ->and($recent->checkLogs()->count())->toBe(0)
        ->and($pending->checkLogs()->count())->toBe(0);
});

test('opsi --all mengecek semua tautan terverifikasi', function () {
    Http::fake(['*' => Http::response('ok', 200)]);
    $recent = OfficialLink::factory()->verified()->create(['last_checked_at' => now()->subDay()]);

    $this->artisan('links:check --all')->assertSuccessful();

    expect($recent->checkLogs()->count())->toBe(1);
});
