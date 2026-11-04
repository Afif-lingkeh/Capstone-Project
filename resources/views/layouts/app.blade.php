{{-- resources/views/layouts/app.blade.php --}}
{{-- Layout aplikasi (desain 03–07): sidebar + topbar. Admin memakai sidebar navy, peran lain sidebar putih.
     Santri di HP mendapat navigasi bawah (desain "Nav/Bottom"). --}}
@php
    $user = Auth::user();
    $role = $user->primaryRole();
    $dark = in_array($role, [\App\Enums\Role::SuperAdmin, \App\Enums\Role::PesantrenAdmin], true);
    $sections = collect($navigation)->groupBy(fn ($item) => $item['section'] ?? 'Menu '.$role?->label());
    $bottomNav = $role === \App\Enums\Role::Santri ? collect($navigation)->whereNotNull('mobile')->values() : collect();
    $isActive = fn (array $item) => request()->routeIs(...$item['match']);
    $unread = $user->unreadNotifications()->count();
    $profile = $user->santriProfile;
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($header) && trim($header) !== '' ? trim(strip_tags($header)).' — ' : '' }}{{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800|fraunces:600i|jetbrains-mono:700|amiri:700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-cloud font-sans text-ink antialiased">
<a href="#konten" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[60] focus:rounded-xl focus:bg-ink focus:px-4 focus:py-3 focus:font-bold focus:text-white">Langsung ke konten</a>

<div x-data="{ sidebarOpen: false }" @keydown.escape.window="sidebarOpen = false" class="min-h-screen">
    {{-- Latar gelap saat sidebar terbuka di layar HP --}}
    <div x-show="sidebarOpen" x-transition.opacity x-cloak @click="sidebarOpen = false" class="fixed inset-0 z-40 bg-ink/40 lg:hidden"></div>

    <aside id="sidebar" :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
           @class([
               'fixed inset-y-0 left-0 z-50 flex w-[280px] -translate-x-full flex-col gap-6 overflow-y-auto overscroll-contain px-5 py-7 lg:translate-x-0 motion-safe:transition-transform motion-safe:duration-200',
               'bg-ink' => $dark,
               'border-r border-hairline bg-white' => ! $dark,
           ])>
        <div class="flex items-center justify-between">
            <a href="{{ route('dashboard') }}" aria-label="PEDULI, ke beranda"><x-brand :inverse="$dark" /></a>
            <button type="button" @click="sidebarOpen = false" @class(['flex size-11 items-center justify-center rounded-xl lg:hidden', 'text-white hover:bg-white/10' => $dark, 'text-ink hover:bg-pebble' => ! $dark])>
                <x-icon name="x" /><span class="sr-only">Tutup menu</span>
            </button>
        </div>

        <nav aria-label="Menu utama" class="flex flex-1 flex-col gap-4">
            @foreach ($sections as $section => $items)
                <div class="flex flex-col gap-0.5">
                    <p @class(['px-3.5 pb-1.5 text-[11px] font-bold uppercase tracking-[0.13em]', 'text-mist' => $dark, 'text-slate' => ! $dark])>{{ $section }}</p>
                    @foreach ($items as $item)
                        @php($active = $isActive($item))
                        <a href="{{ route($item['route']) }}" @if ($active) aria-current="page" @endif
                           @class([
                               'flex items-center gap-3 rounded-xl px-3.5 py-3 text-[15px] transition-colors duration-150',
                               'bg-white/10 font-bold text-white' => $dark && $active,
                               'font-semibold text-hairline hover:bg-white/5 hover:text-white' => $dark && ! $active,
                               'bg-blue-wash font-bold text-cobalt' => ! $dark && $active,
                               'font-semibold text-ink hover:bg-pebble' => ! $dark && ! $active,
                           ])>
                            <x-icon :name="$item['icon']" @class(['text-teal-light' => $dark && $active, 'text-mist' => $dark && ! $active, 'text-cobalt' => ! $dark && $active, 'text-slate' => ! $dark && ! $active]) />
                            <span class="flex-1">{{ $item['label'] }}</span>
                        </a>
                    @endforeach
                </div>
            @endforeach
        </nav>

        @if ($role === \App\Enums\Role::Santri && Route::has('counseling.index'))
            <div class="flex flex-col gap-2 rounded-[18px] bg-sand p-[18px]">
                <p class="text-sm font-bold">Hati sedang berat?</p>
                <p class="text-[13px] leading-5 text-slate">Konselor piket siap mendengar. Ceritamu dijaga sebagai amanah.</p>
                <a href="{{ route('counseling.create') }}" class="w-fit py-1 text-[13px] font-bold text-blue hover:underline">Mulai bercerita →</a>
            </div>
        @elseif ($dark)
            <p class="rounded-2xl bg-white/5 p-4 text-xs leading-5 text-mist">Admin tidak dapat membuka catatan konseling. Setiap akses data sensitif tercatat.</p>
        @endif
    </aside>

    <div class="lg:pl-[280px]">
        <header class="sticky top-0 z-30 flex h-[68px] items-center gap-3 border-b border-hairline bg-white pl-3 pr-3 sm:px-6 lg:h-[76px] lg:px-8">
            <button type="button" @click="sidebarOpen = true" aria-controls="sidebar" :aria-expanded="sidebarOpen.toString()" aria-expanded="false"
                    class="flex size-11 shrink-0 items-center justify-center rounded-2xl text-ink hover:bg-pebble lg:hidden">
                <x-icon name="menu" /><span class="sr-only">Buka menu</span>
            </button>

            <div class="flex min-w-0 flex-1 flex-col gap-0.5">
                <p class="truncate text-lg font-extrabold lg:text-[22px]">{{ $header ?? '' }}</p>
                <p class="hidden truncate text-[13px] font-medium text-slate sm:block">{{ $subtitle ?? now()->translatedFormat('l, j F Y') }}</p>
            </div>

            @if (Route::has('notifications.index'))
                <a href="{{ route('notifications.index') }}" class="relative flex size-11 shrink-0 items-center justify-center rounded-2xl bg-white ring-1 ring-inset ring-hairline hover:bg-pebble lg:size-12">
                    <x-icon name="bell" />
                    <span class="sr-only">Notifikasi{{ $unread ? ", $unread belum dibaca" : '' }}</span>
                    @if ($unread)
                        <span aria-hidden="true" class="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-danger px-1 text-[11px] font-bold text-white">{{ $unread > 9 ? '9+' : $unread }}</span>
                    @endif
                </a>
            @endif

            <x-dropdown align="right" width="48">
                <x-slot name="trigger">
                    <button type="button" class="flex items-center gap-3 rounded-full p-1 ring-1 ring-inset ring-hairline hover:bg-pebble sm:py-2 sm:pl-2 sm:pr-4">
                        <x-avatar :user="$user" size="size-9 sm:size-10" />
                        <span class="hidden text-left sm:block">
                            <span class="block max-w-[160px] truncate text-sm font-bold">{{ $user->name }}</span>
                            <span class="block text-xs font-medium text-slate">{{ $profile ? $profile->level->label().' · Kelas '.$profile->class_name : $role?->label() }}</span>
                        </span>
                        <x-icon name="chevron-down" class="hidden size-4 text-slate sm:block" />
                        <span class="sr-only">Menu akun</span>
                    </button>
                </x-slot>
                <x-slot name="content">
                    <x-dropdown-link :href="route('profile.edit')">Profil saya</x-dropdown-link>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="block w-full px-4 py-3 text-start text-sm font-semibold text-danger-ink hover:bg-pebble">Keluar</button>
                    </form>
                </x-slot>
            </x-dropdown>
        </header>

        <main id="konten" @class(['px-5 pb-12 pt-6 lg:px-10 lg:pt-8', 'pb-28 lg:pb-12' => $bottomNav->isNotEmpty()])>
            @foreach (['success' => 'success', 'warning' => 'warning', 'error' => 'danger'] as $key => $variant)
                @if (session($key))
                    <x-alert :variant="$variant" role="status" class="mb-6" x-data="{ show: true }" x-show="show">{{ session($key) }}</x-alert>
                @endif
            @endforeach
            @if ($errors->any())
                <x-alert variant="danger" title="Ada yang perlu diperbaiki" class="mb-6">{{ $errors->first() }}</x-alert>
            @endif

            {{ $slot }}
        </main>
    </div>

    @if ($bottomNav->isNotEmpty())
        <nav aria-label="Menu cepat" class="fixed inset-x-0 bottom-0 z-30 flex h-[76px] gap-1 border-t border-hairline bg-white px-2 pb-3 pt-2 shadow-[0_-4px_20px_rgb(11_53_88/0.08)] lg:hidden">
            @foreach ($bottomNav as $item)
                @php($active = $isActive($item))
                <a href="{{ route($item['route']) }}" @if ($active) aria-current="page" @endif
                   @class(['flex flex-1 flex-col items-center justify-center gap-1 rounded-[14px] text-[11px]', 'bg-blue-wash font-bold text-cobalt' => $active, 'font-semibold text-slate' => ! $active])>
                    <x-icon :name="$item['icon']" class="size-[22px]" />{{ $item['mobile'] }}
                </a>
            @endforeach
            <button type="button" @click="sidebarOpen = true" class="flex flex-1 flex-col items-center justify-center gap-1 rounded-[14px] text-[11px] font-semibold text-slate">
                <x-icon name="layout-grid" class="size-[22px]" />Lainnya
            </button>
        </nav>
    @endif
</div>
</body>
</html>
