{{-- resources/views/admin/institutions/create.blade.php --}}
<x-app-layout>
    <x-slot name="header">Tambah Institusi</x-slot>

    <form method="POST" action="{{ route('admin.institutions.store') }}" class="max-w-2xl">
        @csrf
        @include('admin.institutions.partials.form')
    </form>
</x-app-layout>
