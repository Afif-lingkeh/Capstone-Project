{{-- resources/views/counselor/requests/show.blade.php --}}
@php($santri = $counselingRequest->santri)
<x-app-layout>
    <x-slot name="header">Permintaan Konseling</x-slot>

    <div class="mx-auto flex max-w-3xl flex-col gap-6">
        <a href="{{ route('counselor.requests.index') }}" class="flex w-fit items-center gap-1.5 py-1 text-sm font-bold text-blue hover:underline"><x-icon name="arrow-left" class="size-4" /> Antrean</a>

        <x-card>
            <div class="flex flex-col gap-5">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="flex flex-col gap-1">
                        <h1 class="text-xl font-extrabold">{{ $santri->name }}</h1>
                        <p class="text-sm text-slate">{{ $santri->gender?->label() }}@if ($santri->santriProfile) · {{ $santri->santriProfile->level->label() }} Kelas {{ $santri->santriProfile->class_name }} · NIS {{ $santri->santriProfile->nis }}@endif</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        @if ($counselingRequest->is_crisis)<x-badge tone="danger">Tanda krisis</x-badge>@endif
                        <x-badge :tone="$counselingRequest->urgency->tone()">{{ $counselingRequest->urgency->label() }}</x-badge>
                    </div>
                </div>
                <dl class="grid gap-4 sm:grid-cols-2">
                    <div class="flex flex-col gap-1"><dt class="text-[11px] font-bold uppercase tracking-[0.11em] text-slate">Topik</dt><dd class="text-[15px] font-medium">{{ $counselingRequest->topic->label() }}</dd></div>
                    <div class="flex flex-col gap-1"><dt class="text-[11px] font-bold uppercase tracking-[0.11em] text-slate">Waktu yang diinginkan</dt><dd class="text-[15px] font-medium">{{ $counselingRequest->preferred_time ?: '—' }}</dd></div>
                    <div class="flex flex-col gap-1 sm:col-span-2"><dt class="text-[11px] font-bold uppercase tracking-[0.11em] text-slate">Cerita santri</dt><dd class="whitespace-pre-line rounded-2xl bg-cloud p-4 text-[15px] leading-[1.7]">{{ $counselingRequest->story }}</dd></div>
                </dl>
                <x-audit-notice />
                <form method="POST" action="{{ route('counselor.requests.accept', $counselingRequest) }}">
                    @csrf
                    <x-primary-button>Ambil permintaan ini <x-icon name="arrow-right" class="size-[18px]" /></x-primary-button>
                </form>
            </div>
        </x-card>
    </div>
</x-app-layout>
