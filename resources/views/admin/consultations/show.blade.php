{{-- resources/views/admin/consultations/show.blade.php --}}
<x-app-layout>
    <x-slot name="header">Moderasi Konsultasi</x-slot>
    <x-slot name="subtitle">{{ $consultation->subject }}</x-slot>

    <a href="{{ route('admin.consultations.index') }}" class="mb-5 flex w-fit items-center gap-1.5 py-1 text-sm font-bold text-blue hover:underline"><x-icon name="arrow-left" class="size-4" /> Semua konsultasi</a>

    <x-alert icon="scroll-text" title="Akses ini tercatat dan kedua pihak diberi tahu" class="mb-6">Santri {{ $consultation->santri->name }} dan alumni {{ $consultation->alumni->name }} menerima pemberitahuan bahwa utas ini ditinjau.</x-alert>

    <div class="flex flex-col gap-6 xl:flex-row xl:items-start">
        <section class="flex flex-1 flex-col overflow-hidden rounded-3xl bg-white ring-1 ring-hairline">
            <header class="flex items-center justify-between gap-3 border-b border-hairline px-6 py-4">
                <h1 class="text-lg font-extrabold">{{ $consultation->subject }}</h1>
                <x-badge :tone="$consultation->statusTone()">{{ $consultation->statusLabel() }}</x-badge>
            </header>
            <ol class="flex flex-col gap-4 bg-cloud px-6 py-6">
                @foreach ($consultation->messages as $message)
                    <li class="flex flex-col gap-1">
                        <span class="text-xs font-semibold text-slate">{{ $message->sender?->name ?? 'Pengguna terhapus' }} ({{ $message->sender_id === $consultation->alumni_id ? 'alumni' : 'santri' }}) · {{ $message->created_at->translatedFormat('j M Y H.i') }}</span>
                        <p class="whitespace-pre-line rounded-[20px] bg-white px-4 py-3 text-[15px] leading-[1.6] ring-1 ring-inset ring-hairline">{{ $message->body }}</p>
                    </li>
                @endforeach
            </ol>
        </section>

        <aside class="flex flex-col gap-4 xl:w-[380px]">
            @foreach ($consultation->reports as $report)
                <div class="flex flex-col gap-3 rounded-3xl bg-white p-6 ring-1 ring-inset ring-hairline">
                    <div class="flex items-center justify-between"><p class="text-sm font-bold">Laporan dari {{ $report->reporter?->name }}</p><x-badge :tone="$report->status === 'open' ? 'danger' : 'success'">{{ $report->status === 'open' ? 'Terbuka' : 'Selesai' }}</x-badge></div>
                    <p class="text-sm">{{ $report->reason }}</p>
                    @if ($report->status === 'open')
                        <form method="POST" action="{{ route('admin.consultations.resolve', $report) }}" class="flex flex-col gap-3">
                            @csrf
                            <x-field label="Tindak lanjut" :for="'res-'.$report->id" error="resolution_note"><x-textarea :id="'res-'.$report->id" name="resolution_note" rows="2" required></x-textarea></x-field>
                            <label class="flex items-center gap-2 text-sm font-semibold"><input type="checkbox" name="close_thread" value="1" class="size-5 rounded-md border-mist text-blue focus:ring-focus">Tutup utas ini</label>
                            <x-primary-button>Tandai selesai</x-primary-button>
                        </form>
                    @else
                        <p class="rounded-2xl bg-cloud p-3 text-sm text-slate">{{ $report->resolution_note }}</p>
                    @endif
                </div>
            @endforeach
        </aside>
    </div>
</x-app-layout>
