{{-- resources/views/components/status-badge.blade.php --}}
{{-- Dipakai untuk enum apa pun yang punya label() dan tone(). --}}
@props(['status'])

<x-badge :tone="$status->tone()" {{ $attributes }}>{{ $status->label() }}</x-badge>
