{{-- resources/views/auth/reset-password.blade.php --}}
<x-guest-layout title="Buat Password Baru">
    <a href="{{ route('login') }}" class="flex w-fit items-center gap-1.5 py-1 text-sm font-bold text-blue hover:underline">
        <x-icon name="arrow-left" class="size-4" /> Kembali ke halaman masuk
    </a>

    <div class="flex flex-col gap-2.5">
        <h1 class="text-[28px] font-extrabold leading-[1.14] tracking-[-0.03em] lg:text-4xl">Buat password baru</h1>
        <p class="text-[15px] leading-6 text-slate">Pilih password yang mudah kamu ingat tapi sulit ditebak orang lain. Jangan samakan dengan akun lain.</p>
    </div>

    <form method="POST" action="{{ route('password.store') }}" class="flex flex-col gap-6"
          x-data="{ pw: '', pw2: '' }">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div class="flex flex-col gap-[18px]">
            <div class="flex flex-col gap-2">
                <x-input-label for="email" value="Email" />
                <x-icon-input id="email" icon="mail" type="email" name="email" :value="old('email', $request->email)" required autocomplete="username"
                              :invalid="$errors->has('email')" aria-describedby="email-error" />
                <x-input-error id="email-error" :messages="$errors->get('email')" />
            </div>
            <div class="flex flex-col gap-2">
                <x-input-label for="password" value="Password baru" />
                <x-icon-input id="password" icon="lock" type="password" name="password" required autofocus autocomplete="new-password" x-model="pw"
                              :invalid="$errors->has('password')" aria-describedby="password-rules password-error" />
                <x-input-error id="password-error" :messages="$errors->get('password')" />
            </div>
            <div class="flex flex-col gap-2">
                <x-input-label for="password_confirmation" value="Ulangi password baru" />
                <x-icon-input id="password_confirmation" icon="lock" type="password" name="password_confirmation" required autocomplete="new-password" x-model="pw2"
                              :invalid="$errors->has('password_confirmation')" aria-describedby="password_confirmation-error" />
                <x-input-error id="password_confirmation-error" :messages="$errors->get('password_confirmation')" />
            </div>
        </div>

        {{-- Syarat password (sama dengan aturan di server): ikon centang + teks, bukan warna saja. --}}
        <ul id="password-rules" class="flex flex-col gap-2 rounded-[14px] bg-cloud p-4 text-[13px] font-semibold">
            @foreach ([['pw.length >= 8', 'Minimal 8 karakter'], ['/[a-zA-Z]/.test(pw) && /\d/.test(pw)', 'Berisi huruf dan angka'], ['pw.length > 0 && pw === pw2', 'Kedua password sama']] as [$rule, $text])
                <li class="flex items-center gap-2" x-data="{ get ok() { return {{ $rule }} } }">
                    <x-icon name="circle-check" class="size-4 text-success" x-show="ok" x-cloak />
                    <x-icon name="circle" class="size-4 text-mist" x-show="! ok" />
                    <span :class="ok ? 'text-ink' : 'text-slate'">{{ $text }}</span>
                    <span class="sr-only" x-text="ok ? '(terpenuhi)' : '(belum)'"></span>
                </li>
            @endforeach
        </ul>

        <x-primary-button class="w-full py-4">Simpan password</x-primary-button>
    </form>
</x-guest-layout>
