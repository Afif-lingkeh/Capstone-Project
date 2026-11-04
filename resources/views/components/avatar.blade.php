{{-- resources/views/components/avatar.blade.php --}}
{{-- Desain "Avatar": foto profil bila ada, inisial bila belum. Foto berupa konten → garis tipis hitam 10%. --}}
@props(['user', 'size' => 'size-10', 'text' => 'text-sm'])

@if ($url = $user->avatarUrl())
    <img src="{{ $url }}" alt="" {{ $attributes->class("$size shrink-0 rounded-full object-cover outline outline-1 -outline-offset-1 outline-black/10") }}>
@else
    <span aria-hidden="true" {{ $attributes->class("$size $text flex shrink-0 items-center justify-center rounded-full bg-blue-wash font-bold text-cobalt") }}>{{ $user->initials() }}</span>
@endif
