# Perbandingan PEDULI vs. Smart Career Compass

**Tanggal:** 4 November 2026
**Penyusun:** analisis otomatis atas kode kedua repositori
**Repositori yang dibandingkan:**

| Nama | Lokasi | Stack | Status |
|---|---|---|---|
| **PEDULI – Smart Career Center** | `./peduli` | Laravel 12 · Blade + Tailwind + Alpine · MySQL · Spatie Permission · Pest | aplikasi kita, yang akan diperbaiki |
| **Smart Career Compass** | `../PKM_LIDO/Smart Career Compass (1)` | React 18 + Vite + TypeScript · shadcn/ui · Supabase (Postgres + Edge Functions) | aplikasi teman, hanya bahan perbandingan |

> **Catatan penting:** kedua aplikasi menargetkan produk yang sama. README teman dibuka dengan kalimat bahwa platformnya bernama "Smart Career Center PEDULI", dan `src/components/IdleTimeoutGuard.tsx:17` memakai kunci `peduli:last_activity`. Jadi ini bukan perbandingan dua produk berbeda, melainkan **dua implementasi atas kebutuhan klien yang sama dengan stack yang sangat berbeda**. Karena itu yang diadaptasi hanya **pola solusi**, bukan kode, teks, atau desain.

---

## 1. Ringkasan dan 3 Perbaikan Terpenting

### Ringkasan

PEDULI secara keseluruhan **lebih matang** daripada pembandingnya pada hampir setiap sumbu yang penting untuk klien pesantren: kelengkapan alur peran, keamanan dan privasi, kualitas kode, dan pengujian. Angka-angka yang terverifikasi:

- **154 test lulus, 506 asersi, 74 detik** (`php artisan test`) — pembanding hanya punya **satu test trivial** (`src/test/example.test.ts`, isinya `expect(true).toBe(true)`).
- **6 peran** dengan berkas route terpisah dan penjagaan di satu tempat (`bootstrap/app.php:20-38`) — pembanding hanya punya **3 peran** (`admin`, `alumni`, `mitra`) dan **tidak punya peran konselor sama sekali** (`supabase/migrations/20260307071958_*.sql:3`).
- Catatan konseling **terenkripsi at-rest** (`app/Models/CounselingSession.php:25-28`) dan **tidak bisa dibuka admin** (`database/seeders/RolePermissionSeeder.php:33-34`) — pembanding menyimpan `counseling.notes` di tabel yang **Row Level Security-nya tidak pernah diaktifkan** (lihat Temuan C-1).
- Aplikasi berhasil dijalankan lokal (SQLite sementara), seluruh alur santri/konselor/admin dibuka manual, tidak ada error.

Namun ada **satu kebutuhan klien yang baru terpenuhi separuh** dan **satu risiko demo yang nyata**, dan justru di situlah pembanding punya sesuatu yang layak ditiru idenya.

### Tiga perbaikan terpenting

#### 1. Lengkapi Kebutuhan #4: belum ada instrumen skrining kesehatan mental sama sekali

Klien meminta *"psikotes dan kesehatan mental dengan rekam jejak yang bisa dilanjutkan konselor"*. PEDULI sudah punya sisi **konseling**-nya dengan sangat baik, tetapi **tidak punya instrumen terukurnya**. `app/Enums/TestDimension.php:8-9` hanya mengenal `academic` dan `interest` — tidak ada dimensi kesejahteraan/kesehatan mental. Satu-satunya tes yang di-seed adalah "Tes Minat & Bakat" berdimensi `interest` (`database/seeders/DemoContentSeeder.php:81`). Menu santri pun tidak punya butir skrining (`config/navigation.php`).

Akibatnya santri tidak punya cara terstruktur untuk mengenali kondisinya sendiri, dan konselor tidak punya angka awal untuk dirujuk. Pembanding justru kuat di sini: `src/lib/psikotesScoring.ts` mengimplementasikan PSS-10, BRS, dan SRQ-20 lengkap dengan *reverse scoring* per butir (baris 24-45), ambang interpretasi (baris 47-115), dan disclaimer tegas bahwa hasilnya bukan diagnosis (baris 20-21).

**Yang diadaptasi hanya idenya**: instrumen skrining publik dengan penilaian berbalik, pita interpretasi, dan disclaimer. Implementasi ditulis sendiri di atas tabel `potential_tests`/`questions`/`test_attempts` yang **sudah ada**, jadi biayanya kecil.

#### 2. Satukan rekam jejak santri untuk konselor

Kata kunci kebutuhan #4 adalah *"rekam jejak yang bisa dilanjutkan konselor"*. Saat ini halaman kasus konselor hanya memuat sesi dan permintaan awal (`app/Http/Controllers/Counselor/CaseController.php:44`); tidak ada hasil tes potensi, tidak ada riwayat skrining, tidak ada riwayat permintaan terdahulu. Konselor pengganti setelah alih tangan memulai hampir dari nol. Hasil tes justru hanya bisa dilihat **pembimbing akademik** (`routes/advisor.php:21`), bukan konselor.

#### 3. Isi data demo untuk modul inti — kalau tidak, demo menampilkan layar kosong

