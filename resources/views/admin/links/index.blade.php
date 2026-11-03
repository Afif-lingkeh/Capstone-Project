{{-- resources/views/admin/links/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">Tautan Resmi</x-slot>

    <div class="mb-4 flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
        <form method="GET" class="flex flex-1 flex-col gap-3 sm:flex-row">
            @if ($currentStatus)
                <input type="hidden" name="status" value="{{ $currentStatus->value }}">
            @endif
            <x-text-input name="q" :value="request('q')" placeholder="Cari nama tautan, URL, atau institusi" class="w-full sm:flex-1" />
            <x-secondary-button type="submit" class="justify-center">Cari</x-secondary-button>
        </form>

        <a href="{{ route('admin.links.create') }}" class="btn-primary">
            + Tambah Tautan
        </a>
    </div>

    <nav class="-mx-1 mb-4 flex gap-2 overflow-x-auto px-1 pb-1 text-sm" aria-label="Status">
        <a href="{{ route('admin.links.index', request()->only('q')) }}" @class([
            'whitespace-nowrap rounded-full px-4 py-2.5 font-semibold ring-[1.5px] ring-inset',
            'border-ink bg-ink text-white ring-ink' => ! $currentStatus,
            'bg-white text-ink ring-hairline hover:bg-cloud' => $currentStatus,
        ])>Semua ({{ $statusCounts->sum() }})</a>
        @foreach ($statuses as $status)
            <a href="{{ route('admin.links.index', [...request()->only('q'), 'status' => $status->value]) }}" @class([
                'whitespace-nowrap rounded-full px-4 py-2.5 font-semibold ring-[1.5px] ring-inset',
                'border-ink bg-ink text-white ring-ink' => $currentStatus === $status,
                'bg-white text-ink ring-hairline hover:bg-cloud' => $currentStatus !== $status,
            ])>{{ $status->label() }} ({{ $statusCounts[$status->value] ?? 0 }})</a>
        @endforeach
    </nav>

    <div class="overflow-x-auto rounded-3xl bg-white ring-1 ring-hairline">
        <table class="min-w-full divide-y divide-hairline text-sm">
            <thead class="bg-cloud text-left text-[11px] font-bold uppercase tracking-[0.12em] text-slate">
                <tr>
                    <th class="px-4 py-3">Tautan</th>
                    <th class="px-4 py-3">Institusi</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Cek terakhir</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-hairline">
                @forelse ($links as $link)
                    <tr>
                        <td class="px-4 py-3">
                            <p class="font-medium text-ink">{{ $link->label }}</p>
                            <p class="break-all font-mono text-xs text-slate">{{ $link->url }}</p>
                        </td>
                        <td class="px-4 py-3">{{ $link->institution->name }}</td>
                        <td class="px-4 py-3"><x-status-badge :status="$link->status" /></td>
                        <td class="whitespace-nowrap px-4 py-3 text-slate">
                            @if ($link->last_check_result)
                                <x-status-badge :status="$link->last_check_result" />
                                <span class="block text-xs">{{ $link->last_checked_at->diffForHumans() }}</span>
                            @else
                                —
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-right">
                            <a href="{{ route('admin.links.edit', $link) }}" class="font-medium text-blue hover:underline">
                                {{ in_array($link->status, [App\Enums\LinkStatus::Pending, App\Enums\LinkStatus::NeedsReview], true) ? 'Tinjau' : 'Detail' }}
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-slate">Tidak ada tautan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $links->links('pagination.peduli') }}</div>
</x-app-layout>
