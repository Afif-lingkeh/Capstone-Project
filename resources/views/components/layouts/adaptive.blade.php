{{-- resources/views/components/layouts/adaptive.blade.php --}}
{{-- Halaman publik yang juga dibuka dari dalam aplikasi: tamu melihat layout publik,
     pengguna yang login melihat layout dengan sidebar. Untuk tamu, halaman mengatur lebarnya sendiri
     (bagian penuh selebar layar), jadi slot tidak dibungkus. --}}
@props(['title' => null])

@auth
    <x-app-layout>
        <x-slot name="header">{{ $title }}</x-slot>
        <div class="mx-auto max-w-6xl">{{ $slot }}</div>
    </x-app-layout>
@else
    <x-layouts.public :title="$title">{{ $slot }}</x-layouts.public>
@endauth
