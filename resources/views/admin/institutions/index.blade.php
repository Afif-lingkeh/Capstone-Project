{{-- resources/views/admin/institutions/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">Institusi</x-slot>

    @if ($errors->has('institution'))
        <div class="mb-4 rounded-xl bg-danger-wash p-4 text-sm text-danger-ink ring-1 ring-danger-line">{{ $errors->first('institution') }}</div>
    @endif

    <div class="mb-4 flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
        <form method="GET" class="grid flex-1 gap-3 sm:grid-cols-4">
            <x-text-input name="q" :value="request('q')" placeholder="Cari nama institusi" class="sm:col-span-2" />
            <x-select-input name="type">
                <option value="">Semua kategori</option>
                @foreach ($types as $type)
                    <option value="{{ $type->value }}" @selected(request('type') === $type->value)>{{ $type->label() }}</option>
                @endforeach
            </x-select-input>
            <x-secondary-button type="submit" class="justify-center">Filter</x-secondary-button>
        </form>

        <a href="{{ route('admin.institutions.create') }}" class="btn-primary">
            + Tambah Institusi
        </a>
    </div>

    <div class="overflow-x-auto rounded-3xl bg-white ring-1 ring-hairline">
        <table class="min-w-full divide-y divide-hairline text-sm">
            <thead class="bg-cloud text-left text-[11px] font-bold uppercase tracking-[0.12em] text-slate">
                <tr>
                    <th class="px-4 py-3">Nama</th>
                    <th class="px-4 py-3">Kategori</th>
                    <th class="px-4 py-3">Lokasi</th>
                    <th class="px-4 py-3">Tautan</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-hairline">
                @forelse ($institutions as $institution)
                    <tr>
                        <td class="px-4 py-3 font-medium text-ink">{{ $institution->name }}</td>
                        <td class="px-4 py-3">{{ $institution->type->label() }}</td>
                        <td class="px-4 py-3 text-slate">{{ collect([$institution->city, $institution->country->name])->filter()->join(', ') }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-slate">{{ $institution->verified_links_count }} terverifikasi / {{ $institution->links_count }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-right">
                            <a href="{{ route('admin.links.create', ['institution' => $institution->id]) }}" class="font-medium text-blue hover:underline">+ Tautan</a>
                            <a href="{{ route('admin.institutions.edit', $institution) }}" class="ml-3 font-medium text-blue hover:underline">Ubah</a>
                            @if ($institution->links_count === 0)
                                <form method="POST" action="{{ route('admin.institutions.destroy', $institution) }}" class="ml-3 inline" onsubmit="return confirm('Hapus institusi ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="font-medium text-danger-ink hover:underline">Hapus</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-slate">Belum ada institusi.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $institutions->links('pagination.peduli') }}</div>
</x-app-layout>
