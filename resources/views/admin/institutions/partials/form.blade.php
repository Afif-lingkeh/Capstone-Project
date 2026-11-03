{{-- resources/views/admin/institutions/partials/form.blade.php --}}
<div class="space-y-6 rounded-3xl bg-white p-6 ring-1 ring-inset ring-hairline">
    <div>
        <x-input-label for="name" value="Nama institusi" />
        <x-text-input id="name" name="name" class="mt-1 block w-full" :value="old('name', $institution->name)" placeholder="mis. Universitas Indonesia" required />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <x-input-label for="type" value="Kategori" />
            <x-select-input id="type" name="type" class="mt-1 block w-full" required>
                <option value="">Pilih</option>
                @foreach ($types as $type)
                    <option value="{{ $type->value }}" @selected(old('type', $institution->type?->value) === $type->value)>{{ $type->label() }}</option>
                @endforeach
            </x-select-input>
            <x-input-error :messages="$errors->get('type')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="country_id" value="Negara" />
            <x-select-input id="country_id" name="country_id" class="mt-1 block w-full" required>
                <option value="">Pilih</option>
                @foreach ($countries as $country)
                    <option value="{{ $country->id }}" @selected((string) old('country_id', $institution->country_id) === (string) $country->id)>{{ $country->name }}</option>
                @endforeach
            </x-select-input>
            <x-input-error :messages="$errors->get('country_id')" class="mt-2" />
        </div>
    </div>

    <div>
        <x-input-label for="city" value="Kota (opsional)" />
        <x-text-input id="city" name="city" class="mt-1 block w-full" :value="old('city', $institution->city)" />
        <x-input-error :messages="$errors->get('city')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="description" value="Deskripsi singkat (opsional)" />
        <textarea id="description" name="description" rows="3" class="mt-1 block w-full rounded-xl border-hairline shadow-sm focus:border-blue focus:ring-success/20">{{ old('description', $institution->description) }}</textarea>
        <x-input-error :messages="$errors->get('description')" class="mt-2" />
    </div>

    <div class="flex items-center gap-3">
        <x-primary-button>Simpan</x-primary-button>
        <a href="{{ route('admin.institutions.index') }}" class="text-sm text-slate hover:underline">Batal</a>
    </div>
</div>
