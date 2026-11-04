{{-- resources/views/alumni/profile.blade.php --}}
<x-app-layout>
    <x-slot name="header">Profil Alumni</x-slot>

    <form method="POST" action="{{ route('alumni.profile.update') }}" class="flex max-w-4xl flex-col gap-6">
        @csrf @method('PATCH')

        <x-card title="Data alumni">
            <div class="grid gap-5 sm:grid-cols-2">
                <x-field label="Tahun lulus" for="graduation_year"><x-text-input id="graduation_year" name="graduation_year" type="number" min="1990" :max="now()->year" :value="old('graduation_year', $profile->graduation_year)" required /></x-field>
                <x-field label="Kegiatan sekarang" for="activity">
                    <x-select-input id="activity" name="activity">
                        @foreach (['studying' => 'Kuliah', 'working' => 'Bekerja', 'other' => 'Lainnya'] as $value => $label)<option value="{{ $value }}" @selected(old('activity', $profile->activity) === $value)>{{ $label }}</option>@endforeach
                    </x-select-input>
                </x-field>
                <x-field label="Kampus atau tempat kerja" for="institution_name" class="sm:col-span-2"><x-text-input id="institution_name" name="institution_name" :value="old('institution_name', $profile->institution_name)" required /></x-field>
                <x-field label="Bidang/jurusan" for="field" optional><x-text-input id="field" name="field" :value="old('field', $profile->field)" placeholder="mis. S1 Ilmu Komputer" /></x-field>
                <x-field label="Jalur" for="pathway" optional>
                    <x-select-input id="pathway" name="pathway">
                        <option value="">—</option>
                        @foreach ($pathways as $p)<option value="{{ $p->value }}" @selected(old('pathway', $profile->pathway?->value) === $p->value)>{{ $p->label() }}</option>@endforeach
                    </x-select-input>
                </x-field>
                <x-field label="Kota" for="city" optional><x-text-input id="city" name="city" :value="old('city', $profile->city)" /></x-field>
                <x-field label="Negara" for="country_id">
                    <x-select-input id="country_id" name="country_id" required>
                        @foreach ($countries as $c)<option value="{{ $c->id }}" @selected((string) old('country_id', $profile->country_id) === (string) $c->id)>{{ $c->name }}</option>@endforeach
                    </x-select-input>
                </x-field>
                <x-field label="Cerita singkat untuk adik-adik" for="bio" class="sm:col-span-2" optional hint="Maks. 600 karakter, mis. bidang yang siap kamu bagikan.">
                    <x-textarea id="bio" name="bio" rows="3" maxlength="600">{{ old('bio', $profile->bio) }}</x-textarea>
                </x-field>
            </div>
        </x-card>

        <x-card title="Kontak yang ditampilkan">
            <p class="mb-5 text-sm text-slate">Anda sendiri yang menentukan kontak mana yang dapat dilihat santri.</p>
            <div class="grid gap-5 sm:grid-cols-2">
                <div class="flex flex-col gap-3">
                    <x-field label="Profil LinkedIn" for="linkedin_url" optional><x-text-input id="linkedin_url" name="linkedin_url" type="url" :value="old('linkedin_url', $profile->linkedin_url)" placeholder="https://www.linkedin.com/in/…" /></x-field>
                    <label class="flex items-center gap-3 text-sm font-semibold"><input type="hidden" name="show_linkedin" value="0"><input type="checkbox" name="show_linkedin" value="1" @checked(old('show_linkedin', $profile->show_linkedin)) class="size-5 rounded-md border-mist text-blue focus:ring-focus">Tampilkan ke santri</label>
                </div>
                <div class="flex flex-col gap-3">
                    <x-field label="Email publik" for="public_email" optional><x-text-input id="public_email" name="public_email" type="email" :value="old('public_email', $profile->public_email)" /></x-field>
                    <label class="flex items-center gap-3 text-sm font-semibold"><input type="hidden" name="show_email" value="0"><input type="checkbox" name="show_email" value="1" @checked(old('show_email', $profile->show_email)) class="size-5 rounded-md border-mist text-blue focus:ring-focus">Tampilkan ke santri</label>
                </div>
                <x-field label="Nomor WhatsApp" for="whatsapp" optional hint="Hanya untuk admin pesantren. Tidak pernah ditampilkan ke santri." class="sm:col-span-2">
                    <x-text-input id="whatsapp" name="whatsapp" type="tel" :value="old('whatsapp', $profile->whatsapp)" />
                </x-field>
                <label class="flex items-center gap-3 text-sm font-semibold sm:col-span-2"><input type="hidden" name="accepts_consultations" value="0"><input type="checkbox" name="accepts_consultations" value="1" @checked(old('accepts_consultations', $profile->accepts_consultations)) class="size-5 rounded-md border-mist text-blue focus:ring-focus">Saya bersedia menerima permintaan konsultasi</label>
            </div>
        </x-card>

        <x-primary-button class="w-fit">Simpan profil</x-primary-button>
    </form>
</x-app-layout>
