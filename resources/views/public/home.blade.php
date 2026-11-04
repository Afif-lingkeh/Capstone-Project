{{-- resources/views/public/home.blade.php --}}
@php
    // Fitur selain direktori ada di balik login: arahkan tamu ke halaman masuk.
    $memberUrl = auth()->check() ? route('dashboard') : route('login');

    $features = [
        ['Kerja & magang luar negeri', 'Program resmi bersama mitra di Jepang, Rusia, dan negara lain, lengkap dengan syarat dan biayanya.', 'Lihat program', 'briefcase-business', 'bg-teal-wash text-teal-ink'],
        ['Info kampus & SNBP', 'Panduan SNBP, SNBT, jalur mandiri, dan beasiswa dengan bahasa yang mudah dipahami.', 'Baca panduan', 'graduation-cap', 'bg-blue-wash text-blue'],
        ['Tes potensi', 'Kenali kekuatan yang Allah titipkan padamu: Saintek, Soshum, Bahasa, atau Keagamaan.', 'Mulai tes', 'clipboard-list', 'bg-warning-wash text-warning'],
        ['Teman bercerita', 'Saat hati sedang berat, ada konselor yang siap mendengar. Ceritamu dijaga sebagai amanah.', 'Mulai bercerita', 'heart-handshake', 'bg-violet-wash text-violet-ink'],
    ];

    $steps = [
        ['01', 'log-in', 'Masuk dengan NIS', 'Akunmu sudah disiapkan pesantren. Cukup masuk dengan NIS, username, atau email.'],
        ['02', 'clipboard-list', 'Kenali potensimu', 'Jawab dengan jujur, lalu lihat jalur mana yang paling sesuai dengan kekuatanmu.'],
        ['03', 'calendar-check', 'Bermusyawarah dengan pembimbing', 'Pilih waktu yang kosong, lalu diskusikan rencanamu bersama ustadz atau ustadzah pembimbing.'],
    ];

    $commitments = [
        ['lock', 'Catatan konseling dirahasiakan', 'Hanya konselor yang mendampingimu yang dapat membacanya. Admin pun tidak bisa.'],
        ['eye-off', 'Nomor HP tidak pernah ditampilkan', 'Alumni sendiri yang menentukan kontak mana yang dapat dilihat.'],
        ['scroll-text', 'Setiap akses tercatat', 'Siapa membuka apa dan kapan, semuanya tercatat. Isi ceritamu tidak ikut dicatat.'],
        ['user-check', 'Dengan izin orang tua', 'Konseling dan Asisten AI baru dibuka setelah orang tua atau wali memberi persetujuan.'],
    ];

    $domains = ['ui.ac.id', 'itb.ac.id', 'ugm.ac.id', 'pknstan.ac.id', 'stis.ac.id', 'uin-jkt.ac.id', 'uniba.sk', 'azhar.edu.eg'];
@endphp

