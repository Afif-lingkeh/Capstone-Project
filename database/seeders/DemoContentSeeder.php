<?php
// database/seeders/DemoContentSeeder.php

namespace Database\Seeders;

use App\Enums\ArticleCategory;
use App\Enums\Pathway;
use App\Enums\ProgramStatus;
use App\Enums\ProgramType;
use App\Enums\SessionMode;
use App\Models\Article;
use App\Models\Country;
use App\Models\CounselorShift;
use App\Models\GuardianConsent;
use App\Models\Major;
use App\Models\Partner;
use App\Models\PotentialTest;
use App\Models\User;
use Illuminate\Database\Seeder;

// Data contoh Fase 4–9 agar setiap modul bisa dicoba. Teks dalam [kurung siku] wajib diganti data asli pesantren.
// Jurusan (MajorSeeder) dan bank soal contoh dianggap data referensi awal; sisanya hanya untuk demo.
class DemoContentSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->isProduction() && ! config('peduli.allow_demo_seed')) {
            $this->command?->warn('DemoContentSeeder dilewati di production.');

            return;
        }

        $admin = User::where('username', 'admin')->first();
        $country = fn (string $iso) => Country::where('iso2', $iso)->value('id');

        // Fase 4: mitra & program
        $lpk = Partner::updateOrCreate(['name' => '[Nama LPK Jepang]'], ['country_id' => $country('JP'), 'description' => 'Lembaga pelatihan kerja mitra resmi pesantren untuk program ke Jepang.', 'website' => 'https://[situs-resmi-lpk].example']);
        $labu = Partner::updateOrCreate(['name' => 'Labugastar'], ['country_id' => $country('RU'), 'description' => 'Mitra pesantren untuk program studi dan kerja di Rusia.']);
        foreach ([
            [$lpk, 'Magang Perawat Lansia (Kaigo)', ProgramType::Internship, now()->addWeeks(6)],
            [$lpk, 'Kerja Pengolahan Makanan', ProgramType::Work, now()->addWeeks(3)],
            [$labu, 'Program Bahasa & Persiapan Kuliah Rusia', ProgramType::Internship, now()->addMonths(2)],
        ] as [$partner, $title, $type, $deadline]) {
            $partner->programs()->firstOrCreate(['title' => $title], [
                'country_id' => $partner->country_id, 'type' => $type,
                'summary' => 'Program resmi mitra pesantren. Seluruh syarat dan biaya dicocokkan dengan dokumen resmi mitra.',
                'requirements' => "Lulus SMA/sederajat\nUsia 18–25 tahun\nSehat jasmani dan rohani\nIzin orang tua",
                'costs' => "Biaya [Rp …] · rincian resmi dari mitra\nDibayar ke rekening atas nama lembaga mitra",
                'min_education' => 'Min. lulus SMA/sederajat', 'deadline' => $deadline,
                'official_contact' => 'Admin pesantren · [kontak resmi]', 'status' => ProgramStatus::Open, 'published_at' => now(), 'created_by' => $admin?->id,
            ]);
        }

        // Fase 5: artikel & jurusan
        $articles = [
            [ArticleCategory::Snbp, 'Panduan SNBP: jalur prestasi tanpa tes', 'SNBP memakai nilai rapor dan prestasi. Kenali syarat, jadwal, dan cara memilih program studi.', 'https://snpmb.id'],
            [ArticleCategory::Snbt, 'Bersiap menghadapi SNBT', 'Tes Potensi Skolastik, literasi, dan penalaran matematika: cara belajar yang efektif dari sekarang.', 'https://snpmb.id'],
            [ArticleCategory::Beasiswa, 'Mengenal beasiswa KIP Kuliah', 'Bantuan biaya pendidikan dari pemerintah untuk mahasiswa dari keluarga kurang mampu.', 'https://kip-kuliah.kemdikbud.go.id'],
            [ArticleCategory::LuarNegeri, 'Kuliah di Timur Tengah: persiapan bahasa dan berkas', 'Langkah awal mendaftar ke kampus di Mesir dan Arab Saudi, termasuk tes bahasa Arab.', null],
        ];
        foreach ($articles as [$category, $title, $excerpt, $source]) {
            Article::firstOrCreate(['title' => $title], [
                'category' => $category, 'excerpt' => $excerpt, 'source_url' => $source, 'published_at' => now(), 'author_id' => $admin?->id,
                'body' => "## Ringkasan\n\n{$excerpt}\n\n## Langkah yang perlu disiapkan\n\n1. Baca pengumuman resmi di situs penyelenggara.\n2. Siapkan berkas sejak awal semester.\n3. Diskusikan pilihanmu dengan pembimbing akademik.\n\n> Jadwal bisa berubah. Selalu cocokkan dengan sumber resmi.",
            ]);
        }

        $majors = [
            Pathway::Saintek->value => ['Kedokteran', 'Teknik Informatika', 'Farmasi', 'Teknik Sipil', 'Statistika'],
            Pathway::Soshum->value => ['Ilmu Hukum', 'Akuntansi', 'Ilmu Komunikasi', 'Hubungan Internasional', 'Manajemen'],
            Pathway::Bahasa->value => ['Sastra Arab', 'Sastra Inggris', 'Bahasa dan Kebudayaan Jepang', 'Pendidikan Bahasa Indonesia'],
            Pathway::Keagamaan->value => ['Ilmu Al-Qur’an dan Tafsir', 'Hukum Keluarga Islam', 'Pendidikan Agama Islam', 'Ekonomi Syariah'],
        ];
        foreach ($majors as $pathway => $names) {
            foreach ($names as $name) {
                Major::firstOrCreate(['name' => $name, 'pathway' => $pathway]);
            }
        }

        // Fase 6: tes contoh (Likert minat) — disempurnakan guru/psikolog.
        $test = PotentialTest::firstOrCreate(['title' => 'Tes Minat & Bakat'], ['dimension' => 'interest', 'description' => 'Pernyataan tentang hal yang kamu sukai. Tidak ada jawaban benar atau salah.', 'estimated_minutes' => 10, 'is_active' => true]);
        if ($test->questions()->doesntExist()) {
            $statements = [
                'saintek' => ['Saya senang membongkar alat untuk mengetahui cara kerjanya.', 'Saya menikmati mengerjakan soal matematika yang menantang.', 'Saya penasaran bagaimana tubuh manusia bekerja.'],
                'soshum' => ['Saya senang berdiskusi tentang masalah di masyarakat.', 'Saya tertarik mengelola uang atau usaha kecil.', 'Saya suka mempelajari sejarah dan peristiwa dunia.'],
                'bahasa' => ['Saya senang menulis cerita atau puisi.', 'Saya cepat menghafal kosakata bahasa asing.', 'Saya suka membaca buku dalam bahasa lain.'],
                'keagamaan' => ['Saya senang mengkaji kitab dan tafsir.', 'Saya ingin mengajar ilmu agama kepada orang lain.', 'Saya tertarik mendalami fikih dan hukum Islam.'],
            ];
            $position = 0;
            foreach (array_map(null, ...array_values($statements)) as $round) {
                foreach (array_keys($statements) as $i => $pathway) {
                    $test->questions()->create(['body' => $round[$i], 'type' => 'likert', 'pathway' => $pathway, 'position' => ++$position]);
                }
            }
        }

        // Fase 5: slot bimbingan contoh
        if ($advisor = User::where('username', 'pembimbing')->first()) {
            foreach ([1, 2, 3] as $day) {
                $start = now()->addDays($day)->setTime(9, 0);
                $advisor->guidanceSlots()->firstOrCreate(['starts_at' => $start], ['ends_at' => $start->copy()->addMinutes(30), 'mode' => SessionMode::Online, 'location' => 'https://meet.google.com/[kode]']);
                $advisor->guidanceSlots()->firstOrCreate(['starts_at' => $start->copy()->addHour()], ['ends_at' => $start->copy()->addMinutes(90), 'mode' => SessionMode::Offline, 'location' => 'Ruang BK']);
            }
        }

        // Fase 7: persetujuan wali santri contoh + piket konselor hari ini
        if ($santri = User::where('username', '20250001')->first()) {
            GuardianConsent::firstOrCreate(['santri_id' => $santri->id], ['guardian_name' => 'Wali Santri Contoh', 'relationship' => 'Ayah', 'method' => 'tertulis', 'consented_at' => now(), 'policy_version' => config('peduli.privacy_policy_version'), 'recorded_by' => $admin?->id]);
        }
        if ($counselor = User::where('username', 'konselor')->first()) {
            CounselorShift::firstOrCreate(['counselor_id' => $counselor->id, 'starts_at' => now()->startOfDay()->setTime(7, 0)], ['ends_at' => now()->startOfDay()->setTime(21, 0)]);
        }

        // Fase 8: profil alumni contoh (terverifikasi)
        if ($alumni = User::where('username', 'alumni')->first()) {
            $profile = $alumni->alumniProfile()->updateOrCreate([], [
                'graduation_year' => 2019, 'institution_name' => 'Comenius University', 'field' => 'S1 Ilmu Komputer', 'pathway' => 'saintek',
                'city' => 'Bratislava', 'country_id' => $country('SK'), 'activity' => 'studying',
                'bio' => 'S1 Ilmu Komputer, Comenius University. Siap berbagi soal beasiswa dan hidup di Eropa.', 'accepts_consultations' => true,
            ]);
            $profile->forceFill(['verification_status' => 'verified', 'verified_at' => now(), 'verified_by' => $admin?->id])->save();
        }
    }
}