`database/seeders/DemoContentSeeder.php:108-111` hanya membuat persetujuan wali dan satu jadwal piket. Tidak ada permintaan konseling, kasus, sesi, pemesanan bimbingan, percobaan tes, konsultasi alumni, maupun berkas yang di-seed. Terverifikasi di browser: dashboard konselor menampilkan **0 / 0 / 0 / 0** pada keempat kartu. Modul yang paling membedakan PEDULI justru akan terlihat tidak bekerja saat demo capstone.

---

## 2. Tabel Perbandingan per Kebutuhan Klien

| # | Kebutuhan | PEDULI | Smart Career Compass | Putusan |
|---|---|---|---|---|
| 1 | **Kerja & magang, termasuk mitra luar negeri** | `Partner` + `Program` + `Country` termodel penuh, santri menyatakan minat & admin menindaklanjuti, rekap CSV per program (`routes/santri.php:16-20`, `app/Http/Controllers/Admin/ProgramController.php:85-107`) | `jobs` + `job_applications` + `partners` + `companies`; lowongan generik, negara hanya teks bebas, tidak ada pemodelan luar negeri | **PEDULI unggul** — "mitra luar negeri" benar-benar termodel, bukan sekadar kolom lokasi |
| 2 | **Informasi & bimbingan kuliah** | `Article` (SNBP/SNBT/mandiri/beasiswa/luar negeri) + `Major` + **slot & pemesanan bimbingan dengan pembimbing akademik** + catatan sesi (`routes/advisor.php:13-17`, `routes/santri.php:23-25`) | `universities`, `scholarships`, `events` — informasi saja, tidak ada pemesanan/bimbingan | **PEDULI unggul** pada bimbingan; pembanding unggul pada entitas beasiswa & acara yang terstruktur |
| 3 | **Direktori tautan resmi kampus & kedinasan** | `Institution` + `OfficialLink` + `LinkCheckLog`, pengecekan otomatis terjadwal harian (`routes/console.php`), alur verifikasi/tolak, **dapat diakses publik tanpa login** (`routes/web.php:19`), panduan "kenali situs palsu" (`routes/web.php:20`) | Tabel `universities` + impor JSON + CRUD admin; tidak ada verifikasi tautan, tidak ada pemantauan kesehatan tautan | **PEDULI jauh unggul** — ini keunggulan paling tajam kita. Yang layak ditiru: **impor massal JSON/CSV** |
| 4 | **Psikotes & kesehatan mental, rekam jejak dilanjutkan konselor** | Konseling sangat kuat: enkripsi catatan, kasus + sesi terstruktur, alih tangan, deteksi krisis + piket konselor, persetujuan wali sebagai gerbang, log audit. **Tetapi tidak ada instrumen skrining**, dan rekam jejak tes tidak terlihat oleh konselor | Instrumen tervalidasi PSS-10/BRS/SRQ-20 dengan *reverse scoring*, pita interpretasi, dan rujukan ke antrean konselor (`counseling_referrals`). **Tetapi keamanannya lemah**: tabel `counseling` tanpa RLS, tidak ada peran konselor, hasil skrining bernama dapat dilihat admin, tidak ada persetujuan wali | **Terbelah.** PEDULI unggul telak pada keamanan & kelanjutan penanganan; pembanding unggul pada instrumen. **Prioritas #1 kita** |
| 5 | **Tes potensi akademik, pemetaan minat & bakat** | `PotentialTest` dengan soal Likert **dan** pilihan berbobot per jalur, simpan otomatis per jawaban, hasil berupa **profil persentase 4 jalur** (bukan satu label), rekomendasi jurusan, dapat dilihat pembimbing binaan (`app/Services/PathwayScorer.php:17-45`) | CBT + psikotes dengan skor total dan pita interpretasi; model lebih sederhana, tidak ada pemetaan multi-jalur | **PEDULI unggul** — profil 4 jalur lebih jujur secara pedagogis daripada label tunggal |
| 6 | **Data & kontak alumni** | Verifikasi admin sebelum tampil, **kontak tampil hanya bila alumni mengizinkan** (`show_email`/`show_linkedin`), nama disamarkan ke santri (`publicName()`), WhatsApp **tidak pernah** terserialisasi, konsultasi termoderasi + pelaporan | `alumni` + `alumni_education` + `alumni_employment` + `career_updates`, **tracer study**, impor CSV, ekspor Excel/PDF, pembaruan realtime | **Terbelah.** PEDULI unggul privasi & persetujuan; pembanding unggul kelengkapan data dan **ekspor/tracer study** |
| — | **Pengujian** | 154 test lulus, 506 asersi | 1 test trivial | **PEDULI unggul telak** |
| — | **Kesiapan demo** | Aplikasi jalan, semua menu terbuka, **tetapi antrean kerja kosong karena tidak di-seed** | Tidak dapat dijalankan di sini (lihat Batasan) | **PEDULI unggul dengan satu catatan** |

---

## 3. Temuan Beserta Buktinya

### 3.1 Kekuatan PEDULI yang harus dipertahankan (terverifikasi)

