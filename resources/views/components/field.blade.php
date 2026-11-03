{{-- resources/views/components/field.blade.php --}}
{{-- Pembungkus label + kontrol + bantuan + pesan error. Isi slot dengan input/select/textarea (beri id = $for). --}}
@props(['label', 'for', 'error' => null, 'hint' => null, 'optional' => false])

<div {{ $attributes->class('flex flex-col gap-2') }}>
    <label for="{{ $for }}" class="text-sm font-bold text-ink">
        {{ $label }}@if ($optional) <span class="font-medium text-slate">(opsional)</span>@endif
    </label>
    {{ $slot }}
    @if ($hint)<p id="{{ $for }}-hint" class="text-[13px] leading-5 text-slate">{{ $hint }}</p>@endif
    <x-input-error :messages="$errors->get($error ?? $for)" />
</div>
