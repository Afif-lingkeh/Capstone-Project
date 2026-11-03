{{-- resources/views/admin/programs/form.blade.php --}}
@php($editing = $program->exists)
<x-app-layout>
    <x-slot name="header">{{ $editing ? 'Ubah Program' : 'Tambah Program' }}</x-slot>

    <form method="POST" action="{{ $editing ? route('admin.programs.update', $program) : route('admin.programs.store') }}" class="flex max-w-4xl flex-col gap-6">
        @csrf
        @if ($editing) @method('PUT') @endif

        <x-card title="Informasi program">
            <div class="grid gap-5 sm:grid-cols-2">
                <x-field label="Judul program" for="title" class="sm:col-span-2">
                    <x-text-input id="title" name="title" :value="old('title', $program->title)" required />
                </x-field>
                <x-field label="Mitra" for="partner_id">
                    <x-select-input id="partner_id" name="partner_id" required>
                        <option value="">Pilih mitra</option>
                        @foreach ($partners as $partner)
                            <option value="{{ $partner->id }}" @selected((string) old('partner_id', $program->partner_id) === (string) $partner->id)>{{ $partner->name }} ({{ $partner->country->name }})</option>
                        @endforeach
                    </x-select-input>
                </x-field>
                <x-field label="Negara tujuan" for="country_id">
                    <x-select-input id="country_id" name="country_id" required>
                        <option value="">Pilih negara</option>
                        @foreach ($countries as $country)
                            <option value="{{ $country->id }}" @selected((string) old('country_id', $program->country_id) === (string) $country->id)>{{ $country->name }}</option>
                        @endforeach
                    </x-select-input>
                </x-field>
                <x-field label="Jenis" for="type">
                    <x-select-input id="type" name="type" required>
                        @foreach ($types as $type)
                            <option value="{{ $type->value }}" @selected(old('type', $program->type?->value) === $type->value)>{{ $type->label() }}</option>
                        @endforeach
                    </x-select-input>
                </x-field>
                <x-field label="Status pendaftaran" for="status">
                    <x-select-input id="status" name="status" required>
                        @foreach ($statuses as $status)
                            <option value="{{ $status->value }}" @selected(old('status', $program->status?->value) === $status->value)>{{ $status->label() }}</option>
                        @endforeach
                    </x-select-input>
                </x-field>
                <x-field label="Ringkasan" for="summary" class="sm:col-span-2" hint="Maks. 500 karakter, tampil di halaman detail.">
                    <x-textarea id="summary" name="summary" rows="3" maxlength="500" required>{{ old('summary', $program->summary) }}</x-textarea>
                </x-field>
            </div>
        </x-card>

        <x-card title="Syarat, biaya, dan tenggat">
            <div class="grid gap-5 sm:grid-cols-2">
                <x-field label="Persyaratan" for="requirements" class="sm:col-span-2" hint="Satu syarat per baris.">
                    <x-textarea id="requirements" name="requirements" rows="6" required>{{ old('requirements', $program->requirements) }}</x-textarea>
                </x-field>
                <x-field label="Biaya" for="costs" class="sm:col-span-2" hint="Baris pertama tampil di kartu program. Tulis sesuai rincian resmi mitra.">
                    <x-textarea id="costs" name="costs" rows="4" required>{{ old('costs', $program->costs) }}</x-textarea>
                </x-field>
                <x-field label="Pendidikan minimal" for="min_education" optional>
                    <x-text-input id="min_education" name="min_education" :value="old('min_education', $program->min_education)" placeholder="Lulus SMA/sederajat" />
                </x-field>
                <x-field label="Tenggat pendaftaran" for="deadline" optional>
                    <x-text-input id="deadline" name="deadline" type="date" :value="old('deadline', $program->deadline?->format('Y-m-d'))" />
                </x-field>
                <x-field label="Kontak resmi" for="official_contact" hint="Lembaga atau petugas pesantren, bukan nomor pribadi.">
                    <x-text-input id="official_contact" name="official_contact" :value="old('official_contact', $program->official_contact)" required />
                </x-field>
                <x-field label="Tautan pengumuman resmi" for="official_url" optional>
                    <x-text-input id="official_url" name="official_url" type="url" :value="old('official_url', $program->official_url)" placeholder="https://" />
                </x-field>
            </div>
        </x-card>

        <label class="flex items-start gap-3 rounded-3xl bg-white p-6 ring-1 ring-inset ring-hairline">
            <input type="hidden" name="publish" value="0">
            <input type="checkbox" name="publish" value="1" @checked(old('publish', $program->published_at !== null)) class="mt-0.5 size-[22px] rounded-[7px] border-[1.5px] border-mist text-blue focus:ring-focus">
            <span class="flex flex-col gap-0.5">
                <span class="text-[15px] font-bold">Tampilkan ke santri</span>
                <span class="text-sm text-slate">Centang setelah syarat dan biaya dicocokkan dengan dokumen resmi mitra.</span>
            </span>
        </label>

        <div class="flex flex-wrap items-center gap-3">
            <x-primary-button>Simpan program</x-primary-button>
            <a href="{{ route('admin.programs.index') }}" class="btn-ghost">Batal</a>
        </div>
    </form>

    @if ($editing)
        @can('delete', $program)
            <form method="POST" action="{{ route('admin.programs.destroy', $program) }}" class="mt-10 max-w-4xl border-t border-hairline pt-6" onsubmit="return confirm('Hapus program ini? Tindakan ini tidak dapat dibatalkan.')">
                @csrf @method('DELETE')
                <x-danger-button>Hapus program</x-danger-button>
            </form>
        @endcan
    @endif
</x-app-layout>
