{{-- resources/views/components/select-input.blade.php --}}
@props(['disabled' => false])

<select @disabled($disabled) {{ $attributes->merge(['class' => 'field pr-10']) }}>
    {{ $slot }}
</select>
