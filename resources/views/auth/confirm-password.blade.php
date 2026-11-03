{{-- resources/views/auth/confirm-password.blade.php --}}
<x-guest-layout title="Konfirmasi Password">
    <span class="flex size-16 items-center justify-center rounded-[20px] bg-success-wash"><x-icon name="shield-check" class="size-7 text-success" /></span>

    <div class="flex flex-col gap-2.5">
        <p class="text-xs font-bold tracking-[0.15em] text-teal-ink">AREA AMAN</p>
        <h1 class="text-[28px] font-extrabold leading-[1.14] tracking-[-0.03em] lg:text-4xl">Konfirmasi password</h1>
        <p class="text-[15px] leading-6 text-slate">Demi keamanan akunmu, masukkan password sekali lagi sebelum membuka pengaturan ini.</p>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}" class="flex flex-col gap-6">
        @csrf
        <div class="flex flex-col gap-2">
            <x-input-label for="password" value="Password" />
            <x-icon-input id="password" icon="lock" type="password" name="password" required autofocus autocomplete="current-password"
                          :invalid="$errors->has('password')" aria-describedby="password-error" />
            <x-input-error id="password-error" :messages="$errors->get('password')" />
        </div>
        <x-primary-button class="w-full py-4">Konfirmasi</x-primary-button>
    </form>
</x-guest-layout>
