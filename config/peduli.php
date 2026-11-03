<?php
// config/peduli.php

return [
    'pesantren_name' => "Pesantren Modern Daarul 'Uluum Lido",
    'contact_email' => env('PEDULI_CONTACT_EMAIL'),

    // Izinkan seeder data demo berjalan di production (hanya untuk server demo/penilaian capstone, JANGAN di server pesantren).
    'allow_demo_seed' => (bool) env('PEDULI_ALLOW_DEMO_SEED', false),
    // Password semua akun demo. Ganti di server yang bisa diakses publik.
    'demo_password' => env('PEDULI_DEMO_PASSWORD', 'password'),

    // Dipakai tabel consents (Fase 7): persetujuan merujuk versi kebijakan tertentu.
    'privacy_policy_version' => '1.0',
    'privacy_policy_updated_at' => '17 Oktober 2026',

    // Fase 7: nomor layanan krisis yang ditampilkan ke santri. Wajib diisi pesantren (mis. layanan kesehatan jiwa resmi).
    'crisis_hotline' => env('PEDULI_CRISIS_HOTLINE'),

    // Kata/frasa yang memicu peringatan krisis (huruf kecil). Disempurnakan bersama konselor/psikolog.
    // Peringatan hanya memberi tahu konselor; tidak pernah dipakai untuk menghukum santri.
    'crisis_keywords' => [
        'bunuh diri', 'ingin mati', 'pengen mati', 'mau mati', 'mengakhiri hidup', 'akhiri hidup',
        'tidak ingin hidup', 'gak mau hidup', 'nggak mau hidup', 'menyakiti diri', 'melukai diri',
        'sayat', 'self harm', 'selfharm', 'lebih baik mati', 'tidak ada gunanya hidup',
        'dipukuli', 'dilecehkan', 'pelecehan', 'diperkosa', 'kekerasan seksual',
    ],

    // Fase 9: Asisten AI (Claude API). Kunci API dibaca dari ANTHROPIC_API_KEY.
    'chat' => [
        'model' => env('PEDULI_CHAT_MODEL', 'claude-opus-5-5'),
        'effort' => env('PEDULI_CHAT_EFFORT', 'low'),   // percakapan singkat: effort rendah cukup dan hemat
        'daily_limit' => (int) env('PEDULI_CHAT_DAILY_LIMIT', 30), // pesan per santri per hari
        'history_turns' => 12,                           // jumlah pesan terakhir yang dikirim sebagai konteks
    ],

    // Pengecekan otomatis tautan resmi (Fase 3).
    'link_check' => [
        'interval_days' => 7,   // tiap tautan dicek ulang paling lambat seminggu sekali
        'timeout' => 15,        // detik menunggu respons situs
        'max_redirects' => 5,
        'delay_ms' => 500,      // jeda antar-situs agar tidak membebani server kampus
    ],
];
