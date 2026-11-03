{{-- resources/views/admin/links/partials/form.blade.php --}}
<div class="space-y-6 rounded-3xl bg-white p-6 ring-1 ring-inset ring-hairline">
    <div>
        <x-input-label for="institution_id" value="Institusi" />
        <x-select-input id="institution_id" name="institution_id" class="mt-1 block w-full" required>
            <option value="">Pilih institusi</option>
            @foreach ($institutions as $institution)
                <option value="{{ $institution->id }}" @selected((string) old('institution_id', $link->institution_id) === (string) $institution->id)>
                    {{ $institution->name }} ({{ $institution->country->name }})
                </option>
            @endforeach
        </x-select-input>
        <p class="mt-1 text-xs text-slate">Institusi belum ada? <a href="{{ route('admin.institutions.create') }}" class="text-blue underline">Tambahkan dulu</a>.</p>
        <x-input-error :messages="$errors->get('institution_id')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="label" value="Nama tautan" />
        <x-text-input id="label" name="label" list="label-suggestions" class="mt-1 block w-full" :value="old('label', $link->label)" required />
        <datalist id="label-suggestions">
            @foreach ($labelSuggestions as $suggestion)
                <option value="{{ $suggestion }}"></option>
            @endforeach
        </datalist>
        <x-input-error :messages="$errors->get('label')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="url" value="URL lengkap" />
        <x-text-input id="url" name="url" type="url" class="mt-1 block w-full font-mono" :value="old('url', $link->url)" placeholder="https://www.contoh.ac.id" required />
        <x-input-error :messages="$errors->get('url')" class="mt-2" />
    </div>

    <p class="rounded-xl bg-warning-wash p-3 text-xs text-warning ring-1 ring-warning/20">
        Tautan baru berstatus <strong>Menunggu Verifikasi</strong> dan belum tampil ke santri.
        Mengubah URL tautan yang sudah terverifikasi akan <strong>membatalkan verifikasinya</strong>.
    </p>

    <div class="flex items-center gap-3">
        <x-primary-button>Simpan</x-primary-button>
        <a href="{{ route('admin.links.index') }}" class="text-sm text-slate hover:underline">Batal</a>
    </div>
</div>
