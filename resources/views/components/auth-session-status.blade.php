@props(['status'])

@if ($status)
    <x-alert variant="success" role="status" {{ $attributes }}>{{ $status }}</x-alert>
@endif
