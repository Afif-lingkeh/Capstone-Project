{{-- resources/views/counselor/handovers/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">Alih Tangan</x-slot>

    <x-page-intro title="Alih tangan kasus" description="Setelah Anda menerima, kasus berpindah ke Anda dan konselor sebelumnya tidak dapat membacanya lagi." />

    <div class="flex flex-col gap-6">
        <x-card title="Masuk" :padded="false">
            <ul class="divide-y divide-hairline">
                @forelse ($incoming as $handover)
                    <li class="flex flex-col gap-4 px-6 py-5">
                        <div class="flex flex-col gap-1">
                            <p class="text-[15px] font-bold">{{ $handover->case->code }} · {{ $handover->case->santri->name }}</p>
                            <p class="text-[13px] text-slate">Dari {{ $handover->from?->name ?? 'konselor' }} · {{ $handover->created_at->diffForHumans() }}</p>
                        </div>
                        <p class="whitespace-pre-line rounded-2xl bg-cloud p-4 text-sm leading-[1.6]">{{ $handover->reason }}</p>
                        <div class="flex flex-wrap gap-2">
                            <form method="POST" action="{{ route('counselor.handovers.accept', $handover) }}">@csrf<x-primary-button class="py-3">Terima kasus</x-primary-button></form>
                            <form method="POST" action="{{ route('counselor.handovers.reject', $handover) }}">@csrf<button type="submit" class="btn-ghost py-3 text-danger-ink">Tolak</button></form>
                        </div>
                    </li>
                @empty
                    <li class="px-6 py-8 text-center text-sm text-slate">Tidak ada permintaan alih tangan.</li>
                @endforelse
            </ul>
        </x-card>

        <x-card title="Yang saya ajukan" :padded="false">
            <ul class="divide-y divide-hairline">
                @forelse ($outgoing as $handover)
                    <li class="flex items-center gap-4 px-6 py-4"><span class="w-28 font-mono text-sm font-bold">{{ $handover->case->code }}</span><span class="flex-1 text-sm">ke {{ $handover->to->name }}</span><x-status-badge :status="$handover->status" /></li>
                @empty
                    <li class="px-6 py-8 text-center text-sm text-slate">Belum ada.</li>
                @endforelse
            </ul>
        </x-card>
    </div>
</x-app-layout>
