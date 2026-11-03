{{-- resources/views/admin/institutions/edit.blade.php --}}
<x-app-layout>
    <x-slot name="header">Ubah Institusi: {{ $institution->name }}</x-slot>

    <form method="POST" action="{{ route('admin.institutions.update', $institution) }}" class="max-w-2xl">
        @csrf
        @method('PUT')
        @include('admin.institutions.partials.form')
    </form>
</x-app-layout>
