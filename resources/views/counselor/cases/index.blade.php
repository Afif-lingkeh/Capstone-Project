{{-- resources/views/counselor/cases/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">Kasus Saya</x-slot>

    <x-page-intro title="Kasus yang saya dampingi" description="Hanya kasus yang Anda pegang saat ini. Kasus yang sudah dialihtangankan tidak tampil lagi di sini." />

    <div class="flex flex-col gap-6">
        <x-card :title="'Aktif ('.$open->count().')'" :padded="false">
            <ul class="divide-y divide-hairline">
                @forelse ($open as $case)
                    <li>
                        <a href="{{ route('counselor.cases.show', $case) }}" class="flex flex-col gap-2 px-6 py-4 hover:bg-cloud sm:flex-row sm:items-center sm:gap-4">
                            <span class="w-28 font-mono text-sm font-bold text-blue">{{ $case->code }}</span>
                            <span class="flex flex-1 flex-col"><span class="text-[15px] font-bold">{{ $case->santri->name }}</span><span class="text-xs text-slate">{{ $case->sessions_count }} sesi · diperbarui {{ $case->updated_at->diffForHumans() }}</span></span>
                            @if ($case->pendingHandover)<x-badge tone="gold">Menunggu alih tangan</x-badge>@endif
                            <x-badge :tone="$case->urgency->tone()">{{ $case->urgency->label() }}</x-badge>
                        </a>
                    </li>
                @empty
                    <li class="px-6 py-8 text-center text-sm text-slate">Tidak ada kasus aktif. Ambil permintaan dari antrean.</li>
                @endforelse
            </ul>
        </x-card>

        @if ($closed->isNotEmpty())
            <x-card title="Selesai" :padded="false">
                <ul class="divide-y divide-hairline">
                    @foreach ($closed as $case)
                        <li><a href="{{ route('counselor.cases.show', $case) }}" class="flex items-center gap-4 px-6 py-4 hover:bg-cloud"><span class="w-28 font-mono text-sm font-bold text-slate">{{ $case->code }}</span><span class="flex-1 text-[15px] font-semibold">{{ $case->santri->name }}</span><span class="text-xs text-slate">ditutup {{ $case->closed_at?->translatedFormat('j M Y') }}</span></a></li>
                    @endforeach
                </ul>
            </x-card>
        @endif
    </div>
</x-app-layout>
