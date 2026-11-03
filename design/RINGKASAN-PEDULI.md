# PEDULI – Smart Career Center: Ringkasan Proyek & Brief Desain

Dokumen acuan untuk melanjutkan desain di pen.dev (Pencil), lalu menerapkannya ke kode Laravel.
Kondisi per **23 Oktober 2026**. Semua isi di bawah dicocokkan dengan file di folder proyek ini.

---

## 1. Konteks singkat

Website untuk **Pesantren Modern Daarul ‘Uluum Lido**. Pengguna utama: santri SMP–SMA (sebagian besar di bawah 18 tahun), konselor, pembimbing akademik, admin, dan alumni.

Lima masalah yang dijawab:
1. Santri sulit menemukan tautan resmi kampus dan sekolah kedinasan, sehingga rawan situs palsu.
2. Info kerja & magang luar negeri belum terpusat (mitra: LPK Jepang, Labugastar Rusia, negara lain menyusul).
3. Kasus konseling tidak terdokumentasi, sehingga sulit dilanjutkan konselor berikutnya.
4. Belum ada pemetaan potensi akademik & minat ke jalur Saintek, Soshum, Bahasa, Keagamaan.
5. Data jejaring alumni (termasuk di luar negeri, mis. Slovakia) terbatas.

Enam peran: Super Admin, Admin Pesantren, Konselor, Pembimbing Akademik, Santri, Alumni.

---

## 2. Teknologi yang benar-benar terpasang

| Komponen | Versi / pilihan |
|---|---|
| Framework | **Laravel 12** (v12.70), PHP ^8.2. Rencana awal Laravel 13 tidak dipakai karena butuh PHP 8.3. Laravel 12 mendapat security fix sampai 24 Februari 2027. |
| Auth | Laravel Breeze (Blade). Tailwind CSS **v3** bawaan Breeze. |
| Frontend | Blade + Tailwind CSS + Alpine.js |
| Peran & izin | spatie/laravel-permission 6.25 + Laravel Policies |
| Database | MySQL (test memakai SQLite in-memory) |
| Testing | Pest 3.8 |
| Bahasa | Kode, tabel, kolom: bahasa Inggris. Tampilan & URL: Bahasa Indonesia (`lang/id`). |
| Zona waktu | Asia/Jakarta |

---

## 3. Status per fase

| Fase | Isi | Status |
|---|---|---|
| 1 | Analisis & desain: ERD, daftar tabel, route, struktur folder, 9 keputusan default | ✅ Selesai, semua default disetujui |
| 2 | Setup, autentikasi, peran & permission, layout, dashboard per peran, kelola pengguna | ✅ Selesai. `php artisan test`: 45 lulus, 2 test bawaan Breeze gagal karena aturan password baru, sudah diperbaiki. Uji manual sudah dilakukan. |
| 3 | Direktori Tautan Resmi + pengecek tautan terjadwal | ✅ Kode sudah ada di proyek. **Hasil `php artisan test` belum dilaporkan.** 24 test baru (26 kasus termasuk dataset) di `tests/Feature/LinkDirectory/`. |
| Redesign | Mockup 5 layar di kanvas Claude + versi HTML di `design-mockup/` | ✅ Selesai. **Langkah berikutnya: impor ke pen.dev.** |
| 4 | Kerja & Magang Luar Negeri | ⏳ Belum |
| 5 | Info Kampus & Bimbingan Kuliah | ⏳ Belum |
| 6 | Tes Potensi Akademik & Pemetaan Jalur | ⏳ Belum |
| 7 | Konseling & Catatan Kasus (enkripsi + log audit) | ⏳ Belum |
| 8 | Direktori Alumni | ⏳ Belum |
| 9 | Asisten AI (Claude API) | ⏳ Belum |
| 10 | Seeder data contoh, testing, panduan deployment | ⏳ Belum |

### Keputusan default Fase 1 (semua disetujui)

