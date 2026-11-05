{{-- resources/views/components/layouts/public.blade.php --}}
@props(['title' => null])
@php
    // Menu publik. Item yang route-nya belum dibuat (modul fase berikutnya) otomatis tidak tampil.
    $nav = collect([
        ['Tautan Resmi', 'links.index'],
        ['Info Kampus', 'campus.index'],
        ['Kerja Luar Negeri', 'programs.index'],
        ['Alumni', 'alumni.directory'],
    ])->filter(fn ($item) => Route::has($item[1]));

    $footer = [
        'Jelajahi' => collect([
            ['Tautan Resmi', 'links.index'],
            ['Kenali Situs Palsu', 'links.guide'],
            ['Info Kampus', 'campus.index'],
            ['Kerja Luar Negeri', 'programs.index'],
        ])->filter(fn ($item) => Route::has($item[1]))->map(fn ($item) => [$item[0], route($item[1])]),
        'Bantuan' => collect([
            ['Kebijakan Privasi', route('privacy')],
            auth()->check() ? ['Dashboard', route('dashboard')] : ['Masuk', route('login')],
            config('peduli.contact_email') ? ['Hubungi Pesantren', 'mailto:'.config('peduli.contact_email')] : null,
        ])->filter(),
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ? $title.' — ' : '' }}{{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800|fraunces:600i|jetbrains-mono:700|amiri:700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col bg-white font-sans text-ink antialiased">
    <a href="#konten" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-50 focus:rounded-xl focus:bg-ink focus:px-4 focus:py-3 focus:font-bold focus:text-white">Langsung ke konten</a>

    <header x-data="{ open: false }" @keydown.escape.window="open = false" class="relative z-30 border-b border-hairline bg-white">
        <div class="mx-auto flex h-[68px] max-w-[1440px] items-center justify-between gap-4 pl-5 pr-3 lg:h-20 lg:px-[120px]">
            <a href="{{ route('home') }}" aria-label="PEDULI, ke beranda">
                <x-brand />
            </a>

            <nav aria-label="Utama" class="hidden items-center gap-2 lg:flex">
                @foreach ($nav as [$label, $route])
                    <a href="{{ route($route) }}" @if (request()->routeIs($route)) aria-current="page" @endif
                       class="rounded-xl px-3.5 py-3 text-[15px] font-semibold text-ink transition-colors duration-150 hover:bg-pebble aria-[current=page]:bg-blue-wash aria-[current=page]:text-cobalt">{{ $label }}</a>
                @endforeach
            </nav>

            <div class="hidden items-center gap-2 lg:flex">
                <a href="{{ route('privacy') }}" class="btn-ghost px-4">Kebijakan privasi</a>
                @auth
                    <a href="{{ route('dashboard') }}" class="btn-dark px-[22px] py-3">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="btn-dark px-[22px] py-3">Masuk</a>
                @endauth
            </div>

            <button type="button" @click="open = ! open" :aria-expanded="open.toString()" aria-expanded="false" aria-controls="menu-publik"
                    class="flex size-12 items-center justify-center rounded-2xl text-ink hover:bg-pebble lg:hidden">
                <x-icon name="menu" x-show="! open" />
                <x-icon name="x" x-show="open" x-cloak />
                <span class="sr-only">Menu</span>
            </button>
        </div>

        <div id="menu-publik" x-show="open" x-cloak @click.outside="open = false"
             class="absolute inset-x-0 top-full border-b border-hairline bg-white px-5 pb-6 pt-2 shadow-float lg:hidden">
            <nav aria-label="Utama (HP)" class="flex flex-col">
                @foreach ($nav as [$label, $route])
                    <a href="{{ route($route) }}" @if (request()->routeIs($route)) aria-current="page" @endif
                       class="rounded-xl px-3 py-3.5 text-base font-semibold text-ink hover:bg-pebble aria-[current=page]:text-blue">{{ $label }}</a>
                @endforeach
                <a href="{{ route('privacy') }}" class="rounded-xl px-3 py-3.5 text-base font-semibold text-ink hover:bg-pebble">Kebijakan privasi</a>
            </nav>
            @auth
                <a href="{{ route('dashboard') }}" class="btn-dark mt-3 w-full">Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="btn-dark mt-3 w-full">Masuk</a>
            @endauth
        </div>
    </header>

    <main id="konten" class="flex-1">{{ $slot }}</main>

    <footer class="bg-ink">
        <div class="mx-auto flex max-w-[1440px] flex-col gap-10 px-5 pb-8 pt-12 lg:px-[120px] lg:pt-16">
            <div class="flex flex-col gap-10 md:flex-row md:gap-12">
                <div class="flex flex-col gap-4 md:w-[360px]">
                    <x-brand inverse />
                    <p class="text-[15px] leading-6 text-hairline">Smart Career Center {{ config('peduli.pesantren_name') }}.</p>
                </div>
                <div class="grid flex-1 grid-cols-2 gap-8 sm:gap-12">
                    @foreach ($footer as $heading => $links)
                        <nav aria-labelledby="footer-{{ Str::slug($heading) }}" class="flex flex-col gap-3">
                            <h2 id="footer-{{ Str::slug($heading) }}" class="text-xs font-bold uppercase tracking-[0.12em] text-mist">{{ $heading }}</h2>
                            @foreach ($links as [$label, $href])
                                <a href="{{ $href }}" class="w-fit py-1 text-[15px] font-semibold text-white hover:underline">{{ $label }}</a>
                            @endforeach
                        </nav>
                    @endforeach
                </div>
            </div>
            <div class="flex flex-col gap-3 border-t border-white/15 pt-6 md:flex-row md:items-center md:justify-between">
                <p class="flex flex-wrap items-center gap-x-3.5 gap-y-1">
                    <span lang="ar" dir="rtl" class="font-arabic text-2xl font-bold text-gold">مَنْ جَدَّ وَجَدَ</span>
                    <span class="text-sm font-medium text-hairline">Man jadda wajada — Siapa bersungguh-sungguh, ia akan sampai.</span>
                </p>
                <p class="text-[13px] font-medium text-mist">&copy; {{ now()->year }} {{ config('peduli.pesantren_name') }}</p>
            </div>
        </div>
    </footer>
</body>
</html>
