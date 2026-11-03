{{-- resources/views/errors/403.blade.php --}}
<x-layouts.public title="Akses Ditolak">
    <div class="flex min-h-[calc(100vh-80px)] flex-col items-center justify-center gap-6 bg-cloud px-5 py-16 text-center">
        <span class="flex size-24 items-center justify-center rounded-[28px] bg-white shadow-tile ring-1 ring-inset ring-hairline">
            <x-icon name="lock" class="size-10 text-blue" />
        </span>
        <p class="text-[13px] font-bold tracking-[0.15em] text-slate">KODE 403</p>
        <h1 class="max-w-2xl text-[32px] font-extrabold leading-[1.1] tracking-[-0.03em] lg:text-[44px]">Afwan, halaman ini bukan untukmu</h1>
        <p class="max-w-[520px] text-[17px] leading-7 text-slate">Halaman ini tidak tersedia untuk akunmu. Kalau menurutmu ini keliru, sampaikan ke admin pesantren, ya.</p>
        <div class="flex flex-col gap-3 sm:flex-row">
            <a href="{{ auth()->check() ? route('dashboard') : route('home') }}" class="btn-primary">
                {{ auth()->check() ? 'Kembali ke dashboard' : 'Kembali ke beranda' }} <x-icon name="arrow-right" class="size-[18px]" />
            </a>
            @auth
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn-secondary w-full">Masuk dengan akun lain</button>
                </form>
            @endauth
        </div>
    </div>
</x-layouts.public>
