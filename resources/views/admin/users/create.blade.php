{{-- resources/views/admin/users/create.blade.php --}}
<x-app-layout>
    <x-slot name="header">Tambah Pengguna</x-slot>

    <form method="POST" action="{{ route('admin.users.store') }}" class="max-w-3xl">
        @csrf
        @include('admin.users.partials.form')
    </form>
</x-app-layout>
