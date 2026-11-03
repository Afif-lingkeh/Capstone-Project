{{-- resources/views/admin/users/edit.blade.php --}}
<x-app-layout>
    <x-slot name="header">Ubah Pengguna: {{ $user->name }}</x-slot>

    <form method="POST" action="{{ route('admin.users.update', $user) }}" class="max-w-3xl">
        @csrf
        @method('PUT')
        @include('admin.users.partials.form')
    </form>
</x-app-layout>
