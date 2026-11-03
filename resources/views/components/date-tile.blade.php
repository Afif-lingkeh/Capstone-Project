{{-- resources/views/components/date-tile.blade.php --}}
{{-- Desain "Date Tile": hari singkat + tanggal. --}}
@props(['date', 'muted' => false])

<span {{ $attributes->class(['flex h-[68px] w-16 shrink-0 flex-col items-center justify-center rounded-2xl', 'bg-pebble' => $muted, 'bg-blue-wash' => ! $muted]) }}>
    <span @class(['text-[11px] font-bold uppercase tracking-[0.09em]', 'text-slate' => $muted, 'text-cobalt' => ! $muted])>{{ Str::upper(Str::substr($date->translatedFormat('D'), 0, 3)) }}</span>
    <span class="text-2xl font-extrabold">{{ $date->format('d') }}</span>
</span>