| Kode | Kekuatan | Bukti |
|---|---|---|
| **S-1** | Catatan konseling terenkripsi at-rest. Jika basis data bocor, isi cerita santri tetap tidak terbaca | `app/Models/CounselingSession.php:25-28` (`chief_complaint`, `assessment`, `intervention`, `follow_up_plan` → cast `encrypted`); `app/Models/CounselingRequest.php:22`; `app/Models/CounselingCase.php:25`; `app/Models/CaseHandover.php:19`; rancangan dijelaskan di `database/migrations/2026_11_03_400001_create_counseling_tables.php:8-9` |
| **S-2** | Pemisahan peran yang tegas: **Super Admin pun tidak bisa membaca catatan konseling** | `database/seeders/RolePermissionSeeder.php:33-36`; `app/Policies/CounselingCasePolicy.php:14-17` (hanya konselor pemegang kasus saat ini); dikunci oleh test `super admin tidak memiliki permission konseling` (`tests/Feature/RoleAccessTest.php`, lulus) |
| **S-3** | Persetujuan orang tua/wali sebagai gerbang sebelum konseling dan Asisten AI dibuka | `app/Http/Controllers/Santri/CounselingController.php:39,49`; `app/Http/Controllers/Santri/ChatController.php:44`; tabel `guardian_consents` menyimpan versi kebijakan yang disetujui (`database/migrations/2026_11_03_400001_*.php:14-26`) |
| **S-4** | Penanganan krisis: deteksi kata kunci → peringatan ke konselor **piket**, dan isi notifikasi sengaja tidak memuat cerita santri | `app/Services/CrisisResponder.php:31-51` (komentar baris 40 eksplisit); daftar kata kunci di `config/peduli.php:28-33`; fallback ke semua konselor aktif bila tidak ada yang piket (`CrisisResponder.php:54-61`) |
| **S-5** | Log audit hanya metadata — siapa membuka apa dan kapan, tanpa pernah menyimpan isi | `app/Services/AuditLogger.php:12-22`; dipanggil saat kasus dibuka (`app/Http/Controllers/Counselor/CaseController.php:41`), sesi dibuat (`:69`), kasus ditutup (`:89`), alih tangan (`:116`) |
| **S-6** | Privasi alumni berlapis: nomor WhatsApp tidak pernah terserialisasi, nama disamarkan, kontak tampil hanya bila diizinkan | `app/Models/AlumniProfile.php:20` (`$hidden = ['whatsapp']`), `:59-64` (`publicName()` → "Rizki M."); `resources/views/alumni/show.blade.php:12-21` menghormati `show_linkedin`/`show_email` dan menyatakan WhatsApp tidak ditampilkan |
| **S-7** | Foto profil santri di disk privat, bukan `public/`, dengan pemeriksaan hak akses per permintaan | `app/Http/Controllers/AvatarController.php:19-30`; alasannya (mayoritas santri di bawah umur) ditulis di `:13-14` |
| **S-8** | Asisten AI **tidak pernah mengarang URL** — hanya boleh menyebut domain dari direktori terverifikasi | `app/Services/ChatAssistant.php:125-127` (instruksi sistem), `:190-205` (tool hanya mengembalikan `LinkStatus::Verified`); kuota harian per santri benar-benar ditegakkan di `app/Http/Controllers/Santri/ChatController.php:54-56` |
| **S-9** | Kesehatan tautan dipantau otomatis tiap malam | `routes/console.php` (`Schedule::command('links:check')->dailyAt('02:00')->withoutOverlapping()`); `app/Services/LinkChecker/LinkCheckerService.php`; konfigurasi jeda antar-situs agar tidak membebani server kampus (`config/peduli.php:48-53`) |
| **S-10** | Aksesibilitas digarap serius | `resources/views/layouts/app.blade.php:26` (tautan lompat ke konten), `:51` (`aria-current="page"`), `:93` (teks `sr-only` untuk jumlah notifikasi), `:80-82` (`aria-controls`/`aria-expanded`); `tailwind.config.js` mengaktifkan `hoverOnlyWhenSupported` agar efek hover tidak nyangkut di layar sentuh |
| **S-11** | Suite test yang benar-benar menguji perilaku, bukan sekadar ada | 154 lulus. Contoh yang bermakna: `pembimbing hanya melihat hasil santri binaannya`, `santri tidak dapat membuka percobaan tes milik santri lain`, `pengguna yang dinonaktifkan dikeluarkan dari sesi`, `semua menu setiap peran dapat dibuka` (6 peran) |

### 3.2 Celah PEDULI (terverifikasi)

