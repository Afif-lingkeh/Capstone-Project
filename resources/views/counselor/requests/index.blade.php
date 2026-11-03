{{-- resources/views/counselor/requests/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">Antrean Permintaan</x-slot>

    <x-page-intro title="Antrean permintaan konseling" description="Tanda krisis selalu paling atas, lalu santri dengan gender yang sama (prioritas, tidak dikunci). Membuka cerita santri tercatat di log audit." />

    @if ($requests->isEmpty())
        <x-empty-state icon="inbox" title="Antrean kosong">Alhamdulillah, belum ada permintaan baru.</x-empty-state>
    @else
        <x-card :padded="false">
            <ul class="divide-y divide-hairline">
                @foreach ($requests as $item)
                    <li @class(['flex flex-col gap-3 px-6 py-5 sm:flex-row sm:items-center', 'bg-danger-wash/50' => $item->is_crisis])>
                        <span @class(['flex size-11 shrink-0 items-center justify-center rounded-xl', 'bg-danger-wash text-danger' => $item->is_crisis, 'bg-blue-wash text-blue' => ! $item->is_crisis])>
                            <x-icon :name="$item->is_crisis ? 'siren' : 'heart-handshake'" />
                        </span>
                        <div class="flex flex-1 flex-col gap-1">
                            <p class="text-base font-bold">{{ $item->santri->name }} · {{ $item->topic->label() }}</p>
                            <p class="text-[13px] font-medium text-slate">
                                {{ $item->santri->gender?->label() }}
                                @if ($item->santri->santriProfile) · {{ $item->santri->santriProfile->level->label() }} Kelas {{ $item->santri->santriProfile->class_name }}@endif
                                · diajukan {{ $item->created_at->diffForHumans() }}
                                @if ($item->preferred_time) · bisa: {{ $item->preferred_time }}@endif
                            </p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            @if ($item->is_crisis)<x-badge tone="danger">Tanda krisis</x-badge>@endif
                            <x-badge :tone="$item->urgency->tone()">{{ $item->urgency->label() }}</x-badge>
                            <a href="{{ route('counselor.requests.show', $item) }}" class="btn-primary px-5 py-2.5 text-sm">Baca & ambil</a>
                        </div>
                    </li>
                @endforeach
            </ul>
        </x-card>
    @endif
</x-app-layout>
