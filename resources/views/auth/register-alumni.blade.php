{{-- resources/views/auth/register-alumni.blade.php --}}
<x-guest-layout title="Daftar Alumni">
    <a href="{{ route('login') }}" class="flex w-fit items-center gap-1.5 py-1 text-sm font-bold text-blue hover:underline"><x-icon name="arrow-left" class="size-4" /> Kembali ke halaman masuk</a>

    <div class="flex flex-col gap-2.5">
        <p class="text-xs font-bold tracking-[0.15em] text-teal-ink">AHLAN WA SAHLAN, KAKAK ALUMNI</p>
        <h1 class="text-[28px] font-extrabold leading-[1.14] tracking-[-0.03em] lg:text-4xl">Daftar sebagai alumni</h1>
        <p class="text-[15px] leading-6 text-slate">Profilmu tampil di direktori setelah diverifikasi admin pesantren. Nomor WhatsApp hanya untuk admin, tidak pernah ditampilkan ke santri.</p>
    </div>

    <form method="POST" action="{{ route('alumni.register') }}" class="flex flex-col gap-[18px]">
        @csrf
        <x-field label="Nama lengkap" for="name"><x-icon-input id="name" icon="user" name="name" :value="old('name')" required autocomplete="name" :invalid="$errors->has('name')" /></x-field>
        <div class="grid gap-[18px] sm:grid-cols-2">
            <x-field label="Jenis kelamin" for="gender">
                <x-select-input id="gender" name="gender" required>
                    <option value="">Pilih</option>
                    @foreach (\App\Enums\Gender::cases() as $gender)<option value="{{ $gender->value }}" @selected(old('gender') === $gender->value)>{{ $gender->label() }}</option>@endforeach
                </x-select-input>
            </x-field>
            <x-field label="Tahun lulus" for="graduation_year"><x-text-input id="graduation_year" name="graduation_year" type="number" min="1990" :max="now()->year" :value="old('graduation_year')" required /></x-field>
        </div>
        <x-field label="Kampus atau tempat kerja sekarang" for="institution_name"><x-text-input id="institution_name" name="institution_name" :value="old('institution_name')" required /></x-field>
        <x-field label="Negara" for="country_id">
            <x-select-input id="country_id" name="country_id" required>
                <option value="">Pilih negara</option>
                @foreach ($countries as $country)<option value="{{ $country->id }}" @selected((string) old('country_id') === (string) $country->id)>{{ $country->name }}</option>@endforeach
            </x-select-input>
        </x-field>
        <x-field label="Email" for="email"><x-icon-input id="email" icon="mail" type="email" name="email" :value="old('email')" required autocomplete="email" :invalid="$errors->has('email')" /></x-field>
        <x-field label="Username" for="username" hint="Huruf, angka, - atau _. Dipakai untuk masuk."><x-icon-input id="username" icon="at-sign" name="username" :value="old('username')" required autocomplete="username" :invalid="$errors->has('username')" /></x-field>
        <x-field label="Nomor WhatsApp" for="whatsapp" optional hint="Hanya untuk verifikasi oleh admin."><x-text-input id="whatsapp" name="whatsapp" type="tel" :value="old('whatsapp')" autocomplete="tel" /></x-field>
        <x-field label="Password" for="password" hint="Minimal 8 karakter, berisi huruf dan angka."><x-icon-input id="password" icon="lock" type="password" name="password" required autocomplete="new-password" :invalid="$errors->has('password')" /></x-field>
        <x-field label="Ulangi password" for="password_confirmation" error="password_confirmation"><x-icon-input id="password_confirmation" icon="lock" type="password" name="password_confirmation" required autocomplete="new-password" /></x-field>
        <x-primary-button class="w-full py-4">Daftar <x-icon name="arrow-right" class="size-[18px]" /></x-primary-button>
        <p class="text-center text-[13px] text-slate">Dengan mendaftar, kamu menyetujui <a href="{{ route('privacy') }}" class="font-bold text-blue underline">kebijakan privasi</a> PEDULI.</p>
    </form>
</x-guest-layout>