| Kode | Masalah | Bukti |
|---|---|---|
| **P-1** | **Tidak ada instrumen skrining kesehatan mental.** Kebutuhan #4 baru terpenuhi separuh | `app/Enums/TestDimension.php:8-9` hanya `Academic` dan `Interest`; `database/seeders/DemoContentSeeder.php:81` menyeed satu tes berdimensi `interest`; tidak ada butir menu skrining di `config/navigation.php`; pencarian `grep -rni "skrining\|screening"` di `app/` tidak menghasilkan apa pun |
| **P-2** | **Rekam jejak santri terfragmentasi.** Konselor tidak melihat hasil tes potensi maupun riwayat permintaan terdahulu | `app/Http/Controllers/Counselor/CaseController.php:44` hanya memuat `santri.santriProfile`, `sessions.counselor`, `request`, `pendingHandover.to`; `resources/views/counselor/cases/show.blade.php` tidak menampilkan data tes. Hasil tes hanya terbuka untuk pembimbing akademik (`routes/advisor.php:21`) |
| **P-3** | **Data demo kosong untuk modul inti.** Antrean konselor, pembimbing, konsultasi alumni, dan moderasi admin semuanya nol saat demo | `database/seeders/DemoContentSeeder.php:108-111` hanya membuat `GuardianConsent` dan `CounselorShift`. Pencarian `CounselingRequest\|CounselingCase\|GuidanceBooking\|Consultation\|TestAttempt` di `database/seeders/` tidak menemukan pembuatan data. **Diverifikasi di browser:** dashboard konselor menampilkan 0 peringatan krisis, 0 permintaan menunggu, 0 kasus aktif, 0 alih tangan |
| **P-4** | **Tidak ada paksa ganti password awal.** Admin membuat semua akun santri dan **mengetahui password mereka**, dan santri tidak pernah diwajibkan menggantinya | `app/Http/Controllers/Admin/SantriImportController.php:73` membuat password acak, `:85` menampilkannya sekali ke admin. Tidak ada kolom `must_change_password` di `database/migrations/0001_01_01_000000_create_users_table.php:12-26`; `grep -rn "must_change_password\|force_password"` di `app/ database/ resources/` kosong |
| **P-5** | **Ekspor CSV rentan formula injection.** Nama dan catatan santri ditulis mentah; sel yang diawali `=`, `+`, `-`, `@` dapat dieksekusi sebagai rumus saat dibuka di Excel | `app/Http/Controllers/Admin/ProgramController.php:93-104` memanggil `fputcsv` langsung atas `$interest->user->name` dan `$interest->note` tanpa sanitasi. Pembanding sudah menanganinya (`src/lib/exportUtils.ts:84-89`) |
| **P-6** | **Tidak ada kebijakan retensi/penghapusan data sensitif.** Catatan konseling tersimpan selamanya, termasuk milik santri yang sudah lulus | `grep -rni "retention\|prune\|purge\|anonym"` di `app/`, `config/`, `routes/console.php` tidak menghasilkan apa pun. `routes/console.php` hanya menjadwalkan `links:check`. Halaman kebijakan privasi (`resources/views/public/privacy.blade.php`) perlu diperiksa agar tidak menjanjikan retensi yang belum diimplementasikan |
| **P-7** | **Tidak ada integrasi berkelanjutan (CI).** 154 test hanya jalan kalau ada yang ingat menjalankannya | Tidak ada direktori `.github` di repositori (`ls -a` pada akar). Berlaku juga untuk pembanding |
| **P-8** | **Kemampuan ekspor data sangat terbatas** untuk peran Analis Data & Pengujian | Hanya satu ekspor CSV di seluruh aplikasi (`app/Http/Controllers/Admin/ProgramController.php:92`). Tidak ada rekap alumni, rekap hasil tes, atau tracer study yang bisa diunduh |
| **P-9** | **Impor massal hanya untuk akun santri.** Mengisi direktori tautan dan bank soal untuk demo harus manual satu per satu | `routes/admin.php:26-28` hanya mendaftarkan impor pengguna. Pembanding punya impor JSON untuk kampus dan bank soal (`src/components/admin/UniversitiesJsonImport.tsx`, `PsikotesJsonImport.tsx`) |

### 3.3 Temuan pada aplikasi pembanding (untuk pelajaran, bukan untuk ditiru)

| Kode | Temuan | Bukti |
|---|---|---|
| **C-1** | **Tujuh tabel diberi hak `SELECT/INSERT/UPDATE/DELETE` penuh ke peran `authenticated`, tetapi Row Level Security tidak pernah diaktifkan** — termasuk `counseling` (yang punya kolom `notes`) dan `psychotest_results`. Artinya, pada skema tersebut, **setiap pengguna yang login berpotensi membaca dan mengubah catatan konseling siapa pun** lewat API Supabase | `supabase/migrations/20260818063930_*.sql:146-158` membuat `counseling` dan memberi GRANT, tanpa `ALTER TABLE ... ENABLE ROW LEVEL SECURITY`; hal sama untuk `psychotest_results` (`:123-132`), `psychotests` (`:111-121`), `cbt_results` (`:135-143`), `alumni_employment` (`:83-94`), `career_updates` (`:96-108`), `companies` (`:16-30`). Verifikasi menyeluruh: daftar `ENABLE ROW LEVEL SECURITY` di seluruh `supabase/migrations/*.sql` tidak memuat satu pun dari ketujuh tabel itu |
| **C-2** | **Hasil skrining kesehatan mental bernama dapat dilihat oleh admin biasa**, bukan konselor terlatih — dan memang tidak ada peran konselor | `src/components/admin/ScreeningInsights.tsx:23-34` mendefinisikan baris tabel berisi `name`, `score`, `level` per orang; `supabase/migrations/20260307071958_*.sql:3` mendefinisikan `app_role` hanya sebagai `('admin', 'alumni', 'mitra')` |
| **C-3** | **Tidak ada persetujuan orang tua/wali** padahal pengguna utamanya santri di bawah umur | `grep -rni "guardian\|wali\|consent"` di `src/` dan `supabase/migrations/` hanya menghasilkan satu kecocokan tak relevan (komentar tentang CSV injection) |
| **C-4** | **Praktis tidak ada pengujian otomatis** | `src/test/example.test.ts` berisi satu test yang hanya memeriksa `expect(true).toBe(true)` |
| **C-5 (positif)** | Pola yang **layak diadaptasi idenya**: instrumen skrining tervalidasi (`src/lib/psikotesScoring.ts:24-115`), wajib ganti password awal (`src/components/auth/ForcePasswordChangeGuard.tsx:13-17`), auto-logout saat idle (`src/components/IdleTimeoutGuard.tsx:16-18`), sanitasi sel CSV (`src/lib/exportUtils.ts:84-89`), validasi unggahan lewat *magic bytes* (`src/lib/fileSecurity.ts:20-46`), impor massal JSON, serta ekspor Excel/PDF dengan pemetaan judul kolom berbahasa Indonesia (`src/lib/exportUtils.ts:10-60`) | — |

