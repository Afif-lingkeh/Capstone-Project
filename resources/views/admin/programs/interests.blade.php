{{-- resources/views/admin/programs/interests.blade.php --}}
<x-app-layout>
    <x-slot name="header">Rekap Peminat</x-slot>

    <x-page-intro :eyebrow="$program->country->name.' · '.$program->partner->name" :title="$program->title" :description="$interests->count().' santri menyatakan berminat.'">
        <x-slot name="actions">
            <a href="{{ route('admin.programs.interests', [$program, 'format' => 'csv']) }}" class="btn-secondary"><x-icon name="download" class="size-[18px]" />Unduh CSV</a>
            <a href="{{ route('admin.programs.edit', $program) }}" class="btn-ghost">Ubah program</a>
        </x-slot>
    </x-page-intro>

    <div class="overflow-x-auto rounded-3xl bg-white ring-1 ring-hairline">
        <table class="min-w-full divide-y divide-hairline text-sm">
            <thead class="bg-cloud text-left text-[11px] font-bold uppercase tracking-[0.12em] text-slate">
                <tr><th class="px-5 py-3">Santri</th><th class="px-5 py-3">Kelas</th><th class="px-5 py-3">Pesan</th><th class="px-5 py-3">Tanggal</th><th class="px-5 py-3">Tindak lanjut</th></tr>
            </thead>
            <tbody class="divide-y divide-hairline">
                @forelse ($interests as $interest)
                    @php($profile = $interest->user->santriProfile)
                    <tr>
                        <td class="px-5 py-4"><p class="font-bold">{{ $interest->user->name }}</p><p class="text-xs text-slate">NIS {{ $profile?->nis ?? '—' }}</p></td>
                        <td class="whitespace-nowrap px-5 py-4">{{ $profile ? $profile->level->label().' · '.$profile->class_name : '—' }}</td>
                        <td class="max-w-sm px-5 py-4 text-slate">{{ $interest->note ?: '—' }}</td>
                        <td class="whitespace-nowrap px-5 py-4 text-slate">{{ $interest->created_at->translatedFormat('j M Y') }}</td>
                        <td class="px-5 py-4">
                            <form method="POST" action="{{ route('admin.programs.follow-up', [$program, $interest->id]) }}">
                                @csrf @method('PATCH')
                                <button type="submit" class="flex items-center gap-2 rounded-xl px-2 py-1.5 hover:bg-pebble">
                                    <x-badge :tone="$interest->followed_up_at ? 'success' : 'gold'">{{ $interest->followed_up_at ? 'Sudah' : 'Belum' }}</x-badge>
                                    <span class="text-xs font-bold text-blue">{{ $interest->followed_up_at ? 'Batalkan' : 'Tandai sudah' }}</span>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-5 py-10 text-center text-slate">Belum ada peminat.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-app-layout>
