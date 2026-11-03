{{-- resources/views/components/sidebar-link.blade.php --}}
@props(['active' => false])

<a {{ $attributes->class([
    'block rounded-md px-3 py-2 text-sm font-medium transition',
    'bg-emerald-900 text-white' => $active,
    'text-emerald-100 hover:bg-emerald-700 hover:text-white' => ! $active,
]) }} @if ($active) aria-current="page" @endif>
    {{ $slot }}
</a>
