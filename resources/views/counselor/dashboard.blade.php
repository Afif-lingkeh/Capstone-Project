{{-- resources/views/counselor/dashboard.blade.php --}}
<x-app-layout>
    <x-slot name="header">Beranda</x-slot>

    <section class="relative mb-7 flex flex-col gap-6 overflow-hidden rounded-[28px] bg-gradient-to-r from-ink to-cobalt px-7 py-8 text-white lg:flex-row lg:items-center lg:px-9">
        <div aria-hidden="true" class="pointer-events-none absolute -top-28 right-20 size-[360px] rounded-full bg-teal-light/25 blur-[50px]"></div>
        <div class="relative flex flex-1 flex-col gap-2.5">
            <p class="text-xs font-bold uppercase tracking-[0.15em] text-teal-light">Assalamu’alaikum · semoga harimu berkah</p>
            <p class="text-[28px] font-extrabold tracking-[-0.02em] lg:text-4xl">{{ auth()->user()->name }}</p>
            <p class="w-fit rounded-full bg-white/10 px-3 py-1.5 text-xs font-semibold">{{ $onDuty ? 'Sedang piket s.d. '.$onDuty->ends_at->format('H.i').' WIB' : 'Tidak sedang piket' }}</p>
        </div>
        <a href="{{ route('counselor.requests.index') }}" class="btn relative w-fit rounded-full bg-white text-ink hover:bg-blue-wash">Buka antrean <x-icon name="arrow-right" class="size-[18px]" /></a>
    </section>

    <div class="mb-7 grid grid-cols-2 gap-4 lg:grid-cols-4">
        <x-stat-card label="Peringatan krisis" :value="$stats['alerts']" icon="siren" :tone="$stats['alerts'] ? 'warning' : 'default'" :href="route('counselor.alerts.index')" />
        <x-stat-card label="Permintaan menunggu" :value="$stats['pending']" icon="inbox" :href="route('counselor.requests.index')" />
        <x-stat-card label="Kasus aktif saya" :value="$stats['cases']" icon="folder-open" :href="route('counselor.cases.index')" />
        <x-stat-card label="Alih tangan masuk" :value="$stats['handovers']" icon="arrow-left-right" :href="route('counselor.handovers.index')" />
    </div>

    <x-card title="Sesi berikutnya" :padded="false">
        <ul class="divide-y divide-hairline">
            @forelse ($nextSessions as $session)
                <li>
                    <a href="{{ route('counselor.cases.show', $session->case) }}" class="flex items-center gap-5 px-6 py-4 hover:bg-cloud">
                        <x-date-tile :date="$session->next_session_at" />
                        <span class="flex flex-1 flex-col gap-0.5"><span class="text-base font-bold">{{ $session->case->santri->name }}</span><span class="text-[13px] text-slate">{{ $session->next_session_at->format('H.i') }} WIB · {{ $session->case->code }}</span></span>
                        <x-badge :tone="$session->case->urgency->tone()">{{ $session->case->urgency->label() }}</x-badge>
                    </a>
                </li>
            @empty
                <li class="px-6 py-8 text-center text-sm text-slate">Belum ada sesi terjadwal.</li>
            @endforelse
        </ul>
    </x-card>
</x-app-layout>
