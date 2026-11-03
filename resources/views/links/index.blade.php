{{-- resources/views/links/index.blade.php --}}
@php
    // Tamu: bagian selebar layar dengan gutter desain. Pengguna login: sudah di dalam layout aplikasi.
    $wrap = auth()->guest() ? 'mx-auto max-w-[1440px] px-5 lg:px-[120px]' : '';
    $chip = 'inline-flex shrink-0 items-center gap-2 whitespace-nowrap rounded-full border-[1.5px] px-[18px] py-3 text-sm font-semibold transition-colors duration-150';
@endphp

<x-layouts.adaptive title="Tautan Resmi Kampus & Sekolah Kedinasan">
    {{-- Judul halaman + kartu panduan --}}
    <section @class(['bg-white' => auth()->guest()])>
        <div @class([$wrap, 'flex flex-col gap-8 lg:flex-row lg:items-end lg:gap-12', 'pb-10 pt-10 lg:pt-16' => auth()->guest()])>
            <div class="flex flex-1 flex-col gap-4">
                @guest
                    <nav aria-label="Breadcrumb">
                        <ol class="flex items-center gap-2 text-[13px]">
                            <li><a href="{{ route('home') }}" class="font-semibold text-slate hover:underline">Beranda</a></li>
                            <li aria-hidden="true"><x-icon name="chevron-right" class="size-3.5 text-mist" /></li>
                            <li><span aria-current="page" class="font-bold text-ink">Tautan Resmi</span></li>
                        </ol>
                    </nav>
                    <h1 class="text-[36px] font-extrabold leading-[1.05] tracking-[-0.03em] text-ink lg:text-[56px]">Tautan resmi kampus &amp; sekolah kedinasan</h1>
                @endguest
                <p class="max-w-[640px] text-[17px] leading-[1.6] text-slate lg:text-lg">Semua tautan di sini sudah diperiksa pesantren. Sebelum mendaftar, pastikan alamat di browser-mu sama persis dengan yang tertulis di sini, ya.</p>
            </div>

            <aside class="relative flex flex-col gap-3.5 overflow-hidden rounded-3xl bg-ink p-7 text-white lg:w-[400px]">
                <x-star class="absolute right-8 top-6 text-blue opacity-50" />
                <span class="flex size-11 items-center justify-center rounded-xl bg-white/10"><x-icon name="triangle-alert" class="size-[22px] text-gold-light" /></span>
                <h2 class="text-xl font-extrabold">Kenali situs kampus palsu</h2>
                <p class="text-sm leading-[1.57] text-hairline">Lima tanda yang sering menjebak calon mahasiswa, dan cara sederhana untuk memeriksanya.</p>
                <a href="{{ route('links.guide') }}" class="flex w-fit items-center gap-1.5 py-1 text-sm font-bold text-teal-light after:absolute after:inset-0 hover:underline">
                    Pelajari tandanya <x-icon name="arrow-right" class="size-4" />
                </a>
            </aside>
        </div>
    </section>

    {{-- Pencarian & filter kategori --}}
    <section @class(['border-b border-hairline bg-white' => auth()->guest(), 'mt-8' => auth()->check()])>
        <div @class([$wrap, 'flex flex-col gap-4', 'py-6' => auth()->guest()])>
            <form method="GET" role="search" class="flex h-14 items-center gap-3 rounded-full border-[1.5px] border-hairline bg-white pl-5 pr-2 shadow-[0_1px_3px_rgb(11_53_88/0.08)] focus-within:border-blue focus-within:ring-2 focus-within:ring-focus">
                @if ($currentType)
                    <input type="hidden" name="type" value="{{ $currentType->value }}">
                @endif
                <x-icon name="search" class="text-slate" />
                <label for="cari-kampus" class="sr-only">Cari kampus</label>
                <input id="cari-kampus" type="search" name="q" value="{{ request('q') }}" placeholder="Cari kampus atau kota"
                       class="h-full min-w-0 flex-1 border-0 bg-transparent p-0 text-[15px] font-medium text-ink placeholder:text-slate focus:ring-0 focus-visible:outline-none">
                <button type="submit" class="btn-primary rounded-full px-5 py-2.5 text-sm shadow-none">Cari</button>
            </form>

            <nav aria-label="Kategori" class="-mx-5 flex gap-2.5 overflow-x-auto px-5 pb-1 lg:mx-0 lg:px-0">
                <a href="{{ route('links.index', request()->only('q')) }}" @if (! $currentType) aria-current="page" @endif @class([
                    $chip,
                    'border-ink bg-ink text-white' => ! $currentType,
                    'border-hairline bg-white text-ink hover:bg-pebble' => $currentType,
                ])>Semua <span @class(['text-xs font-bold', 'text-mist' => ! $currentType, 'text-slate' => $currentType])>{{ $typeCounts->sum() }}</span></a>
                @foreach ($types as $type)
                    <a href="{{ route('links.index', [...request()->only('q'), 'type' => $type->value]) }}" @if ($currentType === $type) aria-current="page" @endif @class([
                        $chip,
                        'border-ink bg-ink text-white' => $currentType === $type,
                        'border-hairline bg-white text-ink hover:bg-pebble' => $currentType !== $type,
                    ])>{{ $type->shortLabel() }} <span @class(['text-xs font-bold', 'text-mist' => $currentType === $type, 'text-slate' => $currentType !== $type])>{{ $typeCounts[$type->value] ?? 0 }}</span></a>
                @endforeach
            </nav>
        </div>
    </section>

    {{-- Hasil --}}
    <section aria-labelledby="hasil">
        <div @class([$wrap, 'flex flex-col gap-6', 'pb-16 pt-10 lg:pb-[72px]' => auth()->guest(), 'pt-6' => auth()->check()])>
            <h2 id="hasil" class="text-sm font-semibold text-slate">
                @if ($institutions->total() > 0)
                    {{ $institutions->firstItem() }}–{{ $institutions->lastItem() }} dari {{ $institutions->total() }} kampus dan sekolah yang sudah kami periksa
                @else
                    Tidak ada hasil
                @endif
            </h2>

            @if ($institutions->isNotEmpty())
                <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                    @foreach ($institutions as $institution)
                        <x-institution-card :institution="$institution" />
                    @endforeach
                </div>
            @else
                <div class="flex flex-col items-center gap-3 rounded-3xl border-[1.5px] border-dashed border-hairline bg-cloud px-6 py-12 text-center">
                    <span class="flex size-12 items-center justify-center rounded-2xl bg-white"><x-icon name="search" class="text-slate" /></span>
                    <p class="text-base font-extrabold">Belum ada tautan terverifikasi yang cocok</p>
                    <p class="max-w-md text-sm leading-[1.57] text-slate">Coba kata kunci lain atau pilih kategori “Semua”. Kalau tetap tidak ada, kampus itu belum kami periksa.</p>
                    @if (request()->hasAny(['q', 'type']))
                        <a href="{{ route('links.index') }}" class="btn-secondary mt-2 py-3">Hapus pencarian</a>
                    @endif
                </div>
            @endif

            {{ $institutions->links('pagination.peduli') }}

            <div class="flex flex-col gap-4 rounded-[20px] bg-sand px-6 py-6 sm:flex-row sm:items-center sm:gap-5 sm:px-7">
                <span class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-white"><x-icon name="message-circle" class="text-gold-ink" /></span>
                <div class="flex flex-1 flex-col gap-0.5">
                    <p class="text-base font-extrabold">Kampus incaranmu belum ada?</p>
                    <p class="text-sm leading-[1.57] text-slate">Ceritakan ke pembimbing akademikmu. Insyaallah akan kami periksa dulu sebelum ditampilkan di sini.</p>
                </div>
            </div>
        </div>
    </section>
</x-layouts.adaptive>