1. **Hosting:** VPS, atau cPanel yang punya akses cron.
2. **Akun santri:** dibuat admin (impor CSV di Fase 10), tanpa registrasi publik. Alumni daftar sendiri lalu diverifikasi admin.
3. **Persetujuan wali:** dicatat admin per santri. Fitur konseling & chatbot terkunci sampai persetujuan tercatat.
4. **Putra/putri:** konselor & pembimbing diprioritaskan sesuai gender, tidak dikunci keras.
5. **Alih tangan kasus:** setelah diterima, konselor lama tidak bisa membaca kasus lagi.
6. **Notifikasi krisis:** ke konselor piket (`counselor_shifts`), in-app + email. Jika tidak ada yang piket, semua konselor diberi tahu.
7. **Konsultasi alumni:** bisa dibaca Admin Pesantren untuk moderasi, kedua pihak diberi tahu, ada tombol laporkan.
8. **Log chatbot:** hanya Super Admin, pesan bertanda krisis disembunyikan dari tampilan evaluasi.
9. **Bank soal:** mulai dari soal contoh (seeder), disempurnakan guru/psikolog.

---

## 4. Yang sudah ada di kode

### Route & halaman

| URL | Nama route | Akses | View |
|---|---|---|---|
| `/` | `home` | publik | `public/home` |
| `/kebijakan-privasi` | `privacy` | publik | `public/privacy` (draf, wajib disahkan pesantren) |
| `/tautan-resmi` | `links.index` | publik | `links/index` (hanya tautan *verified*) |
| `/tautan-resmi/kenali-situs-palsu` | `links.guide` | publik | `links/guide` |
| `/login`, `/forgot-password`, `/reset-password/{token}`, `/verify-email`, `/confirm-password` | bawaan Breeze | tamu / login | `auth/*` |
| `/dashboard` | `dashboard` | login | mengarahkan ke dashboard sesuai peran |
| `/profil` | `profile.edit` | login | `profile/edit` (ubah nama, email, password; username hanya oleh admin) |
| `/beranda` | `santri.dashboard` | santri | `santri/dashboard` |
| `/konselor` | `counselor.dashboard` | konselor | `counselor/dashboard` |
| `/pembimbing` | `advisor.dashboard` | pembimbing | `advisor/dashboard` (daftar santri binaan) |
| `/alumni-saya` | `alumni.dashboard` | alumni | `alumni/dashboard` |
| `/admin` | `admin.dashboard` | super admin, admin pesantren | `admin/dashboard` (ringkasan pengguna + tautan) |
| `/admin/pengguna` | `admin.users.*` | **super admin saja** | `admin/users/*` (daftar, tambah, ubah, aktif/nonaktif) |
| `/admin/institusi` | `admin.institutions.*` | admin | `admin/institutions/*` |
| `/admin/tautan` | `admin.links.*` | admin | `admin/links/*` (+ verifikasi, tolak, cek sekarang) |

Halaman 403 khusus: `errors/403`. Registrasi publik dan hapus akun mandiri sengaja tidak ada.

### Aturan penting yang sudah berjalan

- Login dengan **NIS, username, atau email**; akun nonaktif tidak bisa login dan langsung dikeluarkan dari sesi. Maks. 5 percobaan login.
- Password minimal 8 karakter, berisi huruf dan angka.
- Super Admin **tidak** punya izin `counseling.handle` (admin tidak boleh membaca catatan konseling).
- Sidebar diisi otomatis dari `config/navigation.php`: menu modul baru muncul sendiri begitu route-nya dibuat.
- Tautan resmi punya 4 status: **Menunggu Verifikasi → Terverifikasi**, atau **Perlu Ditinjau Ulang** / **Ditolak**. Santri hanya melihat Terverifikasi.
- Verifikasi wajib mencantumkan sumber (mis. PDDikti) dan mencatat siapa & kapan. Mengubah URL membatalkan verifikasi.
- Perintah `php artisan links:check` (terjadwal pukul 02.00) mengecek tautan yang terakhir dicek >7 hari lalu. 404/410 atau pindah domain → langsung disembunyikan; gangguan sementara → disembunyikan jika 2× berturut-turut.

### Akun demo (password semua: `password`)

`superadmin`, `admin`, `konselor`, `pembimbing`, `alumni`, dan santri `20250001` (tanpa email).

---

## 5. Design system (dipakai di mockup)

### Arah visual
**Hangat & islami modern**, warna utama **biru azure + turquoise**, aksen emas sedikit, latar krem. Elegan, dengan animasi halus dan *playful*.

### Warna

