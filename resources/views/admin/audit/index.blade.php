{{-- resources/views/admin/audit/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">Log Audit</x-slot>

    <x-page-intro title="Log audit data sensitif" description="Metadata akses saja: siapa, kapan, dan apa yang dibuka. Isi catatan konseling tidak pernah tampil di sini." />

    <form method="GET" class="mb-4 flex flex-col gap-3 sm:flex-row">
        <label for="q" class="sr-only">Cari pengguna atau santri</label>
        <x-text-input id="q" name="q" type="search" :value="request('q')" placeholder="Cari nama pengguna atau santri" class="sm:flex-1" />
        <x-select-input name="aksi" aria-label="Jenis aksi" class="sm:w-64">
            <option value="">Semua aksi</option>
            @foreach ($actions as $action)<option value="{{ $action }}" @selected(request('aksi') === $action)>{{ (new \App\Models\AuditLog(['action' => $action]))->actionLabel() }}</option>@endforeach
        </x-select-input>
        <button type="submit" class="btn-secondary">Filter</button>
    </form>

    <div class="overflow-x-auto rounded-3xl bg-white ring-1 ring-hairline">
        <table class="min-w-full divide-y divide-hairline text-sm">
            <thead class="bg-cloud text-left text-[11px] font-bold uppercase tracking-[0.12em] text-slate">
                <tr><th class="px-5 py-3">Waktu</th><th class="px-5 py-3">Pengguna</th><th class="px-5 py-3">Aksi</th><th class="px-5 py-3">Santri terkait</th><th class="px-5 py-3">Objek</th><th class="px-5 py-3">IP</th></tr>
            </thead>
            <tbody class="divide-y divide-hairline">
                @forelse ($logs as $log)
                    <tr>
                        <td class="whitespace-nowrap px-5 py-3 tabular-nums text-slate">{{ $log->created_at->translatedFormat('j M Y H.i') }}</td>
                        <td class="px-5 py-3 font-semibold">{{ $log->user?->name ?? 'Sistem' }}</td>
                        <td class="px-5 py-3">{{ $log->actionLabel() }}</td>
                        <td class="px-5 py-3">{{ $log->santri?->name ?? '—' }}</td>
                        <td class="whitespace-nowrap px-5 py-3 font-mono text-xs text-slate">{{ $log->subject_type }} #{{ $log->subject_id }}</td>
                        <td class="px-5 py-3 font-mono text-xs text-slate">{{ $log->ip_address }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-5 py-10 text-center text-slate">Belum ada catatan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $logs->links('pagination.peduli') }}</div>
</x-app-layout>
