{{-- resources/views/counselor/shifts/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">Jadwal Piket</x-slot>

    <x-page-intro title="Jadwal piket saya" description="Selama piket, Anda menerima notifikasi krisis lebih dulu (in-app dan email). Jadwal diatur admin pesantren." />

    <div class="flex flex-col gap-6 lg:flex-row lg:items-start">
        <div class="flex flex-col gap-3 rounded-3xl bg-ink p-6 text-white lg:w-[340px]">
            <p class="text-xs font-bold uppercase tracking-[0.13em] text-teal-light">Sedang piket</p>
            @forelse ($onDuty as $shift)
                <p class="text-base font-bold">{{ $shift->counselor->name }} <span class="font-medium text-mist">s.d. {{ $shift->ends_at->format('H.i') }}</span></p>
            @empty
                <p class="text-sm text-hairline">Tidak ada yang piket. Peringatan krisis dikirim ke semua konselor.</p>
            @endforelse
        </div>
        <x-card title="Piket yang akan datang" :padded="false" class="flex-1">
            <ul class="divide-y divide-hairline">
                @forelse ($mine as $shift)
                    <li class="flex items-center gap-5 px-6 py-4"><x-date-tile :date="$shift->starts_at" /><span class="text-[15px] font-bold">{{ $shift->starts_at->translatedFormat('l') }}, {{ $shift->starts_at->format('H.i') }}–{{ $shift->ends_at->format('H.i') }} WIB</span></li>
                @empty
                    <li class="px-6 py-8 text-center text-sm text-slate">Belum ada jadwal piket.</li>
                @endforelse
            </ul>
        </x-card>
    </div>
</x-app-layout>
