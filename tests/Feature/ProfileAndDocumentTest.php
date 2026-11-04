<?php
// tests/Feature/ProfileAndDocumentTest.php

use App\Enums\DocumentStatus;
use App\Enums\Role;
use App\Models\Document;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    Storage::fake('local');
    $this->santri = User::factory()->withRole(Role::Santri)->create();
    $this->santri->santriProfile()->create(['nis' => '20259999', 'level' => 'sma', 'class_name' => '11A', 'entry_year' => 2024]);
    $this->admin = User::factory()->withRole(Role::PesantrenAdmin)->create();
});

/** PNG asli berukuran $w×$h tanpa ekstensi GD (agar aturan image & dimensions benar-benar diuji). */
function realPng(int $w = 200, int $h = 200, string $name = 'foto.png'): UploadedFile
{
    $chunk = fn (string $type, string $data) => pack('N', strlen($data)).$type.$data.pack('N', crc32($type.$data));
    $raw = str_repeat("\0".str_repeat("\xFF\xFF\xFF", $w), $h);
    $png = "\x89PNG\r\n\x1a\n".$chunk('IHDR', pack('NNCCCCC', $w, $h, 8, 2, 0, 0, 0)).$chunk('IDAT', gzcompress($raw)).$chunk('IEND', '');

    return UploadedFile::fake()->createWithContent($name, $png);
}

function uploadDoc($test, User $as, array $overrides = []): Document
{
    $test->actingAs($as)->post(route('documents.store'), [
        'file' => UploadedFile::fake()->createWithContent('rapor.pdf', "%PDF-1.4\n1 0 obj<<>>endobj\ntrailer<<>>\n%%EOF"),
        'category' => 'rapor', 'title' => 'Rapor semester 3', ...$overrides,
    ]);

    return Document::latest('id')->firstOrFail();
}

test('santri mengunggah, mengganti, dan menghapus foto profil', function () {
    $this->actingAs($this->santri)->post(route('avatar.update'), ['avatar' => realPng()])->assertSessionHasNoErrors();
    $first = $this->santri->fresh()->avatar_path;
    Storage::disk('local')->assertExists($first);

    $this->actingAs($this->santri)->post(route('avatar.update'), ['avatar' => realPng(300, 300)]);
    Storage::disk('local')->assertMissing($first);

    $this->actingAs($this->santri)->delete(route('avatar.destroy'));
    expect($this->santri->fresh()->avatar_path)->toBeNull();
});

test('foto profil ditolak bila bukan gambar atau terlalu kecil', function () {
    $this->actingAs($this->santri)->post(route('avatar.update'), ['avatar' => UploadedFile::fake()->createWithContent('foto.png', '<?php echo 1;')])->assertSessionHasErrors('avatar');
    $this->actingAs($this->santri)->post(route('avatar.update'), ['avatar' => realPng(40, 40)])->assertSessionHasErrors('avatar');
});

test('foto santri hanya terlihat oleh dirinya dan staf, bukan santri lain atau alumni', function () {
    $this->actingAs($this->santri)->post(route('avatar.update'), ['avatar' => realPng()]);
    $url = route('avatars.show', $this->santri);

    $this->actingAs($this->santri)->get($url)->assertOk();
    $this->actingAs($this->admin)->get($url)->assertOk();
    $this->actingAs(User::factory()->withRole(Role::Counselor)->create())->get($url)->assertOk();
    $this->actingAs(User::factory()->withRole(Role::Santri)->create())->get($url)->assertNotFound();
    $this->actingAs(User::factory()->withRole(Role::Alumni)->create())->get($url)->assertNotFound();
});

test('santri mengisi cita-cita, tetapi tidak bisa mengubah NIS atau kelas', function () {
    $this->actingAs($this->santri)->patch(route('profile.update'), [
        'name' => $this->santri->name, 'aspiration' => 'Kedokteran UGM', 'nis' => '11111111', 'class_name' => '12Z',
    ])->assertSessionHasNoErrors();

    $profile = $this->santri->santriProfile->fresh();
    expect($profile->aspiration)->toBe('Kedokteran UGM')->and($profile->nis)->toBe('20259999')->and($profile->class_name)->toBe('11A');
});

