<?php
// database/seeders/DemoContentSeeder.php

namespace Database\Seeders;

use App\Enums\ArticleCategory;
use App\Enums\BookingStatus;
use App\Enums\CounselingStatus;
use App\Enums\CounselingTopic;
use App\Enums\DocumentCategory;
use App\Enums\DocumentStatus;
use App\Enums\Pathway;
use App\Enums\ProgramStatus;
use App\Enums\ProgramType;
use App\Enums\SessionMode;
use App\Enums\Urgency;
use App\Models\Article;
use App\Models\Consultation;
use App\Models\CounselingCase;
use App\Models\CounselorShift;
use App\Models\Country;
use App\Models\CrisisAlert;
use App\Models\Document;
use App\Models\GuardianConsent;
use App\Models\Major;
use App\Models\Partner;
use App\Models\PotentialTest;
use App\Models\User;
use App\Services\PathwayScorer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

// Data contoh Fase 4–9 agar setiap modul bisa dicoba. Teks dalam [kurung siku] wajib diganti data asli pesantren.
// Jurusan (MajorSeeder) dan bank soal contoh dianggap data referensi awal; sisanya hanya untuk demo.
//
// Seluruh isi konseling di bawah ini adalah skenario fiktif yang ditulis untuk demo, bukan cerita santri sungguhan.
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

        $santri = fn (string $nis) => User::where('username', $nis)->first();
        [$s1, $s2, $s3, $s4] = [$santri('20250001'), $santri('20240002'), $santri('20230003'), $santri('20250004')];
        $counselor = User::where('username', 'konselor')->first();
        $counselor2 = User::where('username', 'konselor2')->first();
        $advisor = User::where('username', 'pembimbing')->first();
        $alumni = User::where('username', 'alumni')->first();

        // Fase 5: slot bimbingan contoh
        if ($advisor) {
            foreach ([1, 2, 3] as $day) {
                $start = now()->addDays($day)->setTime(9, 0);
                $advisor->guidanceSlots()->firstOrCreate(['starts_at' => $start], ['ends_at' => $start->copy()->addMinutes(30), 'mode' => SessionMode::Online, 'location' => 'https://meet.google.com/[kode]']);
                $advisor->guidanceSlots()->firstOrCreate(['starts_at' => $start->copy()->addHour()], ['ends_at' => $start->copy()->addMinutes(90), 'mode' => SessionMode::Offline, 'location' => 'Ruang BK']);
            }
        }

        // Fase 7: persetujuan wali.
        // Nabila (20240002) sengaja dibiarkan tanpa persetujuan agar gerbang izin wali bisa didemokan di menu admin.
        foreach ([[$s1, 'Wali Santri Contoh', 'Ayah'], [$s3, 'Bapak Sulaiman', 'Ayah'], [$s4, 'Bapak Hamzah', 'Paman']] as [$user, $guardian, $relationship]) {
            if ($user) {
                GuardianConsent::firstOrCreate(['santri_id' => $user->id], [
                    'guardian_name' => $guardian, 'relationship' => $relationship, 'method' => 'tertulis',
                    'consented_at' => now()->subMonths(2), 'policy_version' => config('peduli.privacy_policy_version'), 'recorded_by' => $admin?->id,
                ]);
            }
        }

        // Piket konselor hari ini: menentukan siapa yang menerima notifikasi krisis.
        if ($counselor) {
            CounselorShift::firstOrCreate(['counselor_id' => $counselor->id, 'starts_at' => now()->startOfDay()->setTime(7, 0)], ['ends_at' => now()->startOfDay()->setTime(21, 0)]);
        }
        if ($counselor2) {
            CounselorShift::firstOrCreate(['counselor_id' => $counselor2->id, 'starts_at' => now()->addDay()->startOfDay()->setTime(7, 0)], ['ends_at' => now()->addDay()->startOfDay()->setTime(21, 0)]);
        }

        // Fase 8: profil alumni contoh (terverifikasi)
        $alumniProfile = null;
        if ($alumni) {
            $alumniProfile = $alumni->alumniProfile()->updateOrCreate([], [
                'graduation_year' => 2019, 'institution_name' => 'Comenius University', 'field' => 'S1 Ilmu Komputer', 'pathway' => 'saintek',
                'city' => 'Bratislava', 'country_id' => $country('SK'), 'activity' => 'studying',
                'bio' => 'S1 Ilmu Komputer, Comenius University. Siap berbagi soal beasiswa dan hidup di Eropa.', 'accepts_consultations' => true,
            ]);
            $alumniProfile->forceFill(['verification_status' => 'verified', 'verified_at' => now(), 'verified_by' => $admin?->id])->save();
        }

        $this->seedTestAttempt($test, $s1);
        $this->seedBookings($advisor, $s1, $s2);
        $this->seedCounseling($s1, $s3, $s4, $counselor, $counselor2);
        $this->seedConsultations($alumni, $s1, $s2, $s3);
        $this->seedDocuments($s1, $admin);
    }

    /** Satu percobaan tes yang sudah dikumpulkan, supaya profil jalur dan menu pembimbing tidak kosong. */
    private function seedTestAttempt(PotentialTest $test, ?User $santri): void
    {
        if (! $santri || $santri->testAttempts()->whereNotNull('submitted_at')->exists()) {
            return;
        }

        // Jawaban dibuat condong ke Saintek & Bahasa agar hasilnya punya cerita, bukan datar.
        $byPathway = ['saintek' => [5, 4, 5], 'soshum' => [3, 2, 3], 'bahasa' => [4, 5, 4], 'keagamaan' => [4, 4, 5]];
        $used = array_fill_keys(array_keys($byPathway), 0);

        $attempt = $santri->testAttempts()->create(['potential_test_id' => $test->id]);
        foreach ($test->questions()->orderBy('position')->get() as $question) {
            $pathway = $question->pathway->value;
            $attempt->answers()->create(['question_id' => $question->id, 'value' => $byPathway[$pathway][$used[$pathway]++] ?? 3]);
        }

        // Skor dihitung dengan scorer sungguhan agar angka di halaman hasil selalu konsisten dengan jawabannya.
        $attempt->update(['submitted_at' => now()->subDays(3), 'scores' => app(PathwayScorer::class)->score($attempt->fresh())]);
    }

    /** Satu bimbingan mendatang dan satu yang sudah selesai beserta catatan hasilnya. */
    private function seedBookings(?User $advisor, ?User $upcomingSantri, ?User $pastSantri): void
    {
        if (! $advisor) {
            return;
        }

        if ($upcomingSantri && ($slot = $advisor->guidanceSlots()->available()->orderBy('starts_at')->first())) {
            $slot->bookings()->firstOrCreate(['santri_id' => $upcomingSantri->id], [
                'topic' => 'Memilih jurusan antara Saintek dan Bahasa',
                'message' => 'Hasil tes potensi saya tinggi di dua jalur sekaligus. Saya bingung harus mendahulukan yang mana.',
                'status' => BookingStatus::Booked,
            ]);
        }

        if ($pastSantri) {
            $start = now()->subWeek()->setTime(10, 0);
            $past = $advisor->guidanceSlots()->firstOrCreate(['starts_at' => $start], ['ends_at' => $start->copy()->addMinutes(45), 'mode' => SessionMode::Offline, 'location' => 'Ruang BK']);
            $past->bookings()->firstOrCreate(['santri_id' => $pastSantri->id], [
                'topic' => 'Jalur beasiswa untuk Farmasi',
                'message' => 'Ingin tahu beasiswa apa saja yang bisa didaftar dari sekarang.',
                'status' => BookingStatus::Completed,
                'outcome_notes' => 'Sudah membahas KIP Kuliah dan beasiswa internal kampus. Nilai rapor sudah memenuhi syarat awal SNBP.',
                'next_steps' => "Lengkapi berkas rapor semester 1–4\nSiapkan surat keterangan tidak mampu bila mendaftar KIP\nKonsultasi lanjutan setelah pengumuman kuota sekolah",
            ]);
        }
    }

    /**
     * Antrean konseling yang terisi: satu kasus aktif berjalan, satu permintaan biasa menunggu,
     * satu permintaan krisis, dan satu alih tangan yang menunggu jawaban konselor piket.
     */
    private function seedCounseling(?User $s1, ?User $crisisSantri, ?User $waitingSantri, ?User $counselor, ?User $counselor2): void
    {
        if (! $counselor || ! $counselor2) {
            return;
        }

        // 1) Kasus aktif milik konselor piket, lengkap dengan dua catatan sesi.
        if ($s1 && $s1->counselingRequests()->doesntExist()) {
            $case = CounselingCase::create([
                'santri_id' => $s1->id, 'counselor_id' => $counselor->id,
                'status' => CounselingStatus::Open, 'urgency' => Urgency::Medium,
            ]);

            $request = $s1->counselingRequests()->create([
                'topic' => CounselingTopic::Akademik,
                'story' => 'Beberapa minggu ini saya sulit fokus saat belajar malam. Nilai ulangan saya turun dan saya takut mengecewakan orang tua.',
                'preferred_time' => 'Sore setelah ashar',
                'urgency' => Urgency::Medium,
                'status' => CounselingStatus::Accepted,
                'is_crisis' => false,
                'counseling_case_id' => $case->id,
                'accepted_at' => now()->subWeeks(2)->setTime(16, 0),
            ]);
            // Permintaan harus mendahului sesi pertama, jadi tanggal pembuatannya ikut dimundurkan.
            $this->backdate($request, now()->subWeeks(2)->setTime(7, 30));
            $this->backdate($case, now()->subWeeks(2)->setTime(16, 0));

            foreach ([
                [now()->subWeeks(2)->setTime(16, 0), SessionMode::Offline, Urgency::Medium,
                    'Sulit fokus belajar dan takut mengecewakan orang tua.',
                    'Tekanan akademik menjelang ujian, ditambah pola tidur yang berantakan. Tidak ada tanda bahaya.',
                    'Mendengarkan aktif, menormalisasi perasaan, menyusun jadwal belajar yang lebih ringan bersama santri.',
                    'Mencoba jadwal baru selama satu pekan dan mencatat perubahan rasa.',
                    now()->subWeek()->setTime(16, 0)],
                [now()->subWeek()->setTime(16, 0), SessionMode::Offline, Urgency::Low,
                    'Melaporkan hasil percobaan jadwal belajar baru.',
                    'Keluhan berkurang. Santri melaporkan tidur lebih teratur dan mulai berani bertanya di kelas.',
                    'Menguatkan kemajuan yang sudah ada dan melatih cara meminta bantuan guru mata pelajaran.',
                    'Melanjutkan jadwal, evaluasi lagi dua pekan ke depan.',
                    now()->addWeek()->setTime(16, 0)],
            ] as [$heldAt, $mode, $urgency, $complaint, $assessment, $intervention, $plan, $next]) {
                $case->sessions()->create([
                    'counselor_id' => $counselor->id, 'held_at' => $heldAt, 'mode' => $mode, 'urgency' => $urgency,
                    'chief_complaint' => $complaint, 'assessment' => $assessment, 'intervention' => $intervention,
                    'follow_up_plan' => $plan, 'next_session_at' => $next,
                ]);
            }
        }

        // 2) Permintaan biasa yang masih menunggu — bisa diterima langsung saat demo.
        if ($waitingSantri && $waitingSantri->counselingRequests()->doesntExist()) {
            $waitingSantri->counselingRequests()->create([
                'topic' => CounselingTopic::Pertemanan,
                'story' => 'Saya baru pindah asrama dan belum punya teman dekat. Rasanya sepi, apalagi saat akhir pekan.',
                'preferred_time' => 'Kapan saja setelah isya',
                'urgency' => Urgency::Low,
                'status' => CounselingStatus::Pending,
                'is_crisis' => false,
            ]);
        }

        // 3) Permintaan bertanda krisis + peringatan yang masih terbuka.
        //    Skenario kekerasan dipilih (bukan menyakiti diri) agar alur krisis tetap bisa didemokan
        //    di depan audiens tanpa menampilkan simulasi niat bunuh diri. Kata "dipukuli" ada di
        //    config('peduli.crisis_keywords'), jadi deteksi otomatis tetap tertampil apa adanya.
        if ($crisisSantri && $crisisSantri->counselingRequests()->doesntExist()) {
            $request = $crisisSantri->counselingRequests()->create([
                'topic' => CounselingTopic::Pribadi,
                'story' => 'Saya takut kembali ke asrama. Dua hari lalu saya dipukuli kakak kelas dan sampai sekarang belum berani cerita kepada siapa pun.',
                'preferred_time' => 'Secepatnya',
                'urgency' => Urgency::High,
                'status' => CounselingStatus::Pending,
                'is_crisis' => true,
            ]);

            CrisisAlert::firstOrCreate(
                ['santri_id' => $crisisSantri->id, 'source' => 'request', 'source_id' => $request->id],
                ['status' => 'open'],
            );
        }

        // 4) Kasus milik konselor kedua yang sedang dialihtangankan ke konselor piket.
        if ($crisisSantri && ! CounselingCase::where('counselor_id', $counselor2->id)->exists()) {
            $case2 = CounselingCase::create([
                'santri_id' => $crisisSantri->id, 'counselor_id' => $counselor2->id,
                'status' => CounselingStatus::Open, 'urgency' => Urgency::High,
            ]);

            $this->backdate($case2, now()->subDays(10)->setTime(10, 0));
            $case2->sessions()->create([
                'counselor_id' => $counselor2->id,
                'held_at' => now()->subDays(10)->setTime(10, 0),
                'mode' => SessionMode::Offline,
                'urgency' => Urgency::High,
                'chief_complaint' => 'Merasa tidak aman di lingkungan asrama.',
                'assessment' => 'Perlu pendampingan berkelanjutan dan pendamping perempuan agar santri lebih terbuka.',
                'intervention' => 'Membangun rasa aman, menyepakati cara menghubungi konselor bila keadaan mendesak.',
                'follow_up_plan' => 'Alih tangan ke konselor piket yang dapat menemui santri setiap hari.',
                'next_session_at' => null,
            ]);

            $case2->handovers()->firstOrCreate(
                ['to_counselor_id' => $counselor->id, 'status' => CounselingStatus::Pending],
                [
                    'from_counselor_id' => $counselor2->id,
                    'reason' => 'Saya hanya piket sepekan sekali, sedangkan santri ini perlu ditemui lebih sering. Mohon dilanjutkan oleh konselor piket harian.',
                ],
            );
        }
    }

    /** Memundurkan created_at/updated_at agar urutan kejadian di layar demo masuk akal. */
    private function backdate(Model $model, Carbon $at): void
    {
        $model->forceFill(['created_at' => $at, 'updated_at' => $at])->save();
    }

    /** Tiga utas konsultasi dengan status berbeda agar menu santri, alumni, dan moderasi admin terisi. */
    private function seedConsultations(?User $alumni, ?User $s1, ?User $s2, ?User $s3): void
    {
        if (! $alumni) {
            return;
        }

        // Catatan: utas milik 20250001 sengaja berstatus "closed" supaya tombol "Ajukan konsultasi"
        // di halaman profil alumni tetap tampil untuk santri tersebut.
        $threads = [
            [$s1, 'Pengalaman kuliah di Eropa', 'closed', now()->subMonth(), [
                ['santri', 'Assalamualaikum kak. Bagaimana rasanya kuliah jauh dari keluarga?'],
                ['alumni', 'Waalaikumussalam. Berat di awal, tapi lama-lama terbiasa. Kuncinya cari komunitas dan jaga komunikasi dengan rumah.'],
            ]],
            [$s2, 'Beasiswa untuk jurusan Farmasi', 'accepted', now()->subDays(2), [
                ['santri', 'Kak, apakah ada beasiswa luar negeri yang menerima lulusan pesantren untuk Farmasi?'],
                ['alumni', 'Ada beberapa. Yang paling realistis dari pesantren biasanya jalur pemerintah. Syarat bahasanya yang perlu disiapkan jauh-jauh hari.'],
                ['santri', 'Berarti saya mulai belajar bahasa dari sekarang ya kak. Terima kasih banyak.'],
            ]],
            [$s3, 'Kerja dulu atau kuliah dulu?', 'pending', now()->subHours(6), [
                ['santri', 'Kak, saya ingin bekerja dulu sambil menabung. Apakah nanti sulit kembali kuliah?'],
            ]],
        ];

        foreach ($threads as [$santri, $subject, $status, $lastAt, $messages]) {
            if (! $santri) {
                continue;
            }

            $consultation = Consultation::firstOrCreate(
                ['santri_id' => $santri->id, 'alumni_id' => $alumni->id, 'subject' => $subject],
                ['status' => $status, 'last_message_at' => $lastAt],
            );

            if ($consultation->messages()->exists()) {
                continue;
            }
            foreach ($messages as $i => [$role, $body]) {
                $message = $consultation->messages()->create([
                    'sender_id' => $role === 'santri' ? $santri->id : $alumni->id,
                    'body' => $body,
                ]);
                // created_at tidak fillable, jadi waktunya disetel terpisah agar urutan pesan masuk akal.
                $sentAt = $lastAt->copy()->subMinutes((count($messages) - 1 - $i) * 30);
                $message->forceFill(['created_at' => $sentAt, 'updated_at' => $sentAt])->save();
            }
        }
    }

    /** Tiga berkas dengan status berbeda; file PDF-nya benar-benar ditulis agar bisa dibuka saat demo. */
    private function seedDocuments(?User $owner, ?User $admin): void
    {
        if (! $owner || $owner->documents()->exists()) {
            return;
        }

        // Sampai di sini berarti santri ini tidak punya berkas di basis data, sehingga file apa pun
        // yang tersisa di foldernya adalah yatim dari seeding sebelumnya (seeder ini juga dipanggil
        // berkali-kali oleh test). Tanpa ini, storage menumpuk tiap kali seeder dijalankan ulang.
        Storage::disk(Document::DISK)->deleteDirectory("documents/{$owner->id}");

        $documents = [
            [DocumentCategory::Rapor, 'Rapor Semester Ganjil', 'rapor-semester-ganjil.pdf', DocumentStatus::Reviewed, 'Sudah sesuai. Mohon unggah juga rapor semester genap bila sudah terbit.'],
            [DocumentCategory::Sertifikat, 'Sertifikat Juara 2 MTQ Tingkat Kabupaten', 'sertifikat-mtq.pdf', DocumentStatus::Pending, null],
            [DocumentCategory::Surat, 'Surat Keterangan Siswa Aktif', 'surat-keterangan-aktif.pdf', DocumentStatus::NeedsFix, 'Hasil pindai terpotong di bagian tanda tangan. Mohon diunggah ulang.'],
        ];

        foreach ($documents as [$category, $title, $filename, $status, $reviewNote]) {
            // Teks PDF dibatasi ASCII: font base-14 Helvetica tidak memakai UTF-8,
            // sehingga karakter seperti "·" akan tampil sebagai dua glif yang salah.
            $pdf = $this->samplePdf($title, $owner->name.' - NIS '.$owner->santriProfile?->nis);
            $path = "documents/{$owner->id}/".pathinfo($filename, PATHINFO_FILENAME).'-'.uniqid().'.pdf';
            Storage::disk(Document::DISK)->put($path, $pdf);

            $owner->documents()->create([
                'uploaded_by' => $owner->id,
                'category' => $category,
                'title' => $title,
                'original_name' => $filename,
                'path' => $path,
                'mime_type' => 'application/pdf',
                'size' => strlen($pdf),
                'status' => $status,
                'reviewed_by' => $status === DocumentStatus::Pending ? null : $admin?->id,
                'reviewed_at' => $status === DocumentStatus::Pending ? null : now()->subDays(4),
                'review_note' => $reviewNote,
            ]);
        }
    }

    /**
     * PDF satu halaman yang valid, dibangun tanpa pustaka tambahan (server pesantren belum tentu punya GD).
     * Dipakai agar berkas contoh benar-benar bisa dibuka di peramban saat demo, bukan file kosong.
     */
    private function samplePdf(string $title, string $subtitle): string
    {
        // Font base-14 Helvetica bukan UTF-8: karakter di luar ASCII diganti agar tidak tampil sebagai glif rusak.
        $escape = function (string $text): string {
            $ascii = preg_replace('/[^\x20-\x7E]/', '-', $text);

            return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $ascii);
        };
        $stream = "BT /F1 20 Tf 60 740 Td ({$escape($title)}) Tj ET\n"
            ."BT /F1 12 Tf 60 712 Td ({$escape($subtitle)}) Tj ET\n"
            ."BT /F1 10 Tf 60 676 Td (Berkas contoh PEDULI - bukan dokumen resmi.) Tj ET\n";

        $objects = [
            '<</Type/Catalog/Pages 2 0 R>>',
            '<</Type/Pages/Kids[3 0 R]/Count 1>>',
            '<</Type/Page/Parent 2 0 R/MediaBox[0 0 595 842]/Resources<</Font<</F1 4 0 R>>>>/Contents 5 0 R>>',
            '<</Type/Font/Subtype/Type1/BaseFont/Helvetica>>',
            '<</Length '.strlen($stream).">>\nstream\n".$stream.'endstream',
        ];

        $pdf = "%PDF-1.4\n";
        $offsets = [];
        foreach ($objects as $index => $body) {
            $offsets[] = strlen($pdf);
            $pdf .= ($index + 1)." 0 obj\n".$body."\nendobj\n";
        }

        // Tabel xref: setiap entri wajib tepat 20 byte.
        $startxref = strlen($pdf);
        $size = count($objects) + 1;
        $pdf .= "xref\n0 {$size}\n0000000000 65535 f \n";
        foreach ($offsets as $offset) {
            $pdf .= sprintf("%010d 00000 n \n", $offset);
        }

        return $pdf."trailer\n<</Size {$size}/Root 1 0 R>>\nstartxref\n{$startxref}\n%%EOF";
    }
}
