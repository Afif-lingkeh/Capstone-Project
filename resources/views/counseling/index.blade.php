{{-- resources/views/counseling/index.blade.php --}}
@php
    use App\Enums\CounselingStatus;
    $active = $requests->first(fn ($r) => in_array($r->status, [CounselingStatus::Pending, CounselingStatus::Accepted], true) && (! $r->case || $r->case->isOpen()));
    $stepsFor = function ($r) {
        $case = $r->case;
        $hasSessions = $case && $case->sessions->isNotEmpty();
        $next = $case?->sessions->sortByDesc('held_at')->first()?->next_session_at;
        $closed = $case && ! $case->isOpen();
        return [
            ['label' => 'Diajukan', 'date' => $r->created_at->translatedFormat('j M, H.i'), 'state' => 'done'],
            ['label' => 'Diterima konselor', 'date' => $r->accepted_at?->translatedFormat('j M, H.i'), 'state' => $r->accepted_at ? 'done' : 'current'],
            ['label' => 'Sesi dijadwalkan', 'date' => $next?->translatedFormat('j M, H.i') ?? $case?->sessions->first()?->held_at->translatedFormat('j M, H.i'),
             'state' => $hasSessions ? 'done' : ($r->accepted_at ? 'current' : 'todo')],
            ['label' => 'Selesai', 'date' => $case?->closed_at?->translatedFormat('j M, H.i'), 'state' => $closed ? 'done' : ($hasSessions ? 'current' : 'todo')],
        ];
    };
@endphp
<x-app-layout>
    <x-slot name="header">Konseling</x-slot>

    <x-page-intro eyebrow="Teman bercerita" title="Ruang aman untuk bercerita"
                  description="Ceritamu hanya dibaca konselor yang mendampingimu. Admin pun tidak bisa membukanya.">
        @if ($hasConsent && ! $active)
            <x-slot name="actions"><a href="{{ route('counseling.create') }}" class="btn-primary"><x-icon name="heart-handshake" class="size-[18px]" />Mulai bercerita</a></x-slot>
        @endif
    </x-page-intro>

    @if (session('crisis') || $active?->is_crisis)
        <x-crisis-banner :on-duty="$onDuty" class="mb-6" />
    @endif

    @unless ($hasConsent)
        <div class="flex flex-col gap-4 rounded-3xl bg-sand p-7 ring-1 ring-inset ring-gold/25">
            <span class="flex size-12 items-center justify-center rounded-2xl bg-white"><x-icon name="lock" class="size-6 text-gold-ink" /></span>
            <h2 class="text-xl font-extrabold">Konseling belum dibuka</h2>
            <p class="max-w-2xl text-[15px] leading-6 text-slate">Fitur ini menunggu izin dari orang tua atau wali. Minta mereka menghubungi admin pesantren, ya. Sambil menunggu, kamu tetap boleh menemui ustadz/ustadzah atau musyrif/musyrifah kapan saja.</p>
        </div>
    @else
        @if ($active)
            <section aria-labelledby="status-aktif" class="mb-6 flex flex-col gap-4">
                <h2 id="status-aktif" class="text-lg font-extrabold">Status permintaanmu</h2>
                <x-stepper :steps="$stepsFor($active)" />
                <div class="flex flex-col gap-3 rounded-3xl bg-white p-6 ring-1 ring-inset ring-hairline sm:flex-row sm:items-center">
                    <div class="flex flex-1 flex-col gap-1">
                        <p class="text-sm font-bold">{{ $active->topic->label() }}</p>
                        <p class="text-sm text-slate">
                            @if ($active->case?->counselor)
                                Didampingi {{ $active->case->counselor->name }}. Konselor akan memberi tahu jadwal sesi lewat notifikasi.
                            @else
                                Menunggu konselor. Biasanya diterima dalam 1×24 jam pada hari sekolah.
                            @endif
                        </p>
                    </div>
                    @if ($active->status === CounselingStatus::Pending)
                        <form method="POST" action="{{ route('counseling.cancel', $active) }}" onsubmit="return confirm('Batalkan permintaan ini?')">
                            @csrf @method('PATCH')
                            <button type="submit" class="btn-ghost text-danger-ink">Batalkan permintaan</button>
                        </form>
                    @endif
                </div>
            </section>
        @elseif ($requests->isEmpty())
            <x-empty-state icon="heart-handshake" title="Belum ada permintaan">
                Saat hati sedang berat, ada konselor yang siap mendengar.
                <x-slot name="action"><a href="{{ route('counseling.create') }}" class="btn-primary">Mulai bercerita</a></x-slot>
            </x-empty-state>
        @endif

        @if ($requests->count() > ($active ? 1 : 0))
            <x-card title="Riwayat" :padded="false">
                <ul class="divide-y divide-hairline">
                    @foreach ($requests->reject(fn ($r) => $r->is($active)) as $item)
                        <li class="flex items-center gap-4 px-6 py-4">
                            <span class="flex flex-1 flex-col gap-0.5">
                                <span class="text-sm font-bold">{{ $item->topic->label() }}</span>
                                <span class="text-xs text-slate">{{ $item->created_at->translatedFormat('j F Y') }}</span>
                            </span>
                            <x-status-badge :status="$item->case && ! $item->case->isOpen() ? CounselingStatus::Closed : $item->status" />
                        </li>
                    @endforeach
                </ul>
            </x-card>
        @endif
    @endunless
</x-app-layout>
