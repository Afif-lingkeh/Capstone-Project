# Panduan Deployment PEDULI

Panduan memasang PEDULI di server pesantren: VPS, atau cPanel yang punya akses SSH dan cron (keputusan Fase 1 no. 1).

## 1. Kebutuhan server

| Komponen | Versi minimal |
|---|---|
| PHP | 8.2, dengan ekstensi `bcmath`, `ctype`, `curl`, `fileinfo`, `mbstring`, `openssl`, `pdo_mysql`, `tokenizer`, `xml`, `intl` |
| MySQL / MariaDB | MySQL 8.0 atau MariaDB 10.6 |
| Composer | 2.x |
| Node.js | 20 LTS atau lebih baru (hanya untuk build aset; boleh dijalankan di komputer lain lalu folder `public/build` diunggah) |
| HTTPS | Wajib. Gunakan Let's Encrypt atau SSL bawaan cPanel |

Document root web server harus mengarah ke folder **`public/`**, bukan ke akar proyek.

## 2. Pemasangan pertama

```bash
git clone <repo> peduli && cd peduli
composer install --no-dev --optimize-autoloader
cp .env.example .env
php artisan key:generate
```

Isi `.env`:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://peduli.<domain-pesantren>
APP_TIMEZONE=Asia/Jakarta

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=peduli
DB_USERNAME=peduli
DB_PASSWORD=<kata sandi kuat>

MAIL_MAILER=smtp           # notifikasi krisis dikirim lewat email
MAIL_HOST=...
MAIL_PORT=587
MAIL_USERNAME=...
MAIL_PASSWORD=...
MAIL_FROM_ADDRESS=peduli@<domain-pesantren>

PEDULI_CONTACT_EMAIL=...
PEDULI_CRISIS_HOTLINE=...  # WAJIB: nomor layanan krisis resmi yang disetujui pesantren
ANTHROPIC_API_KEY=...      # kosongkan bila Asisten AI belum dipakai
```

Lalu:

```bash
php artisan migrate --force
php artisan db:seed --force        # di production hanya mengisi peran/izin & daftar negara
php artisan peduli:create-admin superadmin "Nama Super Admin" email@pesantren.id
npm ci && npm run build            # atau unggah folder public/build hasil build
php artisan storage:link
php artisan config:cache && php artisan route:cache && php artisan view:cache
```

Pastikan folder `storage/` dan `bootstrap/cache/` dapat ditulis oleh user web server.

## 3. Cron (wajib)

Pengecekan tautan resmi berjalan setiap pukul 02.00 lewat scheduler Laravel. Tambahkan satu baris cron:

```cron
* * * * * cd /path/ke/peduli && php artisan schedule:run >> /dev/null 2>&1
```

Di cPanel: menu **Cron Jobs**, pilih "Once Per Minute", lalu isi perintah yang sama.

## 4. Hal yang tidak boleh dilupakan

- **Cadangkan `APP_KEY`.** Cerita dan catatan konseling disimpan terenkripsi dengan kunci ini. Bila `APP_KEY` hilang atau diganti, catatan lama **tidak bisa dibaca lagi**. Simpan salinannya di tempat aman yang terpisah dari server.
- **Backup database harian**, misalnya `mysqldump` lewat cron atau fitur backup cPanel. Uji pemulihan minimal sekali per semester.
- **Jangan jalankan** `php artisan migrate:fresh` atau `db:seed --class=DemoUserSeeder` di production. Perintah pertama menghapus semua data.
- **Kebijakan privasi** di `/kebijakan-privasi` masih draf. Sahkan dulu oleh pimpinan pesantren, lalu perbarui `privacy_policy_version` dan `privacy_policy_updated_at` di `config/peduli.php`.
- **Kata kunci krisis** ada di `config/peduli.php` (`crisis_keywords`). Tinjau bersama konselor atau psikolog secara berkala.
- **Asisten AI** memakai model di `PEDULI_CHAT_MODEL` (bawaan `claude-opus-5-5`, effort `low`) dengan batas `PEDULI_CHAT_DAILY_LIMIT` pesan per santri per hari. Pantau biayanya di Console Anthropic dan di menu **Evaluasi Asisten AI** (Super Admin).

## 5. Pembaruan aplikasi

```bash
php artisan down
git pull
composer install --no-dev --optimize-autoloader
php artisan migrate --force
npm ci && npm run build
php artisan config:cache && php artisan route:cache && php artisan view:cache
php artisan up
```

## 6. Setelah pemasangan

1. Masuk sebagai Super Admin, lalu buat akun Admin Pesantren, Konselor, dan Pembimbing di **Pengguna**.
2. Impor santri lewat **Pengguna → Impor santri (CSV)**. Cetak daftar password awal, lalu bagikan secara langsung.
3. Catat persetujuan wali di **Persetujuan Wali**. Konseling dan Asisten AI terbuka per santri setelah persetujuannya tercatat.
4. Atur **Piket Konselor**, isi **Institusi & Tautan Resmi**, **Program Luar Negeri**, **Artikel Kampus**, **Daftar Jurusan**, dan **Bank Soal** (ganti soal contoh bersama guru/psikolog).
5. Jalankan `php artisan test` di server staging sebelum setiap rilis besar.
