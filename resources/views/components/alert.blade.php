{{-- resources/views/components/alert.blade.php --}}
{{-- Desain "Alert": ikon + judul + isi. Varian: info, success, warning, danger. --}}
@props(['variant' => 'info', 'title' => null, 'icon' => null])
@php
    [$box, $iconColor, $defaultIcon] = match ($variant) {
        'success' => ['bg-success-wash ring-success/20', 'text-success', 'circle-check'],
        'warning' => ['bg-warning-wash ring-warning/20', 'text-warning', 'triangle-alert'],
        'danger' => ['bg-danger-wash ring-danger-line', 'text-danger', 'circle-x'],
        default => ['bg-blue-wash ring-blue/20', 'text-blue', 'info'],
    };
@endphp
<div {{ $attributes->class(["flex gap-3.5 rounded-2xl px-5 py-4 ring-1 ring-inset $box"]) }}>
    <x-icon :name="$icon ?? $defaultIcon" class="{{ $iconColor }}" />
    <div class="min-w-0 flex-1 space-y-0.5 text-sm">
        @if ($title)
            <p class="font-bold text-ink">{{ $title }}</p>
        @endif
        <div class="leading-[1.5] text-slate">{{ $slot }}</div>
    </div>
</div>
