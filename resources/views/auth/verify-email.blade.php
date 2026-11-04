{{-- resources/views/auth/verify-email.blade.php --}}
<x-guest-layout title="Verifikasi Email">
    <span class="flex size-16 items-center justify-center rounded-[20px] bg-blue-wash"><x-icon name="mail-check" class="size-7 text-blue" /></span>

    <div class="flex flex-col gap-2.5">
        <h1 class="text-[28px] font-extrabold leading-[1.14] tracking-[-0.03em] lg:text-4xl">Satu langkah lagi: periksa emailmu</h1>
        <p class="text-[15px] leading-6 text-slate">Kami sudah mengirim tautan verifikasi ke email akunmu. Buka emailnya, lalu klik tautan itu untuk mengaktifkan akunmu.</p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <x-alert variant="success" role="status" title="Tautan baru sudah dikirim.">Mohon tunggu 1–2 menit sebelum meminta tautan baru.</x-alert>
    @endif

    <div class="flex gap-3">
        <form method="POST" action="{{ route('verification.send') }}" class="flex-1">
            @csrf
            <x-primary-button class="w-full">Kirim ulang email</x-primary-button>
        </form>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn-ghost px-4 text-blue">Keluar</button>
        </form>
    </div>
</x-guest-layout>
