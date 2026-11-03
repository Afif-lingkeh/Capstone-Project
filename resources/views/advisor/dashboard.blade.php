{{-- resources/views/advisor/dashboard.blade.php --}}
<x-app-layout>
    <x-slot name="header">Beranda</x-slot>

    <section class="relative mb-7 flex flex-col gap-6 overflow-hidden rounded-[28px] bg-gradient-to-r from-ink to-cobalt px-7 py-8 text-white lg:flex-row lg:items-center lg:px-9">
        <div aria-hidden="true" class="pointer-events-none absolute -top-28 right-20 size-[360px] rounded-full bg-teal-light/25 blur-[50px]"></div>
        <div class="relative flex flex-1 flex-col gap-2.5">
            <p class="text-xs font-bold uppercase tracking-[0.15em] text-teal-light">Assalamu’alaikum · semoga harimu berkah</p>
            <p class="text-[28px] font-extrabold tracking-[-0.02em] lg:text-4xl">{{ auth()->user()->name }}</p>
            <p class="w-fit rounded-full bg-white/10 px-3 py-1.5 text-xs font-semibold">{{ $advisees->count() }} santri binaan · {{ $openSlots }} slot kosong</p>
        </div>
        <a href="{{ route('advisor.slots.index') }}" class="btn relative w-fit rounded-full bg-white text-ink hover:bg-blue-wash"><x-icon name="calendar-plus" class="size-[18px]" />Buka slot jadwal</a>
    </section>

    @if ($toRecord)
        <x-alert variant="warning" title="{{ $toRecord }} sesi belum dicatat hasilnya" class="mb-6">
            <a href="{{ route('advisor.sessions.index') }}" class="font-bold text-blue underline">Catat sekarang</a> agar santri bisa membaca catatan bimbingannya.
        </x-alert>
    @endif

    <div class="flex flex-col gap-6 xl:flex-row xl:items-start">
        <x-card title="Sesi berikutnya" :padded="false" class="flex-1">
            <x-slot name="actions"><a href="{{ route('advisor.sessions.index') }}" class="text-sm font-bold text-blue hover:underline">Riwayat sesi</a></x-slot>
            <ul class="divide-y divide-hairline">
                @forelse ($upcoming as $booking)
                    <x-session-row :date="$booking->slot->starts_at" :title="$booking->santri->name.' · '.$booking->topic" :status="$booking->status"
                                   :meta="$booking->slot->starts_at->format('H.i').' WIB · '.$booking->slot->mode->label().($booking->slot->location ? ' · '.$booking->slot->location : '')" />
                @empty
                    <li class="px-6 py-8 text-center text-sm text-slate">Belum ada sesi yang dipesan.</li>
                @endforelse
            </ul>
        </x-card>

        <x-card title="Santri binaan" :padded="false" class="xl:w-[420px]">
            <x-slot name="actions"><a href="{{ route('advisor.students.index') }}" class="text-sm font-bold text-blue hover:underline">Hasil tes</a></x-slot>
            <ul class="divide-y divide-hairline">
                @forelse ($advisees as $santri)
                    <li>
                        <a href="{{ route('advisor.students.show', $santri) }}" class="flex items-center gap-3 px-6 py-3.5 hover:bg-cloud">
                            <span class="flex size-9 items-center justify-center rounded-full bg-blue-wash text-xs font-bold text-cobalt">{{ Str::upper(Str::substr($santri->name, 0, 2)) }}</span>
                            <span class="flex flex-1 flex-col"><span class="text-sm font-bold">{{ $santri->name }}</span><span class="text-xs text-slate">{{ $santri->santriProfile ? $santri->santriProfile->level->label().' · '.$santri->santriProfile->class_name : '—' }}</span></span>
                            <x-badge :tone="$santri->tests_done ? 'success' : 'neutral'">{{ $santri->tests_done ? 'Sudah tes' : 'Belum tes' }}</x-badge>
                        </a>
                    </li>
                @empty
                    <li class="px-6 py-8 text-center text-sm text-slate">Belum ada santri binaan.</li>
                @endforelse
            </ul>
        </x-card>
    </div>
</x-app-layout>
