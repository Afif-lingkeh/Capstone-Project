{{-- resources/views/links/guide.blade.php --}}
@php
    $wrap = auth()->guest() ? 'mx-auto max-w-[1440px] px-5 lg:px-[120px]' : '';

    $fakes = [
        ['ul.ac.id', 'huruf i diganti l'],
        ['ui-ac.id.co', 'titik diganti tanda hubung'],
        ['daftar-ui.ac.id.online', 'domain asli disisipkan di depan'],
    ];

    $signs = [
        ['globe', 'Domain tidak berakhiran resmi', 'Perguruan tinggi di Indonesia memakai .ac.id. Sekolah kedinasan memakai .ac.id atau domain kementerian .go.id. Waspadai .com, .online, .site, atau .co untuk kampus dalam negeri. Ingat juga: ikon gembok hanya berarti koneksinya terenkripsi, bukan bukti situsnya asli.'],
        ['spell-check', 'Nama domain mirip, beda satu huruf', 'Baca alamatnya pelan-pelan, huruf demi huruf. Penipu sering mengganti i dengan l, o dengan angka 0, atau menambah tanda hubung.'],
        ['wallet', 'Meminta transfer ke rekening pribadi', 'Biaya resmi selalu dibayar lewat bank atau virtual account atas nama kampus, bukan ke rekening pribadi seseorang.'],
        ['badge-alert', 'Menjanjikan “pasti lulus” atau jalur khusus berbayar', 'Kursi kuliah tidak bisa dibeli. Tawaran “jalur titipan” hampir selalu penipuan, dan keberkahan tidak datang dari jalan seperti itu.'],
        ['forward', 'Disebar lewat pesan berantai tanpa sumber', 'Tabayyun dulu: cocokkan dengan direktori PEDULI atau PDDikti. Kalau masih ragu, tanyakan ke pembimbing akademikmu.'],
    ];

    $checks = [
        'Domain sama persis dengan direktori',
        'Kamu buka sendiri, bukan dari tautan kiriman',
        'Pembayaran atas nama institusi',
        'Info juga ada di situs kementerian',
    ];
@endphp