### 3.4 Celah yang belum diselesaikan oleh keduanya

| Kode | Celah | Catatan |
|---|---|---|
| **G-1** | **Retensi dan penghapusan data sensitif.** PEDULI tidak punya mekanisme apa pun; pembanding punya fungsi hapus akun tetapi tanpa kebijakan retensi | Untuk data kesehatan mental anak di bawah umur, ini bukan hal opsional. Minimal: tetapkan masa simpan, dan sediakan perintah artisan untuk menganonimkan kasus yang sudah lewat masa simpan |
| **G-2** | **Tidak ada CI** di kedua repositori | PEDULI paling diuntungkan karena sudah punya 154 test yang berharga |
| **G-3** | **Tidak ada autentikasi dua faktor** untuk akun yang memegang data sensitif (konselor, Super Admin) | |
| **G-4** | **Tidak ada pengujian aksesibilitas otomatis.** PEDULI menulis markup yang baik secara manual, tetapi tidak ada yang mencegah regresi | |
| **G-5** | **Tidak ada bukti supervisi psikolog berlisensi** atas instrumen maupun daftar kata kunci krisis | `config/peduli.php:27` sudah jujur menulis bahwa daftar kata kunci "disempurnakan bersama konselor/psikolog" — tetapi belum ada catatan bahwa itu sudah terjadi. Ini **wajib** sebelum instrumen skrining dirilis ke santri sungguhan |
| **G-6** | **Pemulihan dari backup belum pernah diuji** | `docs/DEPLOYMENT.md:78` sudah menginstruksikan backup harian dan uji pemulihan per semester — lebih baik daripada pembanding, tetapi belum ada catatan bahwa uji itu sudah dijalankan |

---

## 4. Daftar Prioritas

Kolom **Usaha** memakai satuan hari kerja satu orang. **PM/Dev** = Mohammad Afif (PM & Developer), **APB** = Analis Proses Bisnis, **ADP** = Analis Data & Pengujian.

