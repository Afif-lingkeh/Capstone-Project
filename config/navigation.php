<?php
// config/navigation.php

/*
| Menu sidebar per peran. Item hanya tampil jika route-nya sudah terdaftar
| (dicek di App\Support\Navigation), sehingga menu bertambah otomatis
| setiap kali modul pada fase berikutnya selesai dibangun.
| 'icon' = nama ikon Lucide (lihat components/icon.blade.php), 'section' = judul kelompok menu.
*/

$adminMenu = [
    ['label' => 'Ringkasan', 'route' => 'admin.dashboard', 'icon' => 'layout-dashboard', 'section' => 'Kelola'],
    ['label' => 'Tautan Resmi', 'route' => 'admin.links.index', 'match' => 'admin.links.*', 'icon' => 'link', 'section' => 'Kelola', 'description' => 'Kelola dan verifikasi tautan kampus & sekolah kedinasan.'],
    ['label' => 'Institusi', 'route' => 'admin.institutions.index', 'match' => 'admin.institutions.*', 'icon' => 'building-2', 'section' => 'Kelola', 'description' => 'Daftar kampus dan sekolah kedinasan pemilik tautan.'],
    ['label' => 'Program Luar Negeri', 'route' => 'admin.programs.index', 'match' => ['admin.programs.*', 'admin.partners.*'], 'icon' => 'briefcase-business', 'section' => 'Modul', 'description' => 'Program mitra luar negeri dan rekap peminat.'],
    ['label' => 'Artikel Kampus', 'route' => 'admin.articles.index', 'match' => ['admin.articles.*', 'admin.majors.*'], 'icon' => 'newspaper', 'section' => 'Modul', 'description' => 'Jalur masuk, beasiswa, jurusan, dan kuliah di luar negeri.'],
    ['label' => 'Bank Soal', 'route' => 'admin.tests.index', 'match' => ['admin.tests.*', 'admin.questions.*'], 'icon' => 'clipboard-list', 'section' => 'Modul', 'description' => 'Soal tes potensi dan bobot jalur.'],
    ['label' => 'Verifikasi Alumni', 'route' => 'admin.alumni.index', 'match' => 'admin.alumni.*', 'icon' => 'user-check', 'section' => 'Modul', 'description' => 'Tinjau akun alumni sebelum tampil di direktori.'],
    ['label' => 'Moderasi Konsultasi', 'route' => 'admin.consultations.index', 'match' => 'admin.consultations.*', 'icon' => 'message-square', 'section' => 'Modul', 'description' => 'Pantau percakapan santri dengan alumni.'],
    ['label' => 'Berkas Santri', 'route' => 'admin.documents.index', 'match' => 'admin.documents.*', 'icon' => 'folder-open', 'section' => 'Modul', 'description' => 'Periksa dan unggah berkas santri.'],
    ['label' => 'Persetujuan Wali', 'route' => 'admin.consents.index', 'match' => 'admin.consents.*', 'icon' => 'shield', 'section' => 'Modul', 'description' => 'Catat persetujuan orang tua/wali santri.'],
    ['label' => 'Piket Konselor', 'route' => 'admin.shifts.index', 'match' => 'admin.shifts.*', 'icon' => 'calendar-clock', 'section' => 'Modul', 'description' => 'Jadwal konselor yang menerima notifikasi krisis.'],
];

$shared = [
    ['label' => 'Tautan Resmi', 'route' => 'links.index', 'match' => 'links.*', 'icon' => 'shield-check', 'description' => 'Situs kampus yang sudah diverifikasi.'],
    ['label' => 'Info Kampus', 'route' => 'articles.index', 'match' => 'articles.*', 'icon' => 'graduation-cap', 'description' => 'SNBP, SNBT, mandiri, beasiswa, kuliah di luar negeri.'],
    ['label' => 'Direktori Alumni', 'route' => 'alumni.index', 'match' => ['alumni.index', 'alumni.show'], 'icon' => 'users', 'description' => 'Cari alumni berdasarkan negara, kampus, bidang.'],
];

