{{-- resources/views/alumni/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">Direktori Alumni</x-slot>

    <x-page-intro eyebrow="Jejaring alumni" title="Konsultasi dengan alumni"
                  description="Alumni pesantren yang sedang kuliah atau bekerja di dalam dan luar negeri. Semua sudah diverifikasi pesantren." />

    <form method="GET" class="mb-6 grid gap-3 rounded-3xl bg-white p-4 ring-1 ring-inset ring-hairline sm:grid-cols-2 lg:grid-cols-4">
        <div class="sm:col-span-2">
            <label for="q" class="sr-only">Cari kampus, bidang, atau kota</label>
            <x-text-input id="q" name="q" type="search" :value="request('q')" placeholder="Cari kampus, bidang, atau kota" />
        </div>
        <x-select-input name="negara" aria-label="Negara">
            <option value="">Semua negara</option>
            @foreach ($countries as $country)<option value="{{ $country->id }}" @selected(request()->integer('negara') === $country->id)>{{ $country->name }}</option>@endforeach
        </x-select-input>
        <div class="flex gap-3">
            <x-select-input name="jalur" aria-label="Jalur" class="flex-1">
                <option value="">Semua jalur</option>
                @foreach ($pathways as $pathway)<option value="{{ $pathway->value }}" @selected(request('jalur') === $pathway->value)>{{ $pathway->label() }}</option>@endforeach
            </x-select-input>
            <button type="submit" class="btn-primary px-5 shadow-none">Cari</button>
        </div>
    </form>

    @if ($profiles->isEmpty())
        <x-empty-state icon="users" title="Belum ada alumni yang cocok">Coba ubah filter pencarian.</x-empty-state>
    @else
        <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($profiles as $profile)
                <x-alumni-card :profile="$profile">
                    @if (auth()->user()->hasRole('santri') && $profile->accepts_consultations)
                        <span class="btn-secondary pointer-events-none relative w-full py-3" aria-hidden="true">Ajukan konsultasi</span>
                    @endif
                </x-alumni-card>
            @endforeach
        </div>
        <div class="mt-6">{{ $profiles->links('pagination.peduli') }}</div>
    @endif
</x-app-layout>
