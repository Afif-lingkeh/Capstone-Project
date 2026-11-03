{{-- resources/views/admin/partners/form.blade.php --}}
@php($editing = $partner->exists)
<x-app-layout>
    <x-slot name="header">{{ $editing ? 'Ubah Mitra' : 'Tambah Mitra' }}</x-slot>

    <form method="POST" action="{{ $editing ? route('admin.partners.update', $partner) : route('admin.partners.store') }}" class="flex max-w-3xl flex-col gap-6">
        @csrf
        @if ($editing) @method('PUT') @endif

        <x-card title="Data mitra">
            <div class="grid gap-5 sm:grid-cols-2">
                <x-field label="Nama lembaga" for="name" class="sm:col-span-2">
                    <x-text-input id="name" name="name" :value="old('name', $partner->name)" required />
                </x-field>
                <x-field label="Negara" for="country_id">
                    <x-select-input id="country_id" name="country_id" required>
                        <option value="">Pilih negara</option>
                        @foreach ($countries as $country)
                            <option value="{{ $country->id }}" @selected((string) old('country_id', $partner->country_id) === (string) $country->id)>{{ $country->name }}</option>
                        @endforeach
                    </x-select-input>
                </x-field>
                <x-field label="Situs resmi" for="website" optional>
                    <x-text-input id="website" name="website" type="url" :value="old('website', $partner->website)" placeholder="https://" />
                </x-field>
                <x-field label="Email resmi" for="contact_email" optional>
                    <x-text-input id="contact_email" name="contact_email" type="email" :value="old('contact_email', $partner->contact_email)" />
                </x-field>
                <x-field label="Telepon kantor" for="contact_phone" optional hint="Hanya untuk admin. Tidak ditampilkan ke santri.">
                    <x-text-input id="contact_phone" name="contact_phone" :value="old('contact_phone', $partner->contact_phone)" />
                </x-field>
                <x-field label="Deskripsi" for="description" class="sm:col-span-2" optional>
                    <x-textarea id="description" name="description" rows="4">{{ old('description', $partner->description) }}</x-textarea>
                </x-field>
            </div>
        </x-card>

        <div class="flex flex-wrap items-center gap-3">
            <x-primary-button>Simpan mitra</x-primary-button>
            <a href="{{ route('admin.partners.index') }}" class="btn-ghost">Batal</a>
        </div>
    </form>

    @if ($editing)
        @can('delete', $partner)
            <form method="POST" action="{{ route('admin.partners.destroy', $partner) }}" class="mt-10 max-w-3xl border-t border-hairline pt-6" onsubmit="return confirm('Hapus mitra ini?')">
                @csrf @method('DELETE')
                <x-danger-button>Hapus mitra</x-danger-button>
            </form>
        @endcan
    @endif
</x-app-layout>