| # | Masalah | Solusi | Dampak | Usaha | Berkas terdampak | Penanggung jawab |
|---|---|---|---|---|---|---|
| **1** | P-3: antrean kerja kosong saat demo | Perluas seeder demo: 2 permintaan konseling (1 biasa, 1 bertanda krisis), 1 kasus dengan 2 sesi, 1 alih tangan tertunda, 2 pemesanan bimbingan, 1 percobaan tes terkumpul, 2 konsultasi alumni, 3 berkas santri | **Sangat tinggi** — tanpa ini modul unggulan tampak tidak bekerja | 1,5 | `database/seeders/DemoContentSeeder.php` | PM/Dev |
| **2** | P-1: tidak ada instrumen skrining kesehatan mental (Kebutuhan #4) | Tambah nilai `Wellbeing` pada `TestDimension`; tambah kolom pita interpretasi (`min_score`, `max_score`, `level`, `conclusion`, `recommendation`) untuk tes berdimensi ini; tambah penanda `reverse_scored` pada soal; buat `WellbeingScorer` terpisah dari `PathwayScorer`; halaman hasil dengan **disclaimer tegas bahwa ini bukan diagnosis** dan tombol langsung ke Konseling. Gunakan instrumen dari sumber publiknya sendiri (mis. SRQ-20 WHO), **bukan menyalin teks dari repo teman** | **Sangat tinggi** — menutup separuh kebutuhan klien yang belum terpenuhi | 5 | migrasi baru; `app/Enums/TestDimension.php`; `app/Services/WellbeingScorer.php` (baru); `app/Http/Controllers/Santri/TestController.php`; `resources/views/tests/result.blade.php`; `config/navigation.php` | PM/Dev (kode), APB (pemilihan instrumen & naskah) |
| **3** | P-2: rekam jejak tidak terpadu untuk konselor | Halaman "Rekam jejak santri" untuk konselor pemegang kasus: profil tes potensi, riwayat skrining, riwayat permintaan konseling, ringkasan sesi lintas kasus. **Wajib dicatat di log audit** dan dibatasi `CounselingCasePolicy` | **Tinggi** — inti dari frasa "dapat dilanjutkan konselor" | 3 | `app/Http/Controllers/Counselor/CaseController.php`; `app/Policies/CounselingCasePolicy.php`; view baru di `resources/views/counselor/`; `app/Services/AuditLogger.php` (pemanggilan) | PM/Dev |
| **4** | P-1 lanjutan: hasil skrining perlu sampai ke konselor | Rujukan otomatis: skor skrining di pita paling berisiko → masuk antrean permintaan konselor dengan penanda sumber `skrining`, **tanpa menyalin jawaban butir soal** ke notifikasi | **Tinggi** | 2 | `app/Services/WellbeingScorer.php`; `app/Models/CounselingRequest.php`; `app/Services/CrisisResponder.php`; `app/Http/Controllers/Counselor/RequestController.php` | PM/Dev |
| **5** | P-4: tidak ada paksa ganti password awal | Kolom `must_change_password` pada `users` (default `true` untuk akun hasil impor); middleware yang mengalihkan ke halaman ganti password kecuali untuk rute logout dan ganti password itu sendiri | **Tinggi** — admin saat ini tahu password seluruh santri | 1,5 | migrasi baru; `app/Models/User.php`; middleware baru + `bootstrap/app.php`; `app/Http/Controllers/Admin/SantriImportController.php`; `app/Http/Controllers/Auth/PasswordController.php` | PM/Dev |
| **6** | P-6 / G-1: tidak ada retensi data sensitif | Tetapkan masa simpan di `config/peduli.php`; perintah `peduli:anonymize-closed-cases` yang menganonimkan kasus tertutup melewati masa simpan; jadwalkan bulanan; **selaraskan dengan naskah kebijakan privasi** | **Tinggi** (kepatuhan & etika) | 2 | `config/peduli.php`; `app/Console/Commands/` (baru); `routes/console.php`; `resources/views/public/privacy.blade.php` | PM/Dev (kode), APB (naskah kebijakan) |
| **7** | P-5: CSV formula injection | Pembungkus `fputcsv` yang memberi awalan kutip tunggal pada sel yang dimulai `=`, `+`, `-`, `@`, atau karakter kendali. Pakai di semua ekspor, sekarang dan nanti | **Sedang** (keparahan rendah, biaya sangat rendah) | 0,5 | `app/Support/` (helper baru); `app/Http/Controllers/Admin/ProgramController.php:93-104` | PM/Dev |
| **8** | P-8: ekspor data minim | Ekspor CSV untuk rekap alumni (hanya kolom yang boleh dibagikan — **tanpa WhatsApp**), rekap hasil tes potensi per angkatan (**anonim/agregat**), dan rekap berkas. Semua lewat pembungkus dari butir 7 | **Sedang** — langsung memberi bahan kerja untuk peran ADP | 2 | controller admin terkait; `app/Support/` | ADP (spesifikasi), PM/Dev (kode) |
| **9** | P-7 / G-2: tidak ada CI | Workflow GitHub Actions: `composer install`, `php artisan test`, `./vendor/bin/pint --test` pada setiap push dan pull request | **Sedang** — menjaga 154 test tetap bernilai | 0,5 | `.github/workflows/ci.yml` (baru) | ADP |
| **10** | P-9: impor massal terbatas | Impor CSV/JSON untuk institusi + tautan resmi, dan untuk bank soal | **Sedang** — mempercepat pengisian data demo dan serah terima ke pesantren | 2 | controller admin baru; `routes/admin.php`; view impor | PM/Dev |
| **11** | G-3: tidak ada 2FA | Minimal: wajibkan password kuat + catat dan tampilkan `last_login_at` untuk akun konselor & Super Admin. 2FA penuh ditunda pasca-demo | **Rendah untuk demo**, tinggi untuk produksi | 1 | `app/Http/Requests/Auth/LoginRequest.php`; view profil | PM/Dev |
| **12** | G-5: instrumen belum ditinjau psikolog | Jadwalkan sesi peninjauan instrumen **dan** daftar kata kunci krisis bersama konselor pesantren; catat hasilnya sebagai dokumen | **Sangat tinggi untuk produksi**, wajib sebelum santri sungguhan memakainya | 1 (+ jadwal pihak luar) | `docs/` (dokumen baru); `config/peduli.php:28-33` | APB |
| **13** | G-6: pemulihan backup belum diuji | Jalankan satu kali uji pulih ke basis data kosong, catat waktunya di dokumen deployment | **Sedang** | 0,5 | `docs/DEPLOYMENT.md` | ADP |
| **14** | Ide adaptasi: auto-logout idle | Auto-logout setelah tidak ada aktivitas, relevan untuk komputer bersama di pesantren. Laravel sudah menyediakan `SESSION_LIFETIME`; cukup turunkan nilainya + tambahkan peringatan sebelum sesi habis pada peran yang memegang data sensitif | **Rendah–sedang** | 1 | `.env.example`; `config/session.php`; `resources/views/layouts/app.blade.php` | PM/Dev |

**Perlu diverifikasi (asumsi, bukan temuan):** `config/peduli.php:38` dan `.env.example` memakai nama model `claude-opus-5-5`. Saya **tidak dapat memastikan** nama model ini sahih karena `ANTHROPIC_API_KEY` kosong sehingga tidak ada panggilan nyata yang bisa diuji. Jika nama itu keliru, Asisten AI akan gagal tepat saat demo. **Uji satu panggilan nyata sebelum hari demo.**

---

## 5. Rencana Kerja Mingguan sampai Demo

Delapan minggu, 4 November 2026 – 30 Desember 2026. Demo diasumsikan awal Januari 2027.

| Minggu | Tanggal | Fokus | Keluaran | Penanggung jawab |
|---|---|---|---|---|
| **1** | 4–10 Nov | **Amankan demo & pondasi** | Butir 1 (seeder demo lengkap), butir 7 (sanitasi CSV), butir 9 (CI). **Uji satu panggilan nyata Asisten AI.** APB mulai memilih instrumen skrining | PM/Dev, ADP, APB |
| **2** | 11–17 Nov | **Instrumen skrining — basis data & penilaian** | Migrasi pita interpretasi + penanda *reverse scoring*; `TestDimension::Wellbeing`; `WellbeingScorer` + test unitnya. APB menyerahkan naskah butir soal & interpretasi berbahasa Indonesia | PM/Dev, APB |
| **3** | 18–24 Nov | **Instrumen skrining — alur santri** | Halaman daftar, pengerjaan, dan hasil skrining dengan disclaimer tegas + jalur cepat ke Konseling; menu santri diperbarui; test fitur | PM/Dev |
| **4** | 25 Nov–1 Des | **Rujukan ke konselor + rekam jejak terpadu** | Butir 4 (rujukan otomatis) dan butir 3 (halaman rekam jejak santri untuk konselor, lengkap dengan log audit) + test hak akses | PM/Dev |
| **5** | 2–8 Des | **Keamanan akun & retensi** | Butir 5 (paksa ganti password awal), butir 6 (perintah anonimisasi + jadwal + naskah kebijakan privasi diselaraskan), butir 11 | PM/Dev, APB |
| **6** | 9–15 Des | **Data & impor/ekspor** | Butir 8 (ekspor rekap) dan butir 10 (impor massal institusi/tautan & bank soal); ADP mengisi data demo yang realistis lewat jalur impor itu | ADP, PM/Dev |
| **7** | 16–22 Des | **Pembekuan fitur, pengujian menyeluruh, aksesibilitas** | Tidak ada fitur baru. Uji manual keenam peran di ponsel dan desktop; butir 13 (uji pulih backup); butir 14; perbaikan bug; seluruh test hijau | ADP memimpin, semua |
| **8** | 23–30 Des | **Latihan demo & dokumentasi** | Naskah demo per kebutuhan klien (6 alur, masing-masing ≤ 3 menit); `docs/DEPLOYMENT.md` diperbarui; dua kali gladi bersih; butir 12 (dokumen peninjauan psikolog) selesai | PM/Dev memimpin, semua |

**Aturan main:** butir 1 dan butir 2 tidak boleh bergeser. Bila jadwal meleset, yang dikorbankan lebih dulu adalah butir 10, 11, dan 14 — bukan butir 2, 3, atau 5.

---

## 6. Cara Menguji Setiap Perbaikan

Setiap butir perbaikan di bawah harus lulus **ketiganya**: test otomatis, pemeriksaan manual, dan pemeriksaan privasi.

### Butir 1 — Seeder demo
- **Otomatis:** `php artisan migrate:fresh --seed` pada basis data kosong harus berhasil tanpa galat; `tests/Feature/SmokeTest.php` tetap lulus (uji `halaman detail dengan data contoh dapat dibuka` sudah menjaga ini).
- **Manual:** masuk sebagai `konselor`, `pembimbing`, `alumni`, `admin`, dan `superadmin`; **tidak boleh ada kartu statistik bernilai nol** pada dashboard masing-masing.
- **Privasi:** pastikan `PEDULI_ALLOW_DEMO_SEED` tetap `false` pada `.env.example` (`config/peduli.php:8`) dan data contoh tidak memuat nama orang sungguhan.

### Butir 2 — Instrumen skrining kesehatan mental
- **Otomatis:** test unit `WellbeingScorer` dengan **angka yang dihitung manual lebih dahulu**: satu kasus semua butir skor terendah, satu semua tertinggi, satu campuran yang melibatkan butir berbalik, dan satu kasus tepat di batas antar pita. Test fitur: santri tanpa persetujuan wali ditolak; santri tidak dapat membuka hasil milik santri lain (tiru pola `tests/Feature/Tests/PotentialTestTest.php`).
- **Manual:** kerjakan satu skrining sampai tuntas; pastikan halaman hasil memuat disclaimer "bukan diagnosis" dan tombol ke Konseling **di atas lipatan layar ponsel**.
- **Privasi:** periksa bahwa hasil skrining **tidak muncul** di dashboard admin mana pun, dan admin tidak memperoleh permission baru di `RolePermissionSeeder`.

### Butir 3 — Rekam jejak terpadu untuk konselor
- **Otomatis:** konselor **bukan** pemegang kasus menerima 403; konselor lama setelah alih tangan diterima menerima 403; Super Admin menerima 403. Satu baris `audit_logs` tercipta setiap kali halaman dibuka.
- **Manual:** sebagai konselor pemegang kasus, buka halaman dan pastikan profil tes, riwayat skrining, dan riwayat permintaan tampil dalam satu layar.
- **Privasi:** pastikan `audit_logs` hanya memuat metadata, sesuai `app/Services/AuditLogger.php:12-22`.

### Butir 4 — Rujukan otomatis dari skrining
- **Otomatis:** skor di pita paling berisiko menciptakan tepat satu `CounselingRequest` bersumber `skrining`; skor di pita aman tidak menciptakan apa pun; rujukan ganda dari percobaan berulang tidak menumpuk.
- **Manual:** kerjakan skrining dengan jawaban berisiko sebagai santri, lalu periksa antrean konselor.
- **Privasi:** **periksa isi notifikasi** — tidak boleh memuat jawaban butir soal, mengikuti pola yang sudah benar di `app/Services/CrisisResponder.php:40-48`.

### Butir 5 — Paksa ganti password awal
- **Otomatis:** pengguna hasil impor yang mencoba membuka `/beranda` dialihkan ke halaman ganti password; setelah mengganti, pengalihan berhenti; rute logout tetap dapat diakses selama status wajib-ganti aktif.
- **Manual:** impor satu baris CSV, masuk dengan password yang ditampilkan, pastikan terkunci sampai password diganti.
- **Privasi:** pastikan password lama tidak dapat dipakai kembali dan tidak pernah tercatat di log.

### Butir 6 — Retensi & anonimisasi
- **Otomatis:** `peduli:anonymize-closed-cases` menganonimkan kasus tertutup melewati masa simpan dan **tidak menyentuh** kasus yang masih terbuka maupun yang belum lewat masa simpan; dijalankan dua kali tidak menimbulkan efek ganda.
- **Manual:** jalankan dengan opsi *dry-run* lebih dahulu dan periksa daftar yang akan terdampak.
- **Privasi:** bandingkan masa simpan di `config/peduli.php` dengan yang tertulis di halaman kebijakan privasi — keduanya **harus menyebut angka yang sama**.

### Butir 7 — Sanitasi CSV
- **Otomatis:** test yang membuat santri bernama `=1+1` dan catatan berawalan `@`, lalu memeriksa bahwa keluaran CSV memberi awalan kutip tunggal pada kedua sel.
- **Manual:** unduh berkas, buka di Excel, pastikan sel tampil sebagai teks dan tidak dieksekusi.

### Butir 8 — Ekspor rekap
- **Otomatis:** setiap ekspor hanya memuat kolom yang diizinkan; test khusus yang memastikan **kolom WhatsApp alumni tidak pernah ikut terekspor**; peran tanpa izin menerima 403.
- **Manual:** ADP mengunduh seluruh rekap dan memeriksa kewajaran angkanya.
- **Privasi:** rekap hasil tes harus **agregat atau anonim**, bukan daftar bernama.

### Butir 9 — CI
- **Otomatis:** buka satu pull request percobaan dan pastikan workflow berjalan, lulus, dan **gagal** ketika sengaja disisipkan test yang keliru.

### Butir 10 — Impor massal
- **Otomatis:** baris tidak valid dilewati dengan pesan yang jelas, baris valid tetap masuk — tiru pola uji yang sudah ada: `super admin mengimpor santri dari CSV; baris tidak valid dilewati`.
- **Manual:** impor satu berkas berisi campuran baris benar dan salah; periksa halaman hasil impor.
- **Privasi:** tautan hasil impor harus masuk berstatus **menunggu verifikasi**, bukan langsung terverifikasi dan tampil ke santri.

### Regresi menyeluruh (setiap minggu, oleh ADP)
```bash
php artisan test
```
Angka acuan hari ini: **154 lulus, 506 asersi**. Setiap perbaikan di atas harus **menambah** jumlah ini, bukan sekadar mempertahankannya.

---

## Lampiran: Cara Temuan Ini Diverifikasi, dan Batasannya

### Yang dijalankan dan berhasil
- `php artisan test` pada PEDULI → **154 lulus, 506 asersi, 74,36 detik**.
- Migrasi + seeder demo PEDULI pada basis data SQLite sementara di direktori scratch (**tanpa mengubah `.env`, tanpa menyentuh basis data MySQL**), lalu `php artisan serve`.
- Penelusuran manual lewat peramban: halaman depan publik, login, dashboard santri, `/konseling`, `/tes`, dashboard konselor, dashboard Super Admin. Penjagaan peran diverifikasi langsung — santri yang membuka `/konselor` memperoleh halaman "Akses Ditolak".

### Yang tidak dapat dijalankan, dan sebabnya
- **MySQL lokal tidak berjalan** (`SQLSTATE[HY000] [2002]` pada 127.0.0.1:3306). Karena itu PEDULI dijalankan di atas SQLite sementara. Semua migrasi berjalan bersih di SQLite, tetapi **perilaku khas MySQL tidak teruji** di sesi ini.
- **Smart Career Compass tidak dapat dijalankan sama sekali.** `node_modules` tidak ada, dan aplikasinya membutuhkan proyek Supabase beserta kredensialnya. Memasang dependensi dan menjalankan aplikasi akan memerlukan kredensial yang bukan milik kita. **Seluruh temuan tentang aplikasi pembanding berasal dari pembacaan kode dan berkas migrasi, bukan dari pengamatan runtime.** Ini relevan khususnya untuk Temuan C-1: kesimpulan RLS ditarik dari ketiadaan pernyataan `ENABLE ROW LEVEL SECURITY` di seluruh berkas migrasi, dan **belum dikonfirmasi pada basis data yang berjalan** — ada kemungkinan RLS diaktifkan lewat dasbor Supabase di luar berkas migrasi.
- **Asisten AI tidak diuji secara nyata** karena `ANTHROPIC_API_KEY` kosong. Isi berkas `.env` tidak dibuka sama sekali; seluruh pembacaan konfigurasi dilakukan lewat `.env.example` dan `config/`.

### Batasan yang dipatuhi
- **Tidak ada satu berkas pun yang diubah di repositori teman.**
- **Tidak ada kode, teks, atau desain yang disalin** dari repositori teman. Yang diambil hanya gagasan solusi, dan setiap gagasan itu ditandai eksplisit di bagian 4 beserta rencana implementasi mandiri.
- Isi `.env` dan kredensial tidak pernah dibuka atau ditampilkan.
- Data kesehatan mental dan kontak alumni diperlakukan sebagai data sensitif: setiap butir perbaikan di bagian 6 wajib lulus pemeriksaan privasi tersendiri.
