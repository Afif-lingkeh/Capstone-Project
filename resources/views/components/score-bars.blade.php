{{-- resources/views/components/score-bars.blade.php --}}
{{-- Desain "Score/Bar": empat jalur, diurutkan dari skor tertinggi. Angka persentase selalu tertulis (tidak hanya warna/panjang). --}}
@props(['scores', 'compact' => false])

@php
    $rows = collect(\App\Enums\Pathway::cases())
        ->map(fn ($p) => ['pathway' => $p, 'value' => $scores[$p->value] ?? null])
        ->sortByDesc(fn ($r) => $r['value'] ?? -1);
@endphp
<ul {{ $attributes->class(['flex flex-col', 'gap-4' => ! $compact, 'gap-2.5' => $compact]) }}>
    @foreach ($rows as $row)
        <li class="flex flex-col gap-2">
            <div class="flex items-center justify-between gap-3">
                <span @class(['font-bold', 'text-[15px]' => ! $compact, 'text-[13px]' => $compact])>{{ $row['pathway']->label() }}</span>
                <span @class(['font-extrabold tabular-nums', 'text-[15px]' => ! $compact, 'text-[13px]' => $compact])>{{ $row['value'] === null ? '—' : $row['value'].'%' }}</span>
            </div>
            <div @class(['w-full overflow-hidden rounded-full bg-pebble', 'h-2.5' => ! $compact, 'h-1.5' => $compact])
                 role="img" aria-label="{{ $row['pathway']->label() }} {{ $row['value'] ?? 0 }} persen">
                <div class="h-full rounded-full {{ $row['pathway']->barClass() }}" style="width: {{ $row['value'] ?? 0 }}%"></div>
            </div>
        </li>
    @endforeach
</ul>
