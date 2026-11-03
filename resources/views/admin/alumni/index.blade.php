{{-- resources/views/admin/alumni/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">Verifikasi Alumni</x-slot>

    <x-page-intro title="Verifikasi alumni" description="Cocokkan nama dan tahun lulus dengan arsip pesantren sebelum alumni tampil di direktori." />

    <nav aria-label="Status" class="-mx-5 mb-6 flex gap-2.5 overflow-x-auto px-5 pb-1 lg:mx-0 lg:px-0">
        @foreach (['pending' => 'Menunggu', 'verified' => 'Terverifikasi', 'rejected' => 'Ditolak'] as $value => $label)
            <x-chip :href="route('admin.alumni.index', ['status' => $value])" :active="$status === $value" :count="$counts[$value] ?? 0">{{ $label }}</x-chip>
        @endforeach
    </nav>

    @if ($profiles->isEmpty())
        <x-empty-state icon="user-check" title="Tidak ada data" />
    @else
        <div class="flex flex-col gap-4">
            @foreach ($profiles as $profile)
                <article class="flex flex-col gap-4 rounded-3xl bg-white p-6 ring-1 ring-inset ring-hairline lg:flex-row lg:items-start">
                    <dl class="grid flex-1 gap-x-6 gap-y-3 text-sm sm:grid-cols-2 xl:grid-cols-3">
                        <div class="sm:col-span-2 xl:col-span-3"><dt class="sr-only">Nama</dt><dd class="text-lg font-extrabold">{{ $profile->user->name }}</dd></div>
                        <div><dt class="text-xs font-bold text-slate">Lulus</dt><dd class="font-semibold">{{ $profile->graduation_year }}</dd></div>
                        <div><dt class="text-xs font-bold text-slate">Kampus/tempat kerja</dt><dd class="font-semibold">{{ $profile->institution_name }}</dd></div>
                        <div><dt class="text-xs font-bold text-slate">Negara</dt><dd class="font-semibold">{{ $profile->country?->name ?? '—' }}</dd></div>
                        <div><dt class="text-xs font-bold text-slate">Email</dt><dd class="break-all font-semibold">{{ $profile->user->email }}</dd></div>
                        <div><dt class="text-xs font-bold text-slate">WhatsApp (khusus admin)</dt><dd class="font-semibold">{{ $profile->whatsapp ?: '—' }}</dd></div>
                        <div><dt class="text-xs font-bold text-slate">Mendaftar</dt><dd class="font-semibold">{{ $profile->created_at->translatedFormat('j M Y') }}</dd></div>
                        @if ($profile->rejection_note)<div class="sm:col-span-2 xl:col-span-3"><dt class="text-xs font-bold text-slate">Alasan ditolak</dt><dd>{{ $profile->rejection_note }}</dd></div>@endif
                    </dl>
                    @if ($status !== 'verified')
                        <div class="flex flex-col gap-3 lg:w-80" x-data="{ reject: false }">
                            <form method="POST" action="{{ route('admin.alumni.decide', $profile) }}">@csrf<input type="hidden" name="decision" value="verified"><x-primary-button class="w-full">Verifikasi</x-primary-button></form>
                            <button type="button" @click="reject = ! reject" class="btn-ghost text-danger-ink" :aria-expanded="reject.toString()">Tolak…</button>
                            <form method="POST" action="{{ route('admin.alumni.decide', $profile) }}" x-show="reject" x-cloak class="flex flex-col gap-3">
                                @csrf <input type="hidden" name="decision" value="rejected">
                                <x-field label="Alasan (dikirim ke alumni)" :for="'note-'.$profile->id" error="rejection_note"><x-textarea :id="'note-'.$profile->id" name="rejection_note" rows="2" required></x-textarea></x-field>
                                <x-danger-button>Kirim penolakan</x-danger-button>
                            </form>
                        </div>
                    @endif
                </article>
            @endforeach
        </div>
        <div class="mt-4">{{ $profiles->links('pagination.peduli') }}</div>
    @endif
</x-app-layout>
