{{-- resources/views/admin/links/create.blade.php --}}
<x-app-layout>
    <x-slot name="header">Tambah Tautan Resmi</x-slot>

    <form method="POST" action="{{ route('admin.links.store') }}" class="max-w-2xl">
        @csrf
        @include('admin.links.partials.form')
    </form>
</x-app-layout>
