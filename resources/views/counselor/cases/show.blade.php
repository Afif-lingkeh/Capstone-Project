{{-- resources/views/counselor/cases/show.blade.php --}}
@php($santri = $case->santri)
<x-app-layout>
    <x-slot name="header">Kasus {{ $case->code }}</x-slot>
    <x-slot name="subtitle">{{ $santri->name }} · dibuka {{ $case->created_at->translatedFormat('j F Y') }}</x-slot>

    <a href="{{ route('counselor.cases.index') }}" class="mb-5 flex w-fit items-center gap-1.5 py-1 text-sm font-bold text-blue hover:underline"><x-icon name="arrow-left" class="size-4" /> Kasus saya</a>

    <x-audit-notice :at="$openedAt" class="mb-6" />

    <div class="flex flex-col gap-6 xl:flex-row xl:items-start">
        <div class="flex flex-1 flex-col gap-6">
            @if ($case->request)
                <x-card title="Permintaan awal">
                    <p class="mb-2 text-xs font-bold uppercase tracking-[0.11em] text-slate">{{ $case->request->topic->label() }} · {{ $case->request->created_at->translatedFormat('j M Y') }}</p>
                    <p class="whitespace-pre-line text-[15px] leading-[1.7]">{{ $case->request->story }}</p>
                </x-card>
            @endif

            @forelse ($case->sessions as $session)
                <article class="flex flex-col gap-[18px] rounded-3xl bg-white p-7 ring-1 ring-inset ring-hairline">
                    <header class="flex flex-wrap items-start justify-between gap-3">
                        <div class="flex flex-col gap-0.5">
                            <h2 class="text-lg font-extrabold">Catatan sesi #{{ $loop->iteration }}</h2>
                            <p class="text-[13px] font-medium text-slate">{{ $session->held_at->translatedFormat('j M Y, H.i') }} · {{ $session->mode->label() }} · {{ $session->counselor?->name ?? 'Konselor sebelumnya' }}</p>
                        </div>
                        <x-badge :tone="$session->urgency->tone()">{{ $session->urgency->label() }}</x-badge>
                    </header>
                    <dl class="flex flex-col gap-4">
                        @foreach (['chief_complaint' => 'Keluhan utama', 'assessment' => 'Asesmen', 'intervention' => 'Intervensi', 'follow_up_plan' => 'Rencana tindak lanjut'] as $field => $label)
                            <div class="flex flex-col gap-1">
                                <dt class="text-[11px] font-bold uppercase tracking-[0.11em] text-slate">{{ $label }}</dt>
                                <dd class="whitespace-pre-line text-[15px] font-medium leading-[1.6]">{{ $session->$field }}</dd>
                            </div>
                        @endforeach
                        @if ($session->next_session_at)
                            <div class="flex flex-col gap-1"><dt class="text-[11px] font-bold uppercase tracking-[0.11em] text-slate">Sesi berikutnya</dt><dd class="text-[15px] font-medium">{{ $session->next_session_at->translatedFormat('l, j F Y H.i') }} WIB</dd></div>
                        @endif
                    </dl>
                </article>
            @empty
                <x-empty-state icon="file-text" title="Belum ada catatan sesi">Tulis catatan setelah sesi pertama.</x-empty-state>
            @endforelse

            @if ($case->isOpen())
                <form method="POST" action="{{ route('counselor.sessions.store', $case) }}" class="flex flex-col gap-5 rounded-3xl bg-white p-7 ring-1 ring-inset ring-hairline">
                    @csrf
                    <h2 class="text-lg font-extrabold">Tambah catatan sesi</h2>
                    <div class="grid gap-5 sm:grid-cols-3">
                        <x-field label="Waktu sesi" for="held_at"><x-text-input id="held_at" name="held_at" type="datetime-local" :value="old('held_at', now()->format('Y-m-d\TH:i'))" required /></x-field>
                        <x-field label="Cara" for="mode">
                            <x-select-input id="mode" name="mode">@foreach ($modes as $mode)<option value="{{ $mode->value }}" @selected(old('mode') === $mode->value)>{{ $mode->label() }}</option>@endforeach</x-select-input>
                        </x-field>
                        <x-field label="Tingkat urgensi" for="urgency">
                            <x-select-input id="urgency" name="urgency">@foreach ($urgencies as $u)<option value="{{ $u->value }}" @selected(old('urgency', $case->urgency->value) === $u->value)>{{ $u->label() }}</option>@endforeach</x-select-input>
                        </x-field>
                    </div>
                    @foreach (['chief_complaint' => 'Keluhan utama', 'assessment' => 'Asesmen', 'intervention' => 'Intervensi', 'follow_up_plan' => 'Rencana tindak lanjut'] as $field => $label)
                        <x-field :label="$label" :for="$field"><x-textarea :id="$field" :name="$field" rows="3" required>{{ old($field) }}</x-textarea></x-field>
                    @endforeach
                    <x-field label="Jadwal sesi berikutnya" for="next_session_at" optional hint="Santri diberi tahu waktunya saja, tanpa isi catatan.">
                        <x-text-input id="next_session_at" name="next_session_at" type="datetime-local" :value="old('next_session_at')" />
                    </x-field>
                    <x-primary-button class="w-fit">Simpan catatan (terenkripsi)</x-primary-button>
                </form>
            @endif
        </div>

        <aside class="flex flex-col gap-4 xl:sticky xl:top-24 xl:w-[360px]">
            <div class="flex flex-col gap-3 rounded-3xl bg-white p-6 ring-1 ring-inset ring-hairline">
                <div class="flex items-center justify-between"><p class="text-sm font-bold">Status</p><x-status-badge :status="$case->status" /></div>
                <p class="text-sm text-slate">{{ $santri->gender?->label() }}@if ($santri->santriProfile) · {{ $santri->santriProfile->level->label() }} Kelas {{ $santri->santriProfile->class_name }}@endif</p>
                @if (! $case->isOpen() && $case->closing_summary)
                    <p class="whitespace-pre-line rounded-2xl bg-cloud p-4 text-sm text-slate">{{ $case->closing_summary }}</p>
                @endif
            </div>

            @if ($case->isOpen())
                <div class="flex flex-col gap-4 rounded-3xl bg-white p-6 ring-1 ring-inset ring-hairline">
                    <h2 class="text-base font-extrabold">Alih tangan kasus</h2>
                    @if ($case->pendingHandover)
                        <x-alert variant="warning" title="Menunggu jawaban">Diajukan ke {{ $case->pendingHandover->to->name }}. Kasus tetap Anda pegang sampai diterima.</x-alert>
                    @elseif ($counselors->isEmpty())
                        <p class="text-sm text-slate">Belum ada konselor lain yang aktif.</p>
                    @else
                        <form method="POST" action="{{ route('counselor.cases.handover', $case) }}" class="flex flex-col gap-4">
                            @csrf
                            <x-field label="Kepada" for="to_counselor_id">
                                <x-select-input id="to_counselor_id" name="to_counselor_id" required>
                                    <option value="">Pilih konselor</option>
                                    @foreach ($counselors as $counselor)<option value="{{ $counselor->id }}">{{ $counselor->name }}{{ $counselor->gender === $santri->gender ? ' · gender sama' : '' }}</option>@endforeach
                                </x-select-input>
                            </x-field>
                            <x-field label="Alasan" for="reason"><x-textarea id="reason" name="reason" rows="3" required></x-textarea></x-field>
                            <p class="text-xs text-slate">Setelah diterima, Anda tidak bisa membaca kasus ini lagi.</p>
                            <button type="submit" class="btn-secondary">Ajukan alih tangan</button>
                        </form>
                    @endif
                </div>

                <form method="POST" action="{{ route('counselor.cases.close', $case) }}" class="flex flex-col gap-4 rounded-3xl bg-white p-6 ring-1 ring-inset ring-hairline" onsubmit="return confirm('Tutup kasus ini?')">
                    @csrf
                    <h2 class="text-base font-extrabold">Tutup kasus</h2>
                    <x-field label="Ringkasan penutupan" for="closing_summary"><x-textarea id="closing_summary" name="closing_summary" rows="3" required></x-textarea></x-field>
                    <button type="submit" class="btn-ghost text-danger-ink ring-1 ring-inset ring-danger-line">Tutup kasus</button>
                </form>
            @endif
        </aside>
    </div>
</x-app-layout>
