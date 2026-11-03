{{-- resources/views/admin/majors/index.blade.php --}}
@php($form = $editing ?? new \App\Models\Major)
<x-app-layout>
    <x-slot name="header">Daftar Jurusan</x-slot>

    <x-page-intro title="Jurusan per jalur" description="Dipakai untuk rekomendasi jurusan pada hasil tes potensi santri.">
        <x-slot name="actions"><a href="{{ route('admin.articles.index') }}" class="btn-ghost"><x-icon name="arrow-left" class="size-[18px]" />Artikel</a></x-slot>
    </x-page-intro>

    <div class="flex flex-col gap-6 xl:flex-row xl:items-start">
        <form method="POST" action="{{ $editing ? route('admin.majors.update', $editing) : route('admin.majors.store') }}" class="flex flex-col gap-5 rounded-3xl bg-white p-6 ring-1 ring-inset ring-hairline xl:sticky xl:top-24 xl:w-[380px]">
            @csrf
            @if ($editing) @method('PUT') @endif
            <h2 class="text-lg font-extrabold">{{ $editing ? 'Ubah jurusan' : 'Tambah jurusan' }}</h2>
            <x-field label="Nama jurusan" for="name"><x-text-input id="name" name="name" :value="old('name', $form->name)" required /></x-field>
            <x-field label="Jalur" for="pathway">
                <x-select-input id="pathway" name="pathway">
                    @foreach ($pathways as $pathway)
                        <option value="{{ $pathway->value }}" @selected(old('pathway', $form->pathway?->value) === $pathway->value)>{{ $pathway->label() }}</option>
                    @endforeach
                </x-select-input>
            </x-field>
            <x-field label="Deskripsi singkat" for="description" optional><x-textarea id="description" name="description" rows="3">{{ old('description', $form->description) }}</x-textarea></x-field>
            <x-field label="Prospek karier" for="career_prospects" optional><x-text-input id="career_prospects" name="career_prospects" :value="old('career_prospects', $form->career_prospects)" /></x-field>
            <div class="flex gap-3">
                <x-primary-button>{{ $editing ? 'Simpan' : 'Tambah' }}</x-primary-button>
                @if ($editing)<a href="{{ route('admin.majors.index') }}" class="btn-ghost">Batal</a>@endif
            </div>
        </form>

        <div class="grid flex-1 gap-6 md:grid-cols-2">
            @foreach ($pathways as $pathway)
                <x-card :title="$pathway->label()" :padded="false">
                    <ul class="divide-y divide-hairline">
                        @forelse ($majors[$pathway->value] ?? [] as $major)
                            <li class="flex items-center gap-3 px-6 py-3">
                                <span class="flex-1 text-sm font-semibold">{{ $major->name }}</span>
                                <a href="{{ route('admin.majors.index', ['ubah' => $major->id]) }}" class="text-sm font-bold text-blue hover:underline">Ubah<span class="sr-only"> {{ $major->name }}</span></a>
                                <form method="POST" action="{{ route('admin.majors.destroy', $major) }}" onsubmit="return confirm('Hapus jurusan ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="flex size-9 items-center justify-center rounded-lg text-danger-ink hover:bg-danger-wash"><x-icon name="trash-2" class="size-4" /><span class="sr-only">Hapus {{ $major->name }}</span></button>
                                </form>
                            </li>
                        @empty
                            <li class="px-6 py-6 text-sm text-slate">Belum ada jurusan.</li>
                        @endforelse
                    </ul>
                </x-card>
            @endforeach
        </div>
    </div>
</x-app-layout>
