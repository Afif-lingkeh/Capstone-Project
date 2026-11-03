{{-- resources/views/components/textarea.blade.php --}}
@props(['disabled' => false])

<textarea @disabled($disabled) {{ $attributes->merge(['class' => 'field h-auto min-h-[120px] py-3 leading-relaxed', 'rows' => 5]) }}>{{ $slot }}</textarea>
