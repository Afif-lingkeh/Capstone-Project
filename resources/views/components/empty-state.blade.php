{{-- resources/views/components/empty-state.blade.php --}}
{{-- Desain "Empty State". --}}
@props(['icon' => 'inbox', 'title'])

<div {{ $attributes->class('flex flex-col items-center gap-3 rounded-3xl border-[1.5px] border-dashed border-hairline bg-white px-6 py-12 text-center') }}>
    <span class="flex size-12 items-center justify-center rounded-2xl bg-pebble"><x-icon :name="$icon" class="text-slate" /></span>
    <p class="text-base font-extrabold">{{ $title }}</p>
    @if (trim($slot) !== '')<div class="max-w-md text-sm leading-[1.57] text-slate">{{ $slot }}</div>@endif
    @isset($action)<div class="mt-2">{{ $action }}</div>@endisset
</div>