| Peran | Hex | Catatan |
|---|---|---|
| Latar halaman (krem) | `#FAF7F0` | |
| Latar bagian / panel krem | `#F2ECDF` | blok "Cara kerja", kartu bimbingan |
| Permukaan kartu | `#FFFFFF` | |
| Garis kartu | `#ECE4D2` | |
| Garis input & chip | `#E2DACA` | |
| Pemisah baris | `#F0EADC` | |
| Teks utama (navy) | `#0F2340` | juga footer & sidebar admin |
| Teks sekunder | `#3F4F66` | paragraf |
| Teks redup | `#4A5A70` | keterangan kecil |
| **Azure (utama)** | `#1565C0` | tombol utama, tautan, kata yang ditekankan |
| Azure saat hover | `#0F56A8` | |
| **Azure tua** | `#0B3D7A` | tombol header, sidebar santri, panel gelap |
| Azure muda | `#E3EEFB` | latar ikon, tag PTN |
| Cincin fokus | `#CFE0F5` | |
| Latar baris hover | `#F6F9FD` | |
| **Turquoise** | `#13A89E` | dekorasi, titik, cincin progres (bukan untuk teks kecil) |
| Teks turquoise | `#0B7F78` | label kecil huruf kapital |
| Teks badge turquoise | `#0B6B65` | |
| Turquoise muda | `#D5F3EF` | latar badge "Terverifikasi", tag Luar Negeri |
| Turquoise terang (di latar gelap) | `#5EEAD4` | |
| Emas (aksen) | `#B98A2E` | dekorasi saja |
| Teks emas | `#8A6420` / `#7A5618` | |
| Emas muda | `#F5EEDC` | tag PTS, badge "Menunggu" |
| Ungu (tag Kedinasan) | `#E9E4F7` / teks `#4B3A8C` | |
| Merah (perlu ditinjau) | latar `#FBE4E4`, teks `#8C2F2F`, titik `#C43E3E`, garis `#F2C9C9` | |

**Badge status tautan:** Menunggu Verifikasi = emas muda; Terverifikasi = turquoise muda; Perlu Ditinjau Ulang = merah muda; Ditolak = abu-abu (belum didesain di mockup).

### Tipografi

| Peran | Font | Ukuran di mockup |
|---|---|---|
| Judul (display) | **Fraunces** 500/600, *italic* untuk kata yang ditekankan | hero 76px (line-height 1.02), judul halaman 56–60px, judul bagian 46–52px |
| Teks | **Plus Jakarta Sans** 400/500/600/700 | paragraf 15–19px, tombol 15–16px |
| Label kecil (eyebrow) | Plus Jakarta Sans 700, huruf kapital, letter-spacing 0.16em, warna `#0B7F78` | 13px |
| Domain kampus | monospace (`ui-monospace`) 700, warna azure | 22px |

Keduanya dari Google Fonts.

### Bentuk, bayangan, motif, ikon

- **Radius:** pill `999px` (tombol nav, chip, badge); `16px` tombol & input (`14px` versi kecil); `24–28px` kartu; `32–40px` panel besar.
- **Bayangan:** kartu saat hover `0 22px 40px -20px rgba(11,61,122,.35)`; kartu melayang `0 30px 60px -28px rgba(15,35,64,.45)`; tombol utama `0 14px 28px -14px rgba(21,101,192,.7)`.
- **Motif:** bintang segi delapan (dua persegi, satu diputar 45°), garis tipis, opasitas 12–50%.
- **Logo:** persegi membulat azure + persegi turquoise diputar 45° + lingkaran krem di tengah, di samping tulisan "PEDULI" (Fraunces).
- **Ikon:** garis (stroke) 2px, ujung membulat, grid 24px. Di pen.dev gunakan pustaka **Lucide**.

### Animasi (diterapkan di kode; pen.dev hanya mengimpor tampilan diam)

| Nama | Efek | Waktu |
|---|---|---|
| rise | muncul dari bawah 18–22px + fade, berurutan | 0.7–0.9s, `cubic-bezier(.2,.8,.2,1)`, jeda 0.05–0.1s per elemen |
| lift | kartu naik 5–6px + sedikit miring saat hover | 0.45s, pegas `cubic-bezier(.34,1.56,.64,1)` |
| wiggle | ikon kartu bergoyang ±10° saat hover | 0.6s |
| btn | tombol naik 2px saat hover, mengecil ke 96% saat ditekan, panah bergeser 5px | 0.25–0.3s, pegas |
| floaty | kartu hero melayang naik-turun 10–12px | 6s, berulang |
| spin | motif bintang berputar pelan | 60–80s, berulang |
| ticker | pita domain resmi bergulir, berhenti saat hover | 38s, berulang |
| draw | garis gelombang di bawah kata "pasti" tergambar | 1.4s |
| pop | elemen kecil muncul memantul | 0.7s, pegas |
| grow / ring | batang skor & cincin progres terisi | 1.2s / 1.6s |
| shimmer | kilau pada badge "Terverifikasi" | 3.5s, berulang |
| pulse | titik merah "perlu ditinjau" berdenyut | 2s, berulang |

