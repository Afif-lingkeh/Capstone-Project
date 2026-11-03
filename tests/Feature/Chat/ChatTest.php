<?php
// tests/Feature/Chat/ChatTest.php

use App\Enums\LinkStatus;
use App\Enums\Role;
use App\Models\ChatMessage;
use App\Models\CrisisAlert;
use App\Models\GuardianConsent;
use App\Models\Institution;
use App\Models\OfficialLink;
use App\Models\User;
use App\Services\ChatAssistant;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    config(['services.anthropic.key' => 'test-key']);
    $this->santri = User::factory()->withRole(Role::Santri)->create();
    GuardianConsent::create(['santri_id' => $this->santri->id, 'guardian_name' => 'Ayah', 'relationship' => 'Ayah', 'method' => 'tertulis', 'consented_at' => now(), 'policy_version' => '1.0']);
});

function fakeAssistant(array $reply = []): void
{
    test()->mock(ChatAssistant::class, function ($mock) use ($reply) {
        $mock->shouldReceive('isConfigured')->andReturn(true);
        $mock->shouldReceive('reply')->andReturn([
            'text' => 'Situs resmi UGM ada di bawah ini, ya.', 'links' => [['name' => 'Universitas Gadjah Mada', 'domain' => 'ugm.ac.id', 'url' => 'https://ugm.ac.id']],
            'is_crisis' => false, 'input_tokens' => 100, 'output_tokens' => 50, ...$reply,
        ]);
    });
}

test('asisten AI terkunci tanpa persetujuan wali', function () {
    $other = User::factory()->withRole(Role::Santri)->create();

    $this->actingAs($other)->get(route('chat.index'))->assertOk()->assertSee('Asisten AI belum dibuka');
    $this->actingAs($other)->post(route('chat.send'), ['message' => 'Halo'])->assertForbidden();
});

test('pertanyaan dan jawaban tersimpan beserta kartu tautan resmi', function () {
    fakeAssistant();

    $this->actingAs($this->santri)->post(route('chat.send'), ['message' => 'Link UGM yang resmi?'])->assertRedirect();

    $answer = ChatMessage::where('role', 'assistant')->firstOrFail();
    expect(ChatMessage::count())->toBe(2)->and($answer->links[0]['domain'])->toBe('ugm.ac.id');
    $this->actingAs($this->santri)->get(route('chat.show', $answer->conversation))->assertOk()->assertSee('ugm.ac.id');
});

test('tanda krisis ditangani tanpa memanggil model dan konselor diberi tahu', function () {
    $counselor = User::factory()->withRole(Role::Counselor)->create();

    // Layanan asli: deteksi kata kunci terjadi sebelum panggilan API, jadi tidak ada permintaan jaringan.
    $this->actingAs($this->santri)->post(route('chat.send'), ['message' => 'Aku capek, rasanya ingin mati saja.'])->assertSessionHas('crisis', true);

    expect(CrisisAlert::where('source', 'chat')->count())->toBe(1)
        ->and(ChatMessage::where('is_crisis', true)->count())->toBe(2)
        ->and($counselor->notifications()->count())->toBe(1);
});

test('batas pesan harian berlaku', function () {
    fakeAssistant();
    config(['peduli.chat.daily_limit' => 1]);

    $this->actingAs($this->santri)->post(route('chat.send'), ['message' => 'Pertanyaan satu']);
    $this->actingAs($this->santri)->post(route('chat.send'), ['message' => 'Pertanyaan dua'])->assertSessionHas('warning');

    expect(ChatMessage::where('role', 'user')->count())->toBe(1);
});

test('tanpa kunci API, santri diberi tahu fitur belum aktif', function () {
    config(['services.anthropic.key' => null]);

    $this->actingAs($this->santri)->get(route('chat.index'))->assertSee('Asisten AI belum diaktifkan');
    $this->actingAs($this->santri)->post(route('chat.send'), ['message' => 'Halo'])->assertSessionHas('warning');
});

test('santri menilai jawaban miliknya sendiri saja', function () {
    fakeAssistant();
    $this->actingAs($this->santri)->post(route('chat.send'), ['message' => 'Halo']);
    $answer = ChatMessage::where('role', 'assistant')->firstOrFail();
    $other = User::factory()->withRole(Role::Santri)->create();

    $this->actingAs($other)->post(route('chat.rate', $answer), ['rating' => -1])->assertForbidden();
    $this->actingAs($this->santri)->post(route('chat.rate', $answer), ['rating' => 1]);

    expect($answer->fresh()->rating)->toBe(1);
});

test('tool tautan resmi hanya mengembalikan tautan terverifikasi', function () {
    $institution = Institution::factory()->create(['name' => 'Universitas Contoh']);
    OfficialLink::factory()->for($institution)->create(['status' => LinkStatus::Verified, 'url' => 'https://contoh.ac.id', 'domain' => 'contoh.ac.id']);
    OfficialLink::factory()->for($institution)->create(['status' => LinkStatus::Pending, 'url' => 'https://palsu-contoh.com', 'domain' => 'palsu-contoh.com']);

    $assistant = app(ChatAssistant::class);
    $method = new ReflectionMethod($assistant, 'runTool');
    $links = [];
    $isCrisis = false;
    $args = ['search_official_links', ['query' => 'Contoh'], $this->santri, $this->santri->chatConversations()->create(['title' => 't']), &$links, &$isCrisis];
    $result = $method->invokeArgs($assistant, $args);

    expect(collect($result['results'])->pluck('domain')->all())->toBe(['contoh.ac.id'])
        ->and(array_keys($links))->toBe(['https://contoh.ac.id']);
});

test('evaluasi asisten hanya untuk super admin dan menyembunyikan percakapan krisis', function () {
    $conversation = $this->santri->chatConversations()->create(['title' => 'x']);
    $conversation->messages()->create(['role' => 'user', 'content' => 'Pertanyaan biasa tentang SNBP']);
    $conversation->messages()->create(['role' => 'assistant', 'content' => 'Jawaban biasa tentang SNBP']);
    $crisis = $this->santri->chatConversations()->create(['title' => 'y']);
    $crisis->messages()->create(['role' => 'user', 'content' => 'Isi krisis rahasia', 'is_crisis' => true]);
    $crisis->messages()->create(['role' => 'assistant', 'content' => 'Balasan krisis rahasia', 'is_crisis' => true]);

    $super = User::factory()->withRole(Role::SuperAdmin)->create();
    $admin = User::factory()->withRole(Role::PesantrenAdmin)->create();

    $this->actingAs($super)->get(route('admin.chat-logs.index'))->assertOk()->assertSee('Jawaban biasa tentang SNBP')->assertDontSee('krisis rahasia');
    $this->actingAs($admin)->get(route('admin.chat-logs.index'))->assertForbidden();
});