test('santri mengunggah berkas dan admin diberi tahu', function () {
    $document = uploadDoc($this, $this->santri);

    Storage::disk('local')->assertExists($document->path);
    expect($document->owner_id)->toBe($this->santri->id)
        ->and($document->status)->toBe(DocumentStatus::Pending)
        ->and($document->path)->not->toContain('rapor.pdf')
        ->and($this->admin->notifications()->count())->toBe(1);
});

test('jenis file berbahaya dan file terlalu besar ditolak', function () {
    $this->actingAs($this->santri)->post(route('documents.store'), [
        'file' => UploadedFile::fake()->createWithContent('rapor.php', '<?php system($_GET["c"]); ?>'), 'category' => 'rapor', 'title' => 'x',
    ])->assertSessionHasErrors('file');

    $this->actingAs($this->santri)->post(route('documents.store'), [
        'file' => UploadedFile::fake()->create('besar.pdf', 6000, 'application/pdf'), 'category' => 'rapor', 'title' => 'x',
    ])->assertSessionHasErrors('file');

    expect(Document::count())->toBe(0);
});

test('berkas hanya bisa diunduh pemilik, admin, dan pembimbing santri itu', function () {
    $document = uploadDoc($this, $this->santri);
    $url = route('documents.download', $document);
    $advisor = User::factory()->withRole(Role::AcademicAdvisor)->create();
    $advisor->advisees()->attach($this->santri);

    $this->actingAs($this->santri)->get($url)->assertOk()->assertDownload('rapor.pdf');
    $this->actingAs($this->admin)->get($url)->assertOk();
    $this->actingAs($advisor)->get($url)->assertOk();
    $this->actingAs(User::factory()->withRole(Role::AcademicAdvisor)->create())->get($url)->assertForbidden();
    $this->actingAs(User::factory()->withRole(Role::Santri)->create())->get($url)->assertForbidden();
    $this->actingAs(User::factory()->withRole(Role::Counselor)->create())->get($url)->assertForbidden();
});

test('berkas yang sudah diperiksa tidak bisa dihapus santri, admin tetap bisa', function () {
    $document = uploadDoc($this, $this->santri);
    $this->actingAs($this->admin)->patch(route('admin.documents.review', $document), ['status' => 'reviewed']);

    $this->actingAs($this->santri)->delete(route('documents.destroy', $document))->assertForbidden();
    $this->actingAs($this->admin)->delete(route('documents.destroy', $document));

    expect(Document::count())->toBe(0);
    Storage::disk('local')->assertMissing($document->path);
});

test('admin meminta perbaikan dengan catatan dan santri diberi tahu', function () {
    $document = uploadDoc($this, $this->santri);

    $this->actingAs($this->admin)->patch(route('admin.documents.review', $document), ['status' => 'needs_fix'])->assertSessionHasErrors('review_note');
    $this->actingAs($this->admin)->patch(route('admin.documents.review', $document), ['status' => 'needs_fix', 'review_note' => 'Halaman 2 buram']);

    expect($document->fresh()->status)->toBe(DocumentStatus::NeedsFix)
        ->and($this->santri->notifications()->count())->toBe(1);
    $this->actingAs($this->santri)->get(route('documents.index'))->assertSee('Halaman 2 buram');
});

test('admin mengunggahkan berkas untuk santri', function () {
    $this->actingAs($this->admin)->post(route('admin.documents.store'), [
        'owner_id' => $this->santri->id, 'category' => 'surat', 'title' => 'Surat keterangan aktif',
        'file' => UploadedFile::fake()->createWithContent('surat.pdf', "%PDF-1.4\n%%EOF"),
    ])->assertSessionHasNoErrors();

    $document = $this->santri->documents()->firstOrFail();
    expect($document->uploaded_by)->toBe($this->admin->id)->and($document->status)->toBe(DocumentStatus::Reviewed);
    $this->actingAs($this->santri)->delete(route('documents.destroy', $document))->assertForbidden();
});

test('halaman profil dan berkas dapat dibuka; santri tidak bisa membuka berkas admin', function () {
    uploadDoc($this, $this->santri);

    $this->actingAs($this->santri)->get(route('profile.edit'))->assertOk()->assertSee('20259999');
    $this->actingAs($this->santri)->get(route('documents.index'))->assertOk()->assertSee('Rapor semester 3');
    $this->actingAs($this->admin)->get(route('admin.documents.index'))->assertOk()->assertSee('Rapor semester 3');
    $this->actingAs($this->santri)->get(route('admin.documents.index'))->assertForbidden();
});
