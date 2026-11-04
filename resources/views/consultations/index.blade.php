{{-- resources/views/consultations/index.blade.php --}}
@php
    $row = function ($c) use ($isAlumni) {
        $other = $isAlumni ? 'Santri'.($c->santri->santriProfile ? ' kelas '.$c->santri->santriProfile->class_name : '') : ($c->alumni->alumniProfile?->publicName() ?? $c->alumni->name);
        return [$other, $c->messages->first()?->body];
    };
@endphp
<x-app-layout>
    <x-slot name="header">Konsultasi</x-slot>

    <x-page-intro :title="$isAlumni ? 'Konsultasi dari santri' : 'Konsultasi dengan alumni'" description="Percakapan dapat ditinjau Admin Pesantren demi keamanan bersama.">
        @unless ($isAlumni)
            <x-slot name="actions"><a href="{{ route('alumni.index') }}" class="btn-primary"><x-icon name="users" class="size-[18px]" />Cari alumni</a></x-slot>
        @endunless
    </x-page-intro>

    <div class="flex flex-col gap-6">
        @foreach (['Menunggu jawaban' => $pending, 'Berlangsung' => $active, 'Selesai' => $past] as $title => $items)
            @if ($items->isNotEmpty() || $title === 'Berlangsung')
                <x-card :title="$title.' ('.$items->count().')'" :padded="false">
                    <ul class="divide-y divide-hairline">
                        @forelse ($items as $consultation)
                            @php([$other, $last] = $row($consultation))
                            <li>
                                <a href="{{ route('consultations.show', $consultation) }}" class="flex flex-col gap-2 px-6 py-4 hover:bg-cloud sm:flex-row sm:items-center sm:gap-4">
                                    <span class="flex flex-1 flex-col gap-0.5">
                                        <span class="text-[15px] font-bold">{{ $consultation->subject }}</span>
                                        <span class="text-[13px] text-slate">{{ $other }} · {{ ($consultation->last_message_at ?? $consultation->updated_at)->diffForHumans() }}</span>
                                        @if ($last)<span class="line-clamp-1 text-sm text-slate">{{ $last }}</span>@endif
                                    </span>
                                    <x-badge :tone="$consultation->statusTone()">{{ $consultation->statusLabel() }}</x-badge>
                                </a>
                            </li>
                        @empty
                            <li class="px-6 py-8 text-center text-sm text-slate">Belum ada konsultasi.</li>
                        @endforelse
                    </ul>
                </x-card>
            @endif
        @endforeach
    </div>
</x-app-layout>
