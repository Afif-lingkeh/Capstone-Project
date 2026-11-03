{{-- resources/views/components/progress-ring.blade.php --}}
{{-- Desain "Ring": cincin progres turquoise dengan persentase di tengah. --}}
@props(['percent'])

@php($circumference = 2 * M_PI * 43)
<div {{ $attributes->class('relative size-24 shrink-0') }}>
    <svg viewBox="0 0 96 96" class="size-24 -rotate-90" aria-hidden="true">
        <circle cx="48" cy="48" r="43" fill="none" stroke-width="9.6" class="stroke-pebble" />
        <circle cx="48" cy="48" r="43" fill="none" stroke-width="9.6" stroke-linecap="round" class="stroke-teal"
                stroke-dasharray="{{ $circumference }}" stroke-dashoffset="{{ $circumference * (1 - $percent / 100) }}" />
    </svg>
    <span class="absolute inset-0 flex items-center justify-center text-xl font-extrabold tabular-nums">{{ $percent }}%</span>
</div>
