{{-- resources/views/profile/partials/update-profile-information-form.blade.php --}}
<section>
    <header>
        <h2 class="text-lg font-extrabold">Informasi Profil</h2>
        <p class="mt-1 text-sm text-slate">Perbarui nama dan email Anda. Username hanya dapat diubah oleh admin.</p>
    </header>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="username" value="Username / NIS" />
            <x-text-input id="username" type="text" class="mt-1 block w-full bg-pebble" :value="$user->username" disabled />
        </div>

        <div>
            <x-input-label for="name" value="Nama" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" value="Email (opsional)" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" autocomplete="email" />
            <p class="mt-1 text-xs text-slate">Email diperlukan untuk fitur lupa password.</p>
            <x-input-error class="mt-2" :messages="$errors->get('email')" />
        </div>

        @if ($user->santriProfile)
            <div>
                <x-input-label for="aspiration" value="Cita-cita & kampus impian (opsional)" />
                <x-textarea id="aspiration" name="aspiration" rows="3" maxlength="500" class="mt-1 min-h-[96px]" placeholder="mis. Ingin kuliah Kedokteran di UGM atau melanjutkan ke Al-Azhar">{{ old('aspiration', $user->santriProfile->aspiration) }}</x-textarea>
                <p class="mt-1 text-xs text-slate">Dibaca pembimbing akademikmu agar bimbingannya lebih tepat.</p>
                <x-input-error class="mt-2" :messages="$errors->get('aspiration')" />
            </div>
        @endif

        <div class="flex items-center gap-4">
            <x-primary-button>Simpan</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)" class="text-sm text-slate">Tersimpan.</p>
            @endif
        </div>
    </form>
</section>
