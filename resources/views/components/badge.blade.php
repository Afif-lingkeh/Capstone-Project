{{-- resources/views/components/badge.blade.php --}}
{{-- Desain "Badge/Status": pil dengan titik warna + label (status tidak hanya dibawa warna). --}}
@props(['tone' => 'neutral', 'dot' => true])
@php
    [$box, $dotColor] = match ($tone) {
        'blue' => ['bg-blue-wash text-cobalt', 'bg-blue'],
        'success' => ['bg-success-wash text-success', 'bg-success'],
        'warning' => ['bg-warning-wash text-warning', 'bg-warning'],
        'danger' => ['bg-danger-wash text-danger-ink', 'bg-danger'],
        'gold' => ['bg-gold-wash text-gold-ink', 'bg-gold'],
        'teal' => ['bg-teal-wash text-teal-ink', 'bg-teal'],
        'violet' => ['bg-violet-wash text-violet-ink', 'bg-violet-ink'],
        default => ['bg-pebble text-slate', 'bg-mist'],
    };
@endphp
<span {{ $attributes->class("inline-flex w-fit shrink-0 items-center gap-1.5 whitespace-nowrap rounded-full px-3 py-1.5 text-xs font-bold $box") }}>
    @if ($dot)<span aria-hidden="true" class="size-[7px] rounded-full {{ $dotColor }}"></span>@endif
    {{ $slot }}
</span>
