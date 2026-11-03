{{-- resources/views/components/stat-card.blade.php --}}
{{-- Desain "Card/Stat". --}}
@props(['label', 'value', 'tone' => 'default', 'icon' => null, 'caption' => null, 'href' => null])

@php($tag = $href ? 'a' : 'div')
<{{ $tag }} @if ($href) href="{{ $href }}" @endif {{ $attributes->class(['flex flex-col gap-3 rounded-3xl bg-white p-6 ring-1 ring-inset ring-hairline', 'transition-colors duration-150 hover:bg-pebble' => $href]) }}>
    <div class="flex items-center justify-between gap-3">
        <p class="text-sm font-semibold text-slate">{{ $label }}</p>
        @if ($icon)
            <span @class(['flex size-10 items-center justify-center rounded-xl', 'bg-danger-wash text-danger' => $tone === 'warning', 'bg-blue-wash text-blue' => $tone !== 'warning'])><x-icon :name="$icon" class="size-[18px]" /></span>
        @endif
    </div>
    <p @class(['text-[34px] font-extrabold leading-none tracking-[-0.03em]', 'text-danger-ink' => $tone === 'warning'])>{{ $value }}</p>
    @if ($caption)<p class="text-[13px] font-medium text-slate">{{ $caption }}</p>@endif
</{{ $tag }}>