Semua animasi **dimatikan** bila pengguna mengaktifkan *reduce motion* di perangkatnya.

### Aturan aksesibilitas & isi

- Area sentuh minimal **44px**. Kontras teks minimal 4.5:1 (turquoise `#13A89E` tidak dipakai untuk teks kecil).
- Setiap layar harus rapi di lebar **390px** (HP) dan **1440px** (desktop). Sebagian besar santri memakai HP.
- Bahasa Indonesia, sapaan "Assalamu’alaikum". Slogan: *"Langkah yang pasti menuju kampus & karier impian."* Penutup: *"Man jadda wajada — Siapa bersungguh-sungguh, ia akan sampai."*
- **Jangan** menampilkan jumlah santri pesantren, statistik karangan, atau nomor HP pribadi santri/alumni.
- Data yang belum ada ditulis sebagai placeholder dalam kurung siku, mis. `[TGL]`, `[Nama institusi]`.

---

## 6. Mockup yang sudah dibuat

Kanvas **"Redesign PEDULI Smart Career Center"** di Claude (privat) dan salinan HTML-nya di `design-mockup/`:

| Layar | File HTML | Isi utama |
|---|---|---|
| Beranda publik | `beranda.html` | nav, hero + kartu melayang (tautan terverifikasi, contoh profil minat, chip tanya alumni), pita domain resmi, 6 kartu fitur, 3 langkah cara kerja, bagian privasi, penutup, footer |
| Direktori tautan resmi | `direktori.html` | judul, kartu "Kenali situs kampus palsu", pencarian, chip kategori (Semua/PTN/PTS/Luar Negeri/Kedinasan), kartu institusi dengan domain besar & badge |
| Masuk | `masuk.html` | panel azure kiri + form kanan (NIS/username/email, password, ingat saya) |
| Dashboard santri | `dashboard-santri.html` | sidebar azure tua (9 menu), header, kartu sambutan, 4 akses cepat, progres tes, jadwal bimbingan, tautan baru diverifikasi |
| Admin: kelola tautan | `admin-tautan.html` | sidebar navy, 3 kartu angka, tab status, tabel tautan |

**Cara impor ke pen.dev:** dari terminal VS Code jalankan `cd design-mockup` lalu `php -S localhost:9000`. Di pen.dev desktop, klik ikon **globe**, masukkan mis. `http://localhost:9000/beranda.html?impor`, klik **Import**. Tambahan `?impor` mematikan animasi agar posisi elemen tidak sedang bergerak. Simpan hasilnya sebagai **`design/peduli.pen`**.

---

## 7. Yang perlu didesain di pen.dev

Saran susunan halaman di file `.pen`: **00 Design System · 01 Publik · 02 Auth · 03 Santri · 04 Admin · 05 Konselor · 06 Pembimbing · 07 Alumni**. Setiap layar dibuat versi 1440px dan 390px.

### A. Komponen dasar (00 Design System)
Tombol (utama, sekunder, bahaya, ikon), input & select & textarea (normal, fokus, error, nonaktif), chip filter (aktif/tidak), badge status (4 status tautan + hasil cek: Normal, Tidak ditemukan, Domain berubah, Gagal diakses), tag kategori institusi, kartu, kartu angka, badge "Resmi · Terverifikasi", tampilan domain, sidebar (santri & admin), header, tabel, tab, pesan sukses/error, keadaan kosong, pagination, dialog konfirmasi, dropdown akun.

### B. Layar yang sudah ada di kode tetapi belum ada mockupnya
- Publik: **Kenali situs palsu** (`links/guide`), **Kebijakan privasi**, **403**.
- Auth: lupa password, reset password, verifikasi email, konfirmasi password.
- **Profil saya** (info profil + ubah password).
- Dashboard **admin**, **konselor**, **pembimbing** (daftar santri binaan), **alumni**.
- Admin: **pengguna** (daftar + filter peran/status, form tambah/ubah dengan bagian Data Santri yang muncul jika peran = santri), **institusi** (daftar + form), **tambah tautan**, **detail/ubah tautan** (status, panel verifikasi dengan sumber, form tolak dengan alasan, tombol Cek Sekarang, riwayat pengecekan, hapus).

