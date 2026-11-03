{{-- resources/views/admin/consultations/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">Moderasi Konsultasi</x-slot>

    <x-page-intro title="Moderasi konsultasi alumni" description="Membuka utas mengirim pemberitahuan ke santri dan alumninya, dan tercatat di log audit." />

    <div class="flex flex-col gap-6">
        <x-card :title="'Laporan terbuka ('.$reports->count().')'" :padded="false">
            <ul class="divide-y divide-hairline">
                @forelse ($reports as $report)
                    <li>
                        <a href="{{ route('admin.consultations.show', $report->consultation) }}" class="flex flex-col gap-2 bg-danger-wash/40 px-6 py-4 hover:bg-danger-wash sm:flex-row sm:items-center sm:gap-4">
                            <x-icon name="flag" class="text-danger" />
                            <span class="flex flex-1 flex-col gap-0.5">
                                <span class="text-[15px] font-bold">{{ $report->consultation->subject }}</span>
                                <span class="text-[13px] text-slate">Dilaporkan {{ $report->reporter?->name }} · {{ $report->created_at->diffForHumans() }}</span>
                                <span class="line-clamp-1 text-sm">“{{ $report->reason }}”</span>
                            </span>
                        </a>
                    </li>
                @empty
                    <li class="px-6 py-8 text-center text-sm text-slate">Tidak ada laporan terbuka.</li>
                @endforelse
            </ul>
        </x-card>

        <div class="overflow-x-auto rounded-3xl bg-white ring-1 ring-hairline">
            <table class="min-w-full divide-y divide-hairline text-sm">
                <thead class="bg-cloud text-left text-[11px] font-bold uppercase tracking-[0.12em] text-slate">
                    <tr><th class="px-5 py-3">Topik</th><th class="px-5 py-3">Santri</th><th class="px-5 py-3">Alumni</th><th class="px-5 py-3">Pesan</th><th class="px-5 py-3">Status</th><th class="px-5 py-3 text-right">Aksi</th></tr>
                </thead>
                <tbody class="divide-y divide-hairline">
                    @forelse ($consultations as $c)
                        <tr>
                            <td class="px-5 py-4 font-bold">{{ $c->subject }}</td>
                            <td class="px-5 py-4">{{ $c->santri->name }}</td>
                            <td class="px-5 py-4">{{ $c->alumni->name }}</td>
                            <td class="px-5 py-4">{{ $c->messages_count }}</td>
                            <td class="px-5 py-4"><x-badge :tone="$c->statusTone()">{{ $c->statusLabel() }}</x-badge></td>
                            <td class="px-5 py-4 text-right"><a href="{{ route('admin.consultations.show', $c) }}" class="font-bold text-blue hover:underline">Tinjau<span class="sr-only"> {{ $c->subject }}</span></a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-10 text-center text-slate">Belum ada konsultasi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $consultations->links('pagination.peduli') }}
    </div>
</x-app-layout>