<x-layouts.adaptive title="Kenali Situs Kampus Palsu">
    <section @class(['bg-sand' => auth()->guest()])>
        <div @class([$wrap, 'flex flex-col gap-5', 'pb-14 pt-10 lg:pt-16' => auth()->guest()])>
            @guest
                <nav aria-label="Breadcrumb">
                    <ol class="flex items-center gap-2 text-[13px]">
                        <li><a href="{{ route('links.index') }}" class="font-semibold text-slate hover:underline">Tautan Resmi</a></li>
                        <li aria-hidden="true"><x-icon name="chevron-right" class="size-3.5 text-mist" /></li>
                        <li><span aria-current="page" class="font-bold text-ink">Kenali situs palsu</span></li>
                    </ol>
                </nav>
            @endguest
            <p class="eyebrow text-gold-ink">Panduan aman · 5 menit membaca</p>
            @guest
                <h1 class="max-w-[860px] text-[36px] font-extrabold leading-[1.05] tracking-[-0.03em] text-ink lg:text-[56px]">Kenali situs kampus palsu sebelum mendaftar</h1>
            @endguest
            <p class="max-w-[720px] text-[17px] leading-[1.6] text-slate lg:text-[19px]">Setiap musim pendaftaran, banyak situs tiruan yang meminta uang atau data pribadi. Jangan sampai ikhtiarmu dimanfaatkan orang lain. Lima tanda ini membantumu membedakan yang asli dari yang palsu.</p>
        </div>
    </section>

    {{-- Asli vs palsu --}}
    <section aria-label="Contoh alamat asli dan palsu">
        <div @class([$wrap, 'flex flex-col gap-6 pt-10 lg:flex-row lg:items-start lg:pt-14'])>
            <div class="flex flex-col gap-4 rounded-3xl bg-success-wash p-7 ring-[1.5px] ring-inset ring-success/25 lg:w-[420px]">
                <p class="flex items-center gap-2 text-[13px] font-extrabold tracking-[0.12em] text-success"><x-icon name="circle-check" />ASLI</p>
                <p class="flex items-center gap-2.5 rounded-full bg-white px-[18px] py-3.5">
                    <x-icon name="lock" class="size-4 text-success" />
                    <span class="break-all font-mono text-[17px] font-bold">https://www.ui.ac.id</span>
                </p>
                <p class="text-sm leading-[1.57]">Alamatnya sama persis dengan yang ada di direktori PEDULI dan PDDikti.</p>
            </div>
            <div class="flex flex-1 flex-col gap-3 rounded-3xl bg-danger-wash p-7 ring-[1.5px] ring-inset ring-danger-line">
                <p class="flex items-center gap-2 text-[13px] font-extrabold tracking-[0.12em] text-danger-ink"><x-icon name="circle-x" class="text-danger" />PALSU · CONTOH YANG SERING MUNCUL</p>
                <ul class="flex flex-col gap-3">
                    @foreach ($fakes as [$url, $why])
                        <li class="flex flex-wrap items-center gap-x-3 gap-y-1 rounded-3xl bg-white px-[18px] py-3 sm:rounded-full">
                            <x-icon name="triangle-alert" class="size-4 text-danger" />
                            <span class="break-all font-mono text-base font-bold text-danger-ink">{{ $url }}</span>
                            <span class="text-[13px] font-medium text-slate">· {{ $why }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </section>

    {{-- Lima tanda + daftar cek --}}
    <section aria-label="Lima tanda situs palsu">
        <div @class([$wrap, 'flex flex-col gap-12 pt-10 lg:flex-row lg:gap-16', 'pb-16 lg:pb-24' => auth()->guest(), 'pb-6' => auth()->check()])>
            <ol class="flex-1 divide-y divide-hairline">
                @foreach ($signs as $i => [$icon, $title, $text])
                    <li class="flex gap-5 py-7 sm:gap-6">
                        <span class="w-11 shrink-0 font-accent text-[40px] font-semibold italic leading-10 text-gold" aria-hidden="true">{{ sprintf('%02d', $i + 1) }}</span>
                        <div class="flex flex-1 flex-col gap-2">
                            <h2 class="flex items-start gap-2.5 text-xl font-extrabold leading-snug lg:text-[22px]"><x-icon :name="$icon" class="mt-1 text-blue" />{{ $title }}</h2>
                            <p class="text-base leading-[1.625] text-slate">{{ $text }}</p>
                        </div>
                    </li>
                @endforeach
            </ol>

            <aside class="flex flex-col gap-5 lg:w-[380px]">
                <div class="flex flex-col gap-3.5 rounded-3xl bg-cloud p-7 ring-1 ring-inset ring-hairline">
                    <h2 class="text-lg font-extrabold">Cek cepat sebelum mendaftar</h2>
                    <ul class="flex flex-col gap-1">
                        @foreach ($checks as $check)
                            <li class="flex items-center gap-3 py-1 text-sm font-medium">
                                <span class="flex size-[22px] shrink-0 items-center justify-center rounded-[7px] bg-blue"><x-icon name="check" class="size-3.5 text-white" /></span>
                                {{ $check }}
                            </li>
                        @endforeach
                    </ul>
                    <a href="{{ route('links.index') }}" class="btn-primary w-full">Buka direktori resmi <x-icon name="arrow-right" class="size-[18px]" /></a>
                </div>
                <x-alert variant="warning" icon="flag" title="Menemukan situs yang mencurigakan?">
                    Kabari pembimbing akademikmu supaya teman-temanmu ikut terlindungi.
                </x-alert>
            </aside>
        </div>
    </section>
</x-layouts.adaptive>
