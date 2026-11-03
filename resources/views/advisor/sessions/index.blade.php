{{-- resources/views/advisor/sessions/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">Riwayat Sesi</x-slot>

    <x-page-intro title="Sesi bimbingan" description="Catat hasil setiap sesi agar santri dan pembimbing berikutnya bisa melanjutkan." />

    <div class="flex flex-col gap-6">
        @if ($toRecord->isNotEmpty())
            <x-card title="Perlu dicatat ({{ $toRecord->count() }})" :padded="false">
                <ul class="divide-y divide-hairline">
                    @foreach ($toRecord as $booking)
                        <li class="flex flex-col gap-4 px-6 py-5">
                            <div class="flex items-center gap-5">
                                <x-date-tile :date="$booking->slot->starts_at" />
                                <div class="flex flex-1 flex-col gap-1">
                                    <p class="text-base font-bold">{{ $booking->santri->name }} · {{ $booking->topic }}</p>
                                    <p class="text-[13px] font-medium text-slate">{{ $booking->slot->starts_at->format('H.i') }} WIB · {{ $booking->slot->mode->label() }}</p>
                                </div>
                            </div>
                            @if ($booking->message)<p class="rounded-2xl bg-cloud p-4 text-sm text-slate">“{{ $booking->message }}”</p>@endif
                            <form method="POST" action="{{ route('advisor.sessions.update', $booking) }}" class="grid gap-4 md:grid-cols-2">
                                @csrf @method('PATCH')
                                <fieldset class="md:col-span-2">
                                    <legend class="mb-2 text-sm font-bold">Kehadiran</legend>
                                    <div class="flex gap-3">
                                        <label class="flex items-center gap-2 text-sm font-semibold"><input type="radio" name="status" value="completed" checked class="size-5 border-mist text-blue focus:ring-focus">Hadir, sesi selesai</label>
                                        <label class="flex items-center gap-2 text-sm font-semibold"><input type="radio" name="status" value="no_show" class="size-5 border-mist text-blue focus:ring-focus">Tidak hadir</label>
                                    </div>
                                </fieldset>
                                <x-field label="Catatan hasil" :for="'notes-'.$booking->id" error="outcome_notes">
                                    <x-textarea :id="'notes-'.$booking->id" name="outcome_notes" rows="4"></x-textarea>
                                </x-field>
                                <x-field label="Langkah berikutnya" :for="'next-'.$booking->id" error="next_steps" optional>
                                    <x-textarea :id="'next-'.$booking->id" name="next_steps" rows="4"></x-textarea>
                                </x-field>
                                <div><x-primary-button>Simpan catatan</x-primary-button></div>
                            </form>
                        </li>
                    @endforeach
                </ul>
            </x-card>
        @endif

        <x-card title="Akan datang" :padded="false">
            <ul class="divide-y divide-hairline">
                @forelse ($upcoming as $booking)
                    <x-session-row :date="$booking->slot->starts_at" :title="$booking->santri->name.' · '.$booking->topic" :status="$booking->status"
                                   :meta="$booking->slot->starts_at->format('H.i').' WIB · '.$booking->slot->mode->label().($booking->santri->santriProfile ? ' · Kelas '.$booking->santri->santriProfile->class_name : '')" />
                @empty
                    <li class="px-6 py-8 text-center text-sm text-slate">Belum ada sesi yang dipesan.</li>
                @endforelse
            </ul>
        </x-card>

        <x-card title="Selesai" :padded="false">
            <ul class="divide-y divide-hairline">
                @forelse ($done as $booking)
                    <x-session-row :date="$booking->slot->starts_at" :title="$booking->santri->name.' · '.$booking->topic" :status="$booking->status" muted
                                   :meta="$booking->slot->starts_at->translatedFormat('j M Y')">
                        @if ($booking->outcome_notes)<p class="line-clamp-2 text-sm text-slate">{{ $booking->outcome_notes }}</p>@endif
                    </x-session-row>
                @empty
                    <li class="px-6 py-8 text-center text-sm text-slate">Belum ada sesi yang selesai.</li>
                @endforelse
            </ul>
        </x-card>
    </div>
</x-app-layout>
