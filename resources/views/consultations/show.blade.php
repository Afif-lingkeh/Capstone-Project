{{-- resources/views/consultations/show.blade.php --}}
@php
    $me = auth()->user();
    $nameOf = fn ($user) => $user?->id === $consultation->alumni_id ? ($consultation->alumni->alumniProfile?->publicName() ?? $user->name) : ($user?->name ?? 'Pengguna');
@endphp
<x-app-layout>
    <x-slot name="header">Konsultasi</x-slot>
    <x-slot name="subtitle">{{ $consultation->subject }}</x-slot>

    <a href="{{ route('consultations.index') }}" class="mb-5 flex w-fit items-center gap-1.5 py-1 text-sm font-bold text-blue hover:underline"><x-icon name="arrow-left" class="size-4" /> Semua konsultasi</a>

    <div class="flex flex-col gap-6 xl:flex-row xl:items-start">
        <section aria-labelledby="utas" class="flex flex-1 flex-col overflow-hidden rounded-3xl bg-white ring-1 ring-hairline">
            <header class="flex flex-wrap items-center justify-between gap-3 border-b border-hairline px-6 py-4">
                <h1 id="utas" class="text-lg font-extrabold">{{ $consultation->subject }}</h1>
                <x-badge :tone="$consultation->statusTone()">{{ $consultation->statusLabel() }}</x-badge>
            </header>
            <ol class="flex flex-col gap-4 bg-cloud px-4 py-6 sm:px-6">
                @foreach ($consultation->messages as $message)
                    @php($mine = $message->sender_id === $me->id)
                    <li @class(['flex max-w-[85%] flex-col gap-1', 'self-end items-end' => $mine])>
                        <span class="px-1 text-xs font-semibold text-slate">{{ $mine ? 'Kamu' : $nameOf($message->sender) }} · {{ $message->created_at->translatedFormat('j M H.i') }}</span>
                        <p @class(['whitespace-pre-line rounded-[20px] px-4 py-3 text-[15px] leading-[1.6]', 'rounded-br-md bg-blue text-white' => $mine, 'rounded-bl-md bg-white ring-1 ring-inset ring-hairline' => ! $mine])>{{ $message->body }}</p>
                    </li>
                @endforeach
            </ol>

            @if ($consultation->status === 'accepted')
                <form method="POST" action="{{ route('consultations.message', $consultation) }}" class="flex items-end gap-3 border-t border-hairline p-4">
                    @csrf
                    <label for="body" class="sr-only">Tulis pesan</label>
                    <x-textarea id="body" name="body" rows="2" class="min-h-[52px] flex-1" placeholder="Tulis pesan…" required maxlength="2000"></x-textarea>
                    <button type="submit" class="btn-primary size-[52px] shrink-0 p-0"><x-icon name="send" /><span class="sr-only">Kirim</span></button>
                </form>
            @elseif ($consultation->status === 'pending')
                <div class="flex flex-col gap-3 border-t border-hairline p-6">
                    @if ($isAlumni)
                        <p class="text-sm text-slate">Terima permintaan ini untuk mulai menjawab.</p>
                        <div class="flex gap-2">
                            <form method="POST" action="{{ route('consultations.respond', $consultation) }}">@csrf<input type="hidden" name="decision" value="declined"><button type="submit" class="btn-ghost">Tolak</button></form>
                            <form method="POST" action="{{ route('consultations.respond', $consultation) }}">@csrf<input type="hidden" name="decision" value="accepted"><x-primary-button>Terima</x-primary-button></form>
                        </div>
                    @else
                        <p class="text-sm text-slate">Menunggu alumni menerima permintaanmu.</p>
                    @endif
                </div>
            @endif
        </section>

        <aside class="flex flex-col gap-4 xl:w-[340px]">
            <x-alert icon="shield" title="Demi keamanan bersama">Percakapan dapat ditinjau Admin Pesantren. Jangan bagikan nomor HP, alamat, atau data pribadi lain.</x-alert>
            @if ($consultation->status === 'accepted')
                <form method="POST" action="{{ route('consultations.close', $consultation) }}" onsubmit="return confirm('Tandai konsultasi ini selesai?')">@csrf<button type="submit" class="btn-secondary w-full">Tandai selesai</button></form>
            @endif
            <details class="rounded-3xl bg-white p-5 ring-1 ring-inset ring-hairline" @if ($errors->has('reason')) open @endif>
                <summary class="flex cursor-pointer items-center gap-2 text-sm font-bold text-danger-ink"><x-icon name="flag" class="size-4" />Laporkan percakapan ini</summary>
                <form method="POST" action="{{ route('consultations.report', $consultation) }}" class="mt-4 flex flex-col gap-3">
                    @csrf
                    <x-field label="Apa yang membuatmu tidak nyaman?" for="reason"><x-textarea id="reason" name="reason" rows="3" required maxlength="1000"></x-textarea></x-field>
                    <button type="submit" class="btn-danger">Kirim laporan</button>
                </form>
            </details>
        </aside>
    </div>
</x-app-layout>