return [
    'super-admin' => [
        ...array_slice($adminMenu, 0, 3),
        ['label' => 'Pengguna', 'route' => 'admin.users.index', 'match' => 'admin.users.*', 'icon' => 'user-cog', 'section' => 'Kelola', 'description' => 'Kelola akun, peran, dan impor santri.'],
        ...array_slice($adminMenu, 3),
        ['label' => 'Log Audit', 'route' => 'admin.audit.index', 'match' => 'admin.audit.*', 'icon' => 'scroll-text', 'section' => 'Modul', 'description' => 'Riwayat akses data sensitif.'],
        ['label' => 'Evaluasi Asisten AI', 'route' => 'admin.chat-logs.index', 'match' => 'admin.chat-logs.*', 'icon' => 'sparkles', 'section' => 'Modul', 'description' => 'Tinjau jawaban Asisten AI (pesan krisis disembunyikan).'],
    ],

    'pesantren-admin' => $adminMenu,

    'counselor' => [
        ['label' => 'Beranda', 'route' => 'counselor.dashboard', 'icon' => 'layout-dashboard'],
        ['label' => 'Antrean Permintaan', 'route' => 'counselor.requests.index', 'match' => 'counselor.requests.*', 'icon' => 'inbox', 'description' => 'Permintaan baru dari santri.'],
        ['label' => 'Kasus Saya', 'route' => 'counselor.cases.index', 'match' => ['counselor.cases.*', 'counselor.sessions.*'], 'icon' => 'folder-open', 'description' => 'Kasus aktif dan riwayat sesi.'],
        ['label' => 'Alih Tangan', 'route' => 'counselor.handovers.index', 'match' => 'counselor.handovers.*', 'icon' => 'arrow-left-right', 'description' => 'Permintaan alih tangan kasus.'],
        ['label' => 'Peringatan Krisis', 'route' => 'counselor.alerts.index', 'match' => 'counselor.alerts.*', 'icon' => 'siren', 'description' => 'Sinyal krisis yang perlu segera ditindaklanjuti.'],
        ['label' => 'Jadwal Piket', 'route' => 'counselor.shifts.index', 'match' => 'counselor.shifts.*', 'icon' => 'calendar-clock', 'description' => 'Jadwal piket penerima notifikasi krisis.'],
        ...$shared,
    ],

    'academic-advisor' => [
        ['label' => 'Beranda', 'route' => 'advisor.dashboard', 'icon' => 'layout-dashboard'],
        ['label' => 'Santri Binaan', 'route' => 'advisor.students.index', 'match' => 'advisor.students.*', 'icon' => 'users', 'description' => 'Hasil tes potensi santri binaan.'],
        ['label' => 'Slot Jadwal', 'route' => 'advisor.slots.index', 'match' => 'advisor.slots.*', 'icon' => 'calendar-plus', 'description' => 'Atur waktu bimbingan yang bisa dipesan.'],
        ['label' => 'Riwayat Sesi', 'route' => 'advisor.sessions.index', 'match' => 'advisor.sessions.*', 'icon' => 'history', 'description' => 'Catat hasil bimbingan.'],
        ...$shared,
    ],

    'alumni' => [
        ['label' => 'Beranda', 'route' => 'alumni.dashboard', 'icon' => 'layout-dashboard'],
        ['label' => 'Profil Alumni', 'route' => 'alumni.profile.edit', 'match' => 'alumni.profile.*', 'icon' => 'user-round', 'description' => 'Atur profil dan kontak yang ditampilkan.'],
        ['label' => 'Konsultasi', 'route' => 'consultations.index', 'match' => 'consultations.*', 'icon' => 'message-circle', 'description' => 'Pertanyaan dari santri.'],
        ...$shared,
    ],

    'santri' => [
        ['label' => 'Beranda', 'route' => 'santri.dashboard', 'icon' => 'house', 'mobile' => 'Beranda'],
        ['label' => 'Kerja & Magang', 'route' => 'programs.index', 'match' => 'programs.*', 'icon' => 'briefcase-business', 'description' => 'Program kerja dan magang luar negeri.'],
        ['label' => 'Info Kampus', 'route' => 'articles.index', 'match' => 'articles.*', 'icon' => 'graduation-cap', 'description' => 'SNBP, SNBT, mandiri, beasiswa, kuliah di luar negeri.'],
        ['label' => 'Bimbingan Kuliah', 'route' => 'guidance.index', 'match' => 'guidance.*', 'icon' => 'calendar-check', 'mobile' => 'Bimbingan', 'description' => 'Pesan jadwal dengan pembimbing akademik.'],
        ['label' => 'Tautan Resmi', 'route' => 'links.index', 'match' => 'links.*', 'icon' => 'shield-check', 'mobile' => 'Tautan', 'description' => 'Situs kampus yang sudah diverifikasi.'],
        ['label' => 'Tes Potensi', 'route' => 'tests.index', 'match' => ['tests.*', 'attempts.*'], 'icon' => 'clipboard-list', 'mobile' => 'Tes', 'description' => 'Petakan minat: Saintek, Soshum, Bahasa, Keagamaan.'],
        ['label' => 'Konseling', 'route' => 'counseling.index', 'match' => 'counseling.*', 'icon' => 'heart-handshake', 'description' => 'Ajukan sesi dengan konselor pesantren.'],
        ['label' => 'Direktori Alumni', 'route' => 'alumni.index', 'match' => ['alumni.index', 'alumni.show', 'consultations.*'], 'icon' => 'users', 'description' => 'Cari alumni dan ajukan konsultasi.'],
        ['label' => 'Asisten AI', 'route' => 'chat.index', 'match' => 'chat.*', 'icon' => 'sparkles', 'description' => 'Tanya seputar kampus, program, dan hasil tes.'],
        ['label' => 'Berkas Saya', 'route' => 'documents.index', 'match' => 'documents.*', 'icon' => 'folder-open', 'description' => 'Simpan rapor, sertifikat, dan surat.'],
    ],
];
