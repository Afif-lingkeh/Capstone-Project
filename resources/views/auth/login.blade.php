{{-- resources/views/auth/login.blade.php --}}
<x-guest-layout title="Masuk">
    <div class="flex flex-col gap-2.5">
        <p class="text-xs font-bold tracking-[0.15em] text-teal-ink">ASSALAMU’ALAIKUM</p>
        <h1 class="text-[28px] font-extrabold leading-[1.14] tracking-[-0.03em] lg:text-4xl">Masuk ke PEDULI</h1>
        <p class="text-[15px] leading-6 text-slate">Santri cukup masuk dengan NIS. Ustadz, ustadzah, dan alumni memakai username atau email.</p>
    </div>

    <x-auth-session-status :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="flex flex-col gap-6">
        @csrf

        <div class="flex flex-col gap-[18px]">
            <div class="flex flex-col gap-2">
                <x-input-label for="login" value="NIS, username, atau email" />
                <x-icon-input id="login" icon="user" name="login" :value="old('login')" required autofocus autocomplete="username"
                              :invalid="$errors->has('login')" aria-describedby="login-error" />
                <x-input-error id="login-error" :messages="$errors->get('login')" />
            </div>

            <div class="flex flex-col gap-2">
                <x-input-label for="password" value="Password" />
                <x-icon-input id="password" icon="lock" type="password" name="password" required autocomplete="current-password"
                              :invalid="$errors->has('password')" aria-describedby="password-error" />
                <x-input-error id="password-error" :messages="$errors->get('password')" />
            </div>

            <div class="flex items-center justify-between gap-4">
                <label for="remember_me" class="flex items-center gap-3 py-3">
                    <input id="remember_me" type="checkbox" name="remember" class="size-[22px] rounded-[7px] border-[1.5px] border-mist text-blue focus:ring-focus">
                    <span class="text-[15px] font-medium">Ingat saya</span>
                </label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="py-3 text-sm font-bold text-blue hover:underline">Lupa password?</a>
                @endif
            </div>
        </div>

        <x-primary-button class="w-full py-4">Masuk <x-icon name="arrow-right" class="size-[18px]" /></x-primary-button>
    </form>

    <x-alert title="Belum punya akun?">
        Akun santri disiapkan oleh pesantren. Untuk alumni, silakan
        <a href="{{ route('alumni.register') }}" class="font-bold text-blue underline">daftar dulu</a>, nanti kami verifikasi.
    </x-alert>
</x-guest-layout>
