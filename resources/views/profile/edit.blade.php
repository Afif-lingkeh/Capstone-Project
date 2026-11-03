{{-- resources/views/profile/edit.blade.php --}}
<x-app-layout>
    <x-slot name="header">Profil Saya</x-slot>

    <div class="max-w-2xl space-y-6">
        <div class="rounded-3xl bg-white p-6 ring-1 ring-inset ring-hairline">
            @include('profile.partials.update-profile-information-form')
        </div>

        <div class="rounded-3xl bg-white p-6 ring-1 ring-inset ring-hairline">
            @include('profile.partials.update-password-form')
        </div>
    </div>
</x-app-layout>
