<?php
// tests/Feature/SmokeTest.php
// Membuka setiap halaman utama untuk tiap peran dengan data contoh lengkap, memastikan tidak ada halaman yang error.

use App\Enums\Role;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Http\UploadedFile;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
});

test('semua halaman publik dapat dibuka', function (string $route) {
    $this->get(route($route))->assertOk();
})->with(['home', 'privacy', 'links.index', 'links.guide', 'login', 'alumni.register']);

test('semua menu setiap peran dapat dibuka', function (string $username) {
    $user = User::where('username', $username)->firstOrFail();

    foreach (\App\Support\Navigation::for($user) as $item) {
        $this->actingAs($user)->get(route($item['route']))->assertOk();
    }
    $this->actingAs($user)->get(route('profile.edit'))->assertOk();
    $this->actingAs($user)->get(route('notifications.index'))->assertOk();
})->with(['superadmin', 'admin', 'konselor', 'pembimbing', 'alumni', '20250001']);

test('halaman detail dengan data contoh dapat dibuka', function () {
    $santri = User::where('username', '20250001')->firstOrFail();
    $admin = User::where('username', 'admin')->firstOrFail();
    $program = \App\Models\Program::firstOrFail();
    $article = \App\Models\Article::firstOrFail();
    $alumni = \App\Models\AlumniProfile::firstOrFail();

    $this->actingAs($santri)->get(route('programs.show', $program))->assertOk();
    $this->actingAs($santri)->get(route('articles.show', $article))->assertOk();
    $this->actingAs($santri)->get(route('alumni.show', $alumni))->assertOk()->assertSee('Ajukan konsultasi');
    $this->actingAs($santri)->get(route('counseling.create'))->assertOk();
    $this->actingAs($admin)->get(route('admin.programs.edit', $program))->assertOk();
    $this->actingAs($admin)->get(route('admin.programs.interests', $program))->assertOk();
    $this->actingAs($admin)->get(route('admin.partners.index'))->assertOk();
    $this->actingAs($admin)->get(route('admin.articles.edit', $article))->assertOk();
    $this->actingAs($admin)->get(route('admin.tests.edit', \App\Models\PotentialTest::firstOrFail()))->assertOk();
});

test('super admin mengimpor santri dari CSV; baris tidak valid dilewati', function () {
    $super = User::where('username', 'superadmin')->firstOrFail();
    $csv = "nis;nama;jenjang;kelas;jenis_kelamin;tahun_masuk;nama_wali\n"
        ."20259001;Ahmad Fauzi;sma;10A;L;2025;Bapak Fauzan\n"
        ."20250001;Duplikat NIS;smp;7A;L;2025;\n"
        ."20259002;Siti Aminah;SMP;8B;p;2024;\n";
    $file = UploadedFile::fake()->createWithContent('santri.csv', $csv);

    $this->actingAs($super)->post(route('admin.users.import.store'), ['file' => $file])
        ->assertOk()->assertSee('Ahmad Fauzi')->assertSee('Baris 3');

    $siti = User::where('username', '20259002')->firstOrFail();
    expect($siti->hasRole(Role::Santri->value))->toBeTrue()
        ->and($siti->santriProfile->class_name)->toBe('8B')
        ->and(User::where('name', 'Duplikat NIS')->exists())->toBeFalse();

    $admin = User::where('username', 'admin')->firstOrFail();
    $this->actingAs($admin)->get(route('admin.users.import'))->assertForbidden();
});
