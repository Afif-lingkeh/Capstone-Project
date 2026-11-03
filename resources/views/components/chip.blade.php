{{-- resources/views/components/chip.blade.php --}}
{{-- Desain "Chip/Filter": tautan filter berbentuk pil; aktif = navy. --}}
@props(['href', 'active' => false, 'count' => null])

<a href="{{ $href }}" @if ($active) aria-current="page" @endif {{ $attributes->class([
    'inline-flex shrink-0 items-center gap-2 whitespace-nowrap rounded-full border-[1.5px] px-[18px] py-3 text-sm font-semibold transition-colors duration-150',
    'border-ink bg-ink text-white' => $active,
    'border-hairline bg-white text-ink hover:bg-pebble' => ! $active,
]) }}>
    {{ $slot }}
    @if ($count !== null)<span @class(['text-xs font-bold', 'text-mist' => $active, 'text-slate' => ! $active])>{{ $count }}</span>@endif
</a>