<x-layouts.public>
    {{-- Hero --}}
    <section class="relative overflow-hidden">
        <div aria-hidden="true" class="pointer-events-none absolute inset-0">
            <div class="absolute left-[60%] top-10 size-[520px] rounded-full bg-[#0099ff]/[.18] blur-[55px]"></div>
            <div class="absolute left-[78%] -top-20 size-[360px] rounded-full bg-[#e55cff]/20 blur-[50px]"></div>
            <div class="absolute left-[70%] top-[420px] size-80 rounded-full bg-teal/[.18] blur-[45px]"></div>
        </div>

        <div class="relative mx-auto flex max-w-[1440px] flex-col items-center gap-10 px-5 pb-16 pt-10 lg:flex-row lg:px-[120px] lg:pb-24 lg:pt-[72px]">
            <div class="flex w-full flex-1 flex-col gap-6 lg:gap-7">
                <p class="flex w-fit items-center gap-2 rounded-[19px] bg-blue-wash py-2 pl-2 pr-3.5 text-[13px] font-bold text-cobalt">
                    <x-application-logo class="size-8 lg:size-10" />
                    {{ config('peduli.pesantren_name') }}
                </p>

                <h1 class="text-[40px] font-extrabold leading-[1.08] tracking-[-0.03em] text-ink sm:text-5xl lg:text-[64px]">
                    Langkah yang <em class="font-accent text-[1.09em] font-semibold text-blue">pasti</em><br class="hidden sm:block">
                    menuju kampus &amp; karier impian.
                </h1>

                <p class="max-w-[540px] text-[17px] leading-[1.6] text-slate lg:text-[19px]">
                    Ikhtiar menuju masa depan dimulai dari informasi yang benar. Temukan situs resmi kampus, peluang kerja ke luar negeri, tes potensi, bimbingan, dan teman bercerita, semuanya sudah diperiksa pesantren.
                </p>

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <a href="{{ route('links.index') }}" class="btn-primary py-[18px] pl-7 pr-[26px]">
                        Lihat tautan resmi <x-icon name="arrow-right" class="size-[18px]" />
                    </a>
                    @auth
                        <a href="{{ route('dashboard') }}" class="btn-secondary px-7 py-[18px]">Buka dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="btn-secondary px-7 py-[18px]">Masuk sebagai santri</a>
                    @endauth
                </div>

                <ul class="flex flex-col gap-3 sm:flex-row sm:gap-6">
                    <li class="flex items-center gap-2 text-sm font-semibold"><x-icon name="shield-check" class="size-[18px] text-success" />Hanya tautan yang sudah dicek</li>
                    <li class="flex items-center gap-2 text-sm font-semibold"><x-icon name="lock" class="size-[18px] text-success" />Data santri dijaga sebagai amanah</li>
                </ul>
            </div>

            {{-- Visual: foto + kartu contoh. Hanya ilustrasi, jadi disembunyikan dari pembaca layar. --}}
            <div aria-hidden="true" class="relative hidden h-[600px] w-[560px] shrink-0 lg:block">
                <img src="{{ asset('images/beranda-santri.webp') }}" alt="" width="440" height="580"
                     class="absolute left-20 top-0 h-[580px] w-[440px] rounded-[220px_220px_32px_32px] object-cover outline outline-1 -outline-offset-1 outline-black/10">
                <div class="absolute left-20 top-[380px] h-[200px] w-[440px] rounded-b-4xl bg-gradient-to-b from-ink/0 to-ink/35"></div>

                <div class="absolute left-0 top-[360px] w-[340px]">
                    <x-institution-card name="Universitas Indonesia" location="Depok, Jawa Barat" tag="PTN" domain="ui.ac.id" checked="Dicek hari ini" class="shadow-float" />
                </div>

                <div class="absolute left-[290px] top-[84px] flex items-center gap-2.5 rounded-full bg-white py-2 pl-2 pr-4 shadow-float ring-1 ring-black/5">
                    <span class="flex size-10 items-center justify-center rounded-full bg-gold-wash text-sm font-bold text-gold-ink">RM</span>
                    <span class="flex flex-col gap-0.5">
                        <span class="text-[13px] font-bold">Konsultasi dengan alumni di Slovakia</span>
                        <span class="text-xs font-medium text-slate">Biasanya dibalas dalam 2 hari</span>
                    </span>
                </div>

                <div class="absolute left-[368px] top-[250px] flex items-center gap-2.5 rounded-[22px] bg-white py-2.5 pl-2.5 pr-3.5 shadow-float ring-1 ring-black/5">
                    <span class="flex size-10 flex-col items-center justify-center rounded-xl bg-blue-wash">
                        <span class="text-[9px] font-extrabold tracking-[0.08em] text-cobalt">KAM</span>
                        <span class="text-[15px] font-extrabold leading-none">05</span>
                    </span>
                    <span class="flex flex-col gap-0.5">
                        <span class="text-[13px] font-bold">Bimbingan SNBP</span>
                        <span class="text-xs font-medium text-slate">09.00 WIB · Daring</span>
                    </span>
                </div>

                <x-star class="absolute left-[530px] top-5 text-gold opacity-60" />
            </div>
        </div>
    </section>

    {{-- Pita domain resmi --}}
    <section aria-labelledby="pita-domain" class="border-y border-hairline bg-cloud">
        <div class="mx-auto flex max-w-[1440px] flex-col items-center gap-5 px-5 py-8 lg:px-[120px]">
            <h2 id="pita-domain" class="text-center text-xs font-bold uppercase tracking-[0.13em] text-slate">Situs resmi yang sudah kami periksa</h2>
            <ul class="flex flex-wrap justify-center gap-2 sm:gap-3">
                @foreach ($domains as $domain)
                    <li class="flex items-center gap-2 rounded-full bg-white px-4 py-2.5 shadow-card ring-1 ring-black/5">
                        <x-icon name="lock" class="size-3.5 text-success" />
                        <span class="font-mono text-sm font-bold">{{ $domain }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>

    {{-- Fitur --}}
    <section aria-labelledby="fitur" class="mx-auto flex max-w-[1440px] flex-col gap-12 px-5 py-16 lg:px-[120px] lg:py-28">
        <div class="flex max-w-[640px] flex-col gap-3">
            <p class="eyebrow text-blue">Satu pintu, banyak jalan</p>
            <h2 id="fitur" class="text-[32px] font-extrabold leading-[1.1] tracking-[-0.03em] lg:text-[44px]">Bekal untuk setiap langkah setelah lulus</h2>
            <p class="text-[17px] leading-[1.6] text-slate">Mau kuliah di dalam negeri, belajar ke Timur Tengah, atau bekerja di Jepang? Apa pun pilihanmu, mulailah dari sini tanpa menebak-nebak mana situs yang asli.</p>
        </div>

        <div class="flex flex-col gap-6 lg:flex-row">
            <article class="relative flex flex-col gap-6 overflow-hidden rounded-4xl bg-ink p-7 text-white lg:w-[520px] lg:p-10">
                <x-star class="absolute right-16 top-7 text-blue opacity-50" />
                <span class="flex size-14 items-center justify-center rounded-2xl bg-white/10"><x-icon name="shield-check" class="size-[26px] text-teal-light" /></span>
                <h3 class="text-[26px] font-extrabold leading-[1.17] tracking-[-0.02em] lg:text-[30px]">Tautan resmi kampus &amp; kedinasan</h3>
                <p class="text-base leading-[1.625] text-hairline">Kami memeriksanya setiap pekan dan mencocokkannya dengan sumber resmi seperti PDDikti. Kalau ada yang janggal, tautannya kami sembunyikan dulu supaya kamu tidak tertipu.</p>
                <div aria-hidden="true" class="flex flex-col gap-1.5 rounded-2xl bg-white/[.06] px-[18px] py-3.5 ring-1 ring-inset ring-white/[.08]">
                    <span class="text-[11px] font-bold tracking-[0.13em] text-mist">SITUS RESMI</span>
                    <span class="flex items-center gap-2.5">
                        <x-icon name="lock" class="size-4 text-teal-light" />
                        <span class="flex-1 font-mono text-[22px] font-bold">pknstan.ac.id</span>
                    </span>
                </div>
                <a href="{{ route('links.index') }}" class="btn-primary w-fit py-3.5 pl-6 pr-[22px]">Buka direktori <x-icon name="arrow-right" class="size-[18px]" /></a>
            </article>

            <ul class="grid flex-1 gap-6 sm:grid-cols-2">
                @foreach ($features as [$title, $text, $cta, $icon, $tile])
                    <li class="flex flex-col gap-3.5 rounded-3xl bg-white p-7 shadow-card ring-1 ring-black/5">
                        <span class="flex size-12 items-center justify-center rounded-[14px] {{ $tile }}"><x-icon :name="$icon" class="size-[22px]" /></span>
                        <h3 class="text-lg font-extrabold">{{ $title }}</h3>
                        <p class="flex-1 text-sm leading-normal text-slate">{{ $text }}</p>
                        <a href="{{ $memberUrl }}" class="flex w-fit items-center gap-1.5 py-1 text-sm font-bold text-blue hover:underline">
                            {{ $cta }}<span class="sr-only">: {{ $title }}</span> <x-icon name="arrow-right" class="size-4" />
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>

    {{-- Cara kerja --}}
    <section aria-labelledby="langkah" class="bg-sand">
        <div class="mx-auto flex max-w-[1440px] flex-col gap-14 px-5 py-16 lg:px-[120px] lg:py-28">
            <div class="flex max-w-[760px] flex-col gap-3">
                <p class="eyebrow text-gold-ink">Mulai dengan bismillah</p>
                <h2 id="langkah" class="text-[32px] font-extrabold leading-[1.1] tracking-[-0.03em] lg:text-[44px]">Tiga langkah kecil, mulai hari ini</h2>
            </div>
            <ol class="grid gap-10 md:grid-cols-3 md:gap-8">
                @foreach ($steps as [$number, $icon, $title, $text])
                    <li class="flex flex-col gap-4 border-t-2 border-gold pt-7">
                        <div class="flex items-center justify-between">
                            <span class="font-accent text-5xl font-semibold italic text-gold-ink" aria-hidden="true">{{ $number }}</span>
                            <x-icon :name="$icon" class="size-6 text-ink" />
                        </div>
                        <h3 class="text-[22px] font-extrabold leading-7">{{ $title }}</h3>
                        <p class="text-[15px] leading-6 text-slate">{{ $text }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    {{-- Privasi --}}
    <section aria-labelledby="amanah" class="mx-auto flex max-w-[1440px] flex-col items-center gap-12 px-5 py-16 lg:flex-row lg:gap-[72px] lg:px-[120px] lg:py-28">
        <div class="relative hidden h-[600px] w-[520px] shrink-0 lg:block">
            <img src="{{ asset('images/beranda-konseling.webp') }}" alt="" width="520" height="600" loading="lazy"
                 class="size-full rounded-4xl object-cover outline outline-1 -outline-offset-1 outline-black/10">
            <p class="absolute bottom-6 left-6 flex items-center gap-2.5 rounded-full bg-white/95 py-2.5 pl-2.5 pr-4 text-[13px] font-bold shadow-float">
                <span class="flex size-8 items-center justify-center rounded-full bg-success-wash"><x-icon name="lock" class="size-4 text-success" /></span>
                Hanya konselormu yang bisa membacanya
            </p>
        </div>

        <div class="flex w-full flex-1 flex-col gap-8">
            <div class="flex flex-col gap-6">
                <div class="flex flex-col gap-3">
                    <p class="eyebrow text-blue">Amanah kami</p>
                    <h2 id="amanah" class="text-[32px] font-extrabold leading-[1.1] tracking-[-0.03em] lg:text-[44px]">Ceritamu adalah amanah</h2>
                    <p class="text-[17px] leading-[1.6] text-slate">Sebagian besar santri yang memakai PEDULI masih di bawah 18 tahun. Karena itu, hanya orang yang memang bertugas membantumu yang bisa melihat datamu.</p>
                </div>
                <a href="{{ route('privacy') }}" class="btn-secondary w-fit py-3.5 pl-6 pr-[22px]">Baca kebijakan privasi <x-icon name="arrow-right" class="size-[18px]" /></a>
            </div>

            <ul class="divide-y divide-hairline overflow-hidden rounded-[28px] bg-white shadow-card ring-1 ring-black/5">
                @foreach ($commitments as [$icon, $title, $text])
                    <li class="flex gap-4 px-6 py-5">
                        <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-success-wash"><x-icon :name="$icon" class="size-[18px] text-success" /></span>
                        <span class="flex flex-1 flex-col gap-1">
                            <span class="text-[15px] font-bold">{{ $title }}</span>
                            <span class="text-sm leading-[1.57] text-slate">{{ $text }}</span>
                        </span>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>

    {{-- Penutup --}}
    <section class="mx-auto max-w-[1440px] px-5 pb-16 lg:px-[120px] lg:pb-28">
        <div class="relative flex items-center gap-14 overflow-hidden rounded-[32px] bg-gradient-to-br from-blue to-cobalt px-7 py-10 lg:rounded-5xl lg:py-14 lg:pl-[72px] lg:pr-14">
            <div aria-hidden="true" class="pointer-events-none absolute -left-20 -top-[120px] size-[420px] rounded-full bg-teal-light/20 blur-[45px]"></div>
            <div class="relative flex flex-1 flex-col gap-5">
                <p lang="ar" dir="rtl" class="w-fit font-arabic text-[40px] font-bold text-gold-light">مَنْ جَدَّ وَجَدَ</p>
                <h2 class="text-[32px] font-extrabold leading-[1.1] tracking-[-0.03em] text-white lg:text-[44px]">Siapa bersungguh-sungguh, ia akan sampai.</h2>
                <p class="text-[17px] leading-[1.6] text-blue-wash lg:text-lg">Luruskan niat, sempurnakan ikhtiar, lalu bertawakal. Langkah pertamanya sederhana: periksa situs resmi kampus impianmu hari ini.</p>
                <div class="flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('links.index') }}" class="btn rounded-full bg-white py-[18px] pl-7 pr-[26px] text-ink hover:bg-blue-wash">Lihat tautan resmi <x-icon name="arrow-right" class="size-[18px]" /></a>
                    @guest
                        <a href="{{ route('login') }}" class="btn border-[1.5px] border-white/50 px-7 py-[18px] text-white hover:bg-white/10">Masuk</a>
                    @endguest
                </div>
            </div>
            <img src="{{ asset('images/beranda-mihrab.webp') }}" alt="" width="360" height="440" loading="lazy"
                 class="relative hidden h-[440px] w-[360px] rounded-[180px_180px_24px_24px] object-cover outline outline-1 -outline-offset-1 outline-white/10 lg:block">
        </div>
    </section>
</x-layouts.public>