### C. Layar untuk fase berikutnya

**Fase 4 – Kerja & Magang Luar Negeri**
- Santri: daftar program (filter negara/mitra/status), detail program (negara, mitra, syarat, biaya, tenggat, kontak resmi, status buka/tutup), tombol **Simpan** dan **Saya berminat**.
- Admin: kelola negara, mitra, program; **rekap peminat** per program.

**Fase 5 – Info Kampus & Bimbingan Kuliah**
- Santri: daftar & detail artikel (kategori SNBP, SNBT, Mandiri, Beasiswa, Luar Negeri); slot bimbingan yang tersedia + riwayat, pesan & batal slot.
- Pembimbing: kelola slot jadwal (offline/online), catat hasil sesi, daftar santri binaan, hasil tes santri binaan.
- Admin: kelola artikel, kelola jurusan.

**Fase 6 – Tes Potensi & Pemetaan Jalur**
- Santri: daftar tes, layar mengerjakan tes (pilihan ganda & skala Likert, jawaban tersimpan otomatis), **hasil berupa profil skor 4 jalur** (Saintek, Soshum, Bahasa, Keagamaan; bukan satu label), rekomendasi jurusan & kampus (terhubung ke modul 2 & 3), ajakan berdiskusi dengan pembimbing, riwayat perkembangan.
- Admin: bank soal per dimensi (kemampuan akademik / minat-bakat), opsi jawaban dengan **bobot per jalur**.

**Fase 7 – Konseling & Catatan Kasus** (data paling sensitif)
- Santri: form ajukan konseling (kategori topik, cerita, waktu yang diinginkan, tingkat urgensi menurut santri), status permintaan.
- Konselor: antrean permintaan, ambil permintaan, daftar kasus, detail kasus berisi sesi & **catatan terstruktur** (keluhan utama, asesmen, intervensi, rencana tindak lanjut, tingkat urgensi), **alih tangan kasus** (ajukan, terima/tolak), tutup kasus, **peringatan krisis**.
- Admin: catat **persetujuan wali**, **jadwal piket konselor**. Super Admin: **log audit** (metadata saja, tanpa isi catatan).
- Wajib terlihat di UI: pemberitahuan bahwa setiap akses tercatat; admin tidak bisa membuka catatan.

**Fase 8 – Direktori Alumni**
- Publik: **daftar sebagai alumni**.
- Santri: direktori alumni (filter negara, kampus, bidang), profil alumni, ajukan konsultasi.
- Santri & alumni: utas konsultasi + tombol **laporkan**, keterangan bahwa percakapan dapat ditinjau admin.
- Alumni: edit profil dengan pilihan kontak yang ditampilkan (LinkedIn, email publik); terima/tolak konsultasi. Nomor WhatsApp tidak pernah tampil ke santri.
- Admin: verifikasi alumni, moderasi konsultasi.

**Fase 9 – Asisten AI**
- Santri: layar chat, contoh pertanyaan, kartu rekomendasi tautan (hanya dari direktori terverifikasi), respons empatik + tombol hubungi konselor saat ada tanda krisis, pesan batas penggunaan, penilaian jawaban (suka/tidak). Tidak boleh tampil sebagai pemberi diagnosis.

**Fase 10**
- Super Admin: **impor santri dari CSV** (NIS, nama, kelas). Sisanya seeder, test, dan panduan deployment (tanpa layar baru).

---

## 8. Setelah desain di pen.dev selesai

1. Simpan `design/peduli.pen` di folder proyek ini.
2. Minta Claude menerapkan desain ke Laravel: token warna & font ke `tailwind.config.js`, animasi dengan CSS + Alpine.js, lalu semua view Fase 2–3 diubah ke tampilan baru.
3. Lanjut Fase 4 dengan tampilan baru sejak awal.

## 9. Catatan kecil

- MySQL harus berjalan sebelum `composer run dev` (log 18 Oktober mencatat koneksi MySQL ditolak).
- File kosong `storage/app/peduli-fase2.tar.gz` sisa pemindahan file boleh dihapus.
- Folder `design-mockup/` boleh dihapus setelah impor ke pen.dev selesai.
