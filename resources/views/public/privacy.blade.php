{{-- resources/views/public/privacy.blade.php --}}
@php
    $toc = [
        'pengelola' => 'Pengelola data',
        'data' => 'Data yang kami kumpulkan',
        'tujuan' => 'Tujuan penggunaan',
        'persetujuan' => 'Persetujuan',
        'konseling' => 'Perlindungan data konseling',
        'asisten-ai' => 'Asisten AI',
        'akses' => 'Siapa yang dapat mengakses',
        'penyimpanan' => 'Penyimpanan',
        'hak' => 'Hak Anda',
        'kontak' => 'Kontak',
    ];
    $n = array_flip(array_keys($toc));
    $h2 = 'scroll-mt-6 text-2xl font-extrabold text-ink';
    $p = 'text-base leading-[1.625] text-slate';
@endphp

<x-layouts.public title="Kebijakan Privasi">
    <section class="mx-auto flex max-w-[1440px] flex-col gap-4 px-5 pb-10 pt-10 lg:px-[120px] lg:pt-16">
        <p class="eyebrow text-blue">Kebijakan privasi</p>
        <h1 class="max-w-[820px] text-[36px] font-extrabold leading-[1.08] tracking-[-0.03em] lg:text-[52px]">Bagaimana kami menjaga amanah data santri</h1>
        <p class="text-sm font-semibold text-slate">Terakhir diperbarui {{ config('peduli.privacy_policy_updated_at') }} · Versi {{ config('peduli.privacy_policy_version') }}</p>
        <x-alert variant="warning" icon="file-warning" title="Draf · wajib disahkan pimpinan pesantren sebelum dipublikasikan.">
            Isi kebijakan ini masih perlu ditinjau pihak pesantren.
        </x-alert>
    </section>

    <div class="mx-auto flex max-w-[1440px] flex-col gap-10 px-5 pb-16 lg:flex-row lg:items-start lg:gap-16 lg:px-[120px] lg:pb-24">
        <nav aria-labelledby="daftar-isi" class="flex flex-col gap-1 rounded-[20px] bg-cloud p-5 lg:sticky lg:top-6 lg:w-[280px] lg:shrink-0">
            <h2 id="daftar-isi" class="px-3 pb-1 text-[11px] font-bold tracking-[0.13em] text-slate">DAFTAR ISI</h2>
            <ol class="flex flex-col gap-1">
                @foreach ($toc as $id => $label)
                    <li>
                        <a href="#{{ $id }}" class="flex gap-2.5 rounded-[10px] px-3 py-2.5 text-[13px] font-semibold text-slate transition-colors duration-150 hover:bg-white hover:text-ink">
                            <span class="w-4 font-extrabold text-blue">{{ $loop->iteration }}</span>{{ $label }}
                        </a>
                    </li>
                @endforeach
            </ol>
        </nav>

        <article class="flex max-w-[720px] flex-1 flex-col gap-10">
            <p class="text-lg leading-[1.67] text-ink">Data yang kamu titipkan adalah amanah. Karena sebagian besar pengguna kami masih di bawah 18 tahun, kami hanya mengumpulkan data yang benar-benar diperlukan.</p>

            <section class="flex flex-col gap-3">
                <h2 id="pengelola" class="{{ $h2 }}">{{ $n['pengelola'] + 1 }}. Pengelola data</h2>
                <p class="{{ $p }}">Aplikasi Smart Career Center PEDULI dikelola oleh {{ config('peduli.pesantren_name') }} sebagai pengendali data pribadi. Kebijakan ini disusun mengikuti prinsip Undang-Undang Nomor 27 Tahun 2022 tentang Pelindungan Data Pribadi (UU PDP).</p>
            </section>

            <section class="flex flex-col gap-3">
                <h2 id="data" class="{{ $h2 }}">{{ $n['data'] + 1 }}. Data yang kami kumpulkan</h2>
                <ul class="{{ $p }} list-disc space-y-1 pl-6 marker:text-mist">
                    <li><strong class="text-ink">Data akun:</strong> nama, NIS atau username, jenis kelamin, email dan nomor HP (opsional).</li>
                    <li><strong class="text-ink">Data santri:</strong> jenjang, kelas, tahun masuk, dan nama wali.</li>
                    <li><strong class="text-ink">Hasil tes potensi:</strong> jawaban dan profil skor per jalur.</li>
                    <li><strong class="text-ink">Data konseling:</strong> permintaan dan catatan sesi konseling. Data ini termasuk data kesehatan, yaitu data pribadi yang bersifat spesifik.</li>
                    <li><strong class="text-ink">Percakapan dengan Asisten AI.</strong></li>
                    <li><strong class="text-ink">Data alumni:</strong> tahun lulus, kampus atau tempat kerja, negara, bidang, dan kontak yang dipilih untuk ditampilkan.</li>
                    <li><strong class="text-ink">Data keamanan:</strong> waktu login, alamat IP, dan catatan akses data sensitif.</li>
                </ul>
                <p class="{{ $p }}">Kami tidak mengumpulkan NIK, alamat rumah, maupun tanggal lahir.</p>
            </section>

            <section class="flex flex-col gap-3">
                <h2 id="tujuan" class="{{ $h2 }}">{{ $n['tujuan'] + 1 }}. Tujuan penggunaan</h2>
                <p class="{{ $p }}">Data hanya digunakan untuk layanan di aplikasi ini: informasi studi dan karier, bimbingan akademik, pemetaan minat dan potensi, layanan konseling, penghubung santri dengan alumni, serta menjaga keamanan sistem. Data tidak dijual dan tidak digunakan untuk iklan.</p>
            </section>

            <section class="flex flex-col gap-3">
                <h2 id="persetujuan" class="{{ $h2 }}">{{ $n['persetujuan'] + 1 }}. Persetujuan</h2>
                <p class="{{ $p }}">Sebagian besar santri berusia di bawah 18 tahun. Karena itu, pemrosesan data santri dilakukan berdasarkan persetujuan orang tua atau wali, sesuai ketentuan UU PDP tentang data pribadi anak. Persetujuan dapat ditarik dengan menghubungi pesantren.</p>
            </section>

            <section class="flex flex-col gap-3">
                <h2 id="konseling" class="{{ $h2 }}">{{ $n['konseling'] + 1 }}. Perlindungan khusus data konseling</h2>
                <ul class="{{ $p }} list-disc space-y-1 pl-6 marker:text-mist">
                    <li>Catatan konseling disimpan dalam bentuk terenkripsi.</li>
                    <li>Catatan hanya dapat dibaca oleh konselor yang menangani kasus. Admin aplikasi tidak dapat membacanya.</li>
                    <li>Setiap akses dan perubahan catatan konseling dicatat (siapa, kapan, dan tindakan apa).</li>
                </ul>
            </section>

            <section class="flex flex-col gap-3">
                <h2 id="asisten-ai" class="{{ $h2 }}">{{ $n['asisten-ai'] + 1 }}. Asisten AI</h2>
                <p class="{{ $p }}">Pesan yang dikirim ke Asisten AI diproses oleh penyedia layanan AI (Anthropic) yang servernya berada di luar Indonesia, untuk menghasilkan jawaban. Asisten AI tidak memiliki akses ke catatan konseling. Mohon tidak menuliskan data pribadi yang tidak diperlukan. Jika sebuah pesan menunjukkan tanda bahaya bagi keselamatan santri, konselor piket akan diberi tahu agar dapat segera membantu.</p>
            </section>

            <section class="flex flex-col gap-3">
                <h2 id="akses" class="{{ $h2 }}">{{ $n['akses'] + 1 }}. Siapa yang dapat mengakses data</h2>
                <p class="{{ $p }}">Akses dibatasi sesuai peran. Pembimbing akademik hanya melihat hasil tes santri binaannya. Alumni tidak melihat nomor telepon santri. Santri tidak melihat nomor telepon pribadi alumni. Percakapan konsultasi santri dengan alumni dapat ditinjau admin pesantren untuk menjaga keamanan santri.</p>
            </section>

            <section class="flex flex-col gap-3">
                <h2 id="penyimpanan" class="{{ $h2 }}">{{ $n['penyimpanan'] + 1 }}. Penyimpanan</h2>
                <p class="{{ $p }}">Data disimpan selama diperlukan untuk tujuan di atas. Jangka waktu penyimpanan setelah santri lulus ditetapkan oleh pesantren dan akan dicantumkan di sini.</p>
            </section>

            <section class="flex flex-col gap-3">
                <h2 id="hak" class="{{ $h2 }}">{{ $n['hak'] + 1 }}. Hak Anda</h2>
                <p class="{{ $p }}">Anda (atau orang tua/wali untuk santri di bawah umur) berhak meminta informasi tentang data yang kami simpan, memperbaiki data yang keliru, menarik persetujuan, dan meminta penghapusan data, sesuai ketentuan UU PDP.</p>
            </section>

            <section class="flex items-center gap-4 rounded-[20px] bg-sand p-6">
                <span class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-white"><x-icon name="mail" class="text-gold-ink" /></span>
                <div class="flex flex-1 flex-col gap-0.5">
                    <h2 id="kontak" class="scroll-mt-6 text-base font-extrabold">{{ $n['kontak'] + 1 }}. Ada yang ingin ditanyakan?</h2>
                    <p class="text-sm leading-[1.64] text-slate">
                        Pertanyaan atau permintaan terkait data pribadi dapat disampaikan kepada admin pesantren
                        @if (config('peduli.contact_email'))
                            melalui <a href="mailto:{{ config('peduli.contact_email') }}" class="font-bold text-blue underline">{{ config('peduli.contact_email') }}</a>.
                        @else
                            secara langsung.
                        @endif
                    </p>
                </div>
            </section>
        </article>
    </div>
</x-layouts.public>
