{{-- resources/views/admin/programs/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">Program Luar Negeri</x-slot>

    <x-page-intro title="Program kerja & magang" description="Program baru tersimpan sebagai draf sampai dicentang “Tampilkan ke santri”.">
        <x-slot name="actions">
            <a href="{{ route('admin.partners.index') }}" class="btn-secondary">Mitra ({{ $partnerCount }})</a>
            <a href="{{ route('admin.programs.create') }}" class="btn-primary"><x-icon name="plus" class="size-[18px]" />Tambah program</a>
        </x-slot>
    </x-page-intro>

    <div class="mb-6 grid gap-4 sm:grid-cols-3">
        <x-stat-card label="Program buka" :value="$stats['open']" icon="briefcase-business" />
        <x-stat-card label="Draf" :value="$stats['draft']" icon="file-text" />
        <x-stat-card label="Peminat belum ditindaklanjuti" :value="$stats['interests']" icon="users" :tone="$stats['interests'] ? 'warning' : 'default'" />
    </div>

    <div class="overflow-x-auto rounded-3xl bg-white ring-1 ring-hairline">
        <table class="min-w-full divide-y divide-hairline text-sm">
            <thead class="bg-cloud text-left text-[11px] font-bold uppercase tracking-[0.12em] text-slate">
                <tr><th class="px-5 py-3">Program</th><th class="px-5 py-3">Negara</th><th class="px-5 py-3">Status</th><th class="px-5 py-3">Tenggat</th><th class="px-5 py-3">Peminat</th><th class="px-5 py-3 text-right">Aksi</th></tr>
            </thead>
            <tbody class="divide-y divide-hairline">
                @forelse ($programs as $program)
                    <tr class="hover:bg-cloud">
                        <td class="px-5 py-4">
                            <p class="font-bold">{{ $program->title }}</p>
                            <p class="text-xs text-slate">{{ $program->type->label() }} · {{ $program->partner->name }}</p>
                        </td>
                        <td class="whitespace-nowrap px-5 py-4">{{ $program->country->name }}</td>
                        <td class="px-5 py-4">
                            @if (! $program->isPublished())
                                <x-badge tone="gold">Draf</x-badge>
                            @else
                                <x-status-badge :status="$program->status" />
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-5 py-4 text-slate">{{ $program->deadline?->translatedFormat('j M Y') ?? '—' }}</td>
                        <td class="px-5 py-4"><a href="{{ route('admin.programs.interests', $program) }}" class="font-bold text-blue hover:underline">{{ $program->interests_count }} orang</a></td>
                        <td class="whitespace-nowrap px-5 py-4 text-right"><a href="{{ route('admin.programs.edit', $program) }}" class="font-bold text-blue hover:underline">Ubah<span class="sr-only"> {{ $program->title }}</span></a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-5 py-10 text-center text-slate">Belum ada program. Tambahkan mitra dulu, lalu programnya.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $programs->links('pagination.peduli') }}</div>
</x-app-layout>
