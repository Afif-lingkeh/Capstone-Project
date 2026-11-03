{{-- resources/views/auth/forgot-password.blade.php --}}
<x-guest-layout title="Lupa Password">
    <a href="{{ route('login') }}" class="flex w-fit items-center gap-1.5 py-1 text-sm font-bold text-blue hover:underline">
        <x-icon name="arrow-left" class="size-4" /> Kembali ke halaman masuk
    </a>

    <div class="flex flex-col gap-2.5">
        <h1 class="text-[28px] font-extrabold leading-[1.14] tracking-[-0.03em] lg:text-4xl">Lupa password? Tidak apa-apa.</h1>
        <p class="text-[15px] leading-6 text-slate">Tulis email yang terdaftar. Kami kirimkan tautan untuk membuat password baru.</p>
    </div>

    <x-auth-session-status :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="flex flex-col gap-6">
        @csrf
        <div class="flex flex-col gap-2">
            <x-input-label for="email" value="Email" />
            <x-icon-input id="email" icon="mail" type="email" name="email" :value="old('email')" required autofocus autocomplete="email"
                          placeholder="nama@contoh.id" :invalid="$errors->has('email')" aria-describedby="email-error" />
            <x-input-error id="email-error" :messages="$errors->get('email')" />
        </div>
        <x-primary-button class="w-full py-4">Kirim tautan reset</x-primary-button>
    </form>

    <x-alert variant="warning" title="Santri tanpa email?">
        Tidak masalah. Temui admin pesantren, nanti dibantu mengatur ulang password-mu.
    </x-alert>
</x-guest-layout>
