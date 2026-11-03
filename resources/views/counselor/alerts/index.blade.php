{{-- resources/views/counselor/alerts/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">Peringatan Krisis</x-slot>

    <x-page-intro title="Peringatan krisis" description="Muncul saat cerita santri atau pesan ke Asisten AI mengandung tanda bahaya. Segera temui santrinya, lalu tandai sedang ditangani." />

    <div class="flex flex-col gap-6">
        <x-card :title="'Perlu tindakan ('.$open->count().')'" :padded="false">
            <ul class="divide-y divide-hairline">
                @forelse ($open as $alert)
                    <li class="flex flex-col gap-3 bg-danger-wash/40 px-6 py-5 sm:flex-row sm:items-center">
                        <span class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-danger-wash text-danger"><x-icon name="siren" /></span>
                        <div class="flex flex-1 flex-col gap-0.5">
                            <p class="text-[15px] font-bold">{{ $alert->santri->name }}</p>
                            <p class="text-[13px] text-slate">{{ $alert->sourceLabel() }} · {{ $alert->created_at->diffForHumans() }}@if ($alert->santri->santriProfile) · {{ $alert->santri->santriProfile->level->label() }} Kelas {{ $alert->santri->santriProfile->class_name }}@endif</p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            @if ($alert->source === 'request' && in_array($alert->source_id, $pendingRequestIds))
                                <a href="{{ route('counselor.requests.show', $alert->source_id) }}" class="btn-secondary py-2.5 text-sm">Buka permintaan</a>
                            @endif
                            <form method="POST" action="{{ route('counselor.alerts.acknowledge', $alert) }}">@csrf<button type="submit" class="btn bg-danger py-2.5 text-sm text-white hover:bg-danger-ink">Saya tangani</button></form>
                        </div>
                    </li>
                @empty
                    <li class="px-6 py-8 text-center text-sm text-slate">Tidak ada peringatan terbuka.</li>
                @endforelse
            </ul>
        </x-card>

        <x-card title="Sudah ditangani" :padded="false">
            <ul class="divide-y divide-hairline">
                @forelse ($handled as $alert)
                    <li class="flex items-center gap-4 px-6 py-4"><span class="flex-1 text-sm font-semibold">{{ $alert->santri->name }}</span><span class="text-xs text-slate">oleh {{ $alert->acknowledger?->name ?? '—' }} · {{ $alert->acknowledged_at?->translatedFormat('j M H.i') }}</span></li>
                @empty
                    <li class="px-6 py-8 text-center text-sm text-slate">Belum ada.</li>
                @endforelse
            </ul>
        </x-card>
    </div>
</x-app-layout>
