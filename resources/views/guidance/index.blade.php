{{-- resources/views/guidance/index.blade.php --}}
@php($dates = $slotsByDate->keys())
<x-app-layout>
    <x-slot name="header">Bimbingan Kuliah</x-slot>

    <x-page-intro eyebrow="Bimbingan kuliah" title="Bermusyawarah dengan pembimbing"
                  description="Pilih waktu yang kosong, lalu ceritakan apa yang ingin kamu diskusikan. Pembimbingmu sendiri ditandai lebih dulu." />

    @if ($upcoming->isNotEmpty())
        <x-card title="Jadwal yang akan datang" :padded="false" class="mb-6">
            <ul class="divide-y divide-hairline">
                @foreach ($upcoming as $booking)
                    <x-session-row :date="$booking->slot->starts_at" :title="$booking->topic" :status="$booking->status"
                                   :meta="$booking->slot->starts_at->format('H.i').'–'.$booking->slot->ends_at->format('H.i').' WIB · '.$booking->slot->mode->label().' · '.$booking->slot->advisor->name">
                        @if ($booking->slot->location)
                            <p class="break-all text-[13px] font-semibold text-ink">{{ $booking->slot->mode === \App\Enums\SessionMode::Online ? 'Tautan' : 'Tempat' }}: {{ $booking->slot->location }}</p>
                        @endif
                        <x-slot name="actions">
                            <form method="POST" action="{{ route('guidance.cancel', $booking) }}" onsubmit="return confirm('Batalkan jadwal ini?')">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn-ghost px-4 py-2.5 text-sm text-danger-ink">Batalkan</button>
                            </form>
                        </x-slot>
                    </x-session-row>
                @endforeach
            </ul>
        </x-card>
    @endif

    {{-- Desain "Booking Widget": pilih tanggal → waktu → isi topik. Tanpa JavaScript, semua waktu tetap tampil sebagai pilihan. --}}
    <section aria-labelledby="pesan-jadwal" class="mb-6 overflow-hidden rounded-3xl bg-white shadow-tile ring-1 ring-inset ring-hairline">
        <h2 id="pesan-jadwal" class="border-b border-hairline px-6 py-5 text-lg font-extrabold">Pesan jadwal baru</h2>

        @if ($dates->isEmpty())
            <div class="p-6"><x-empty-state icon="calendar" title="Belum ada waktu kosong" class="border-0">Pembimbing belum membuka slot untuk 4 minggu ke depan. Coba cek lagi besok, ya.</x-empty-state></div>
        @elseif (! $canBook)
            <div class="p-6"><x-alert variant="warning" title="Jadwalmu sudah penuh">Kamu sudah punya {{ \App\Http\Controllers\Santri\GuidanceController::MAX_UPCOMING }} jadwal yang akan datang. Selesaikan atau batalkan salah satunya untuk memesan lagi.</x-alert></div>
        @else
            <form method="POST" action="{{ route('guidance.book') }}" x-data="{ day: '{{ $dates->first() }}', slot: '{{ old('slot_id') }}' }" class="flex flex-col lg:flex-row">
                @csrf
                <div class="flex flex-1 flex-col gap-5 p-6 lg:border-r lg:border-hairline">
                    <fieldset>
                        <legend class="mb-3 text-sm font-bold">1. Pilih tanggal</legend>
                        <div class="-mx-6 flex gap-2.5 overflow-x-auto px-6 pb-1">
                            @foreach ($slotsByDate as $date => $slots)
                                @php($d = \Illuminate\Support\Carbon::parse($date))
                                <button type="button" @click="day = '{{ $date }}'" :aria-pressed="(day === '{{ $date }}').toString()"
                                        :class="day === '{{ $date }}' ? 'bg-ink text-white ring-ink' : 'bg-white text-ink ring-hairline hover:bg-pebble'"
                                        class="flex w-[72px] shrink-0 flex-col items-center gap-0.5 rounded-2xl py-3 ring-[1.5px] ring-inset">
                                    <span class="text-[11px] font-bold uppercase tracking-[0.08em] opacity-80">{{ Str::substr($d->translatedFormat('D'), 0, 3) }}</span>
                                    <span class="text-xl font-extrabold">{{ $d->format('d') }}</span>
                                    <span class="text-[11px] font-semibold opacity-80">{{ $d->translatedFormat('M') }}</span>
                                </button>
                            @endforeach
                        </div>
                    </fieldset>

                    <fieldset>
                        <legend class="mb-3 text-sm font-bold">2. Pilih waktu</legend>
                        <x-input-error :messages="$errors->get('slot_id')" class="mb-3" />
                        @foreach ($slotsByDate as $date => $slots)
                            <div x-show="day === '{{ $date }}'" @if (! $loop->first) x-cloak @endif class="grid gap-2.5 sm:grid-cols-2">
                                <p class="sr-only">{{ \Illuminate\Support\Carbon::parse($date)->translatedFormat('l, j F Y') }}</p>
                                @foreach ($slots as $slot)
                                    <label class="flex cursor-pointer items-center gap-3 rounded-xl px-4 py-3 ring-[1.5px] ring-inset ring-blue transition-colors duration-150 has-[:checked]:bg-blue has-[:checked]:text-white has-[:focus-visible]:outline has-[:focus-visible]:outline-2 has-[:focus-visible]:outline-offset-2 has-[:focus-visible]:outline-blue">
                                        <input type="radio" name="slot_id" value="{{ $slot->id }}" x-model="slot" class="sr-only" required>
                                        <span class="flex flex-1 flex-col">
                                            <span class="text-[15px] font-bold">{{ $slot->starts_at->format('H.i') }}–{{ $slot->ends_at->format('H.i') }}</span>
                                            <span class="text-xs font-semibold opacity-80">{{ $slot->advisor->name }} · {{ $slot->mode->label() }}</span>
                                        </span>
                                        @if ($myAdvisorIds->contains($slot->advisor_id))<span class="rounded-md bg-teal-wash px-2 py-0.5 text-[11px] font-bold text-teal-ink">Pembimbingmu</span>@endif
                                    </label>
                                @endforeach
                            </div>
                        @endforeach
                        <p class="mt-3 flex items-center gap-2 text-xs font-semibold text-slate"><x-icon name="globe" class="size-4" />Waktu Indonesia Barat (WIB)</p>
                    </fieldset>
                </div>

                <div class="flex flex-col gap-5 p-6 lg:w-[380px]">
                    <p class="text-sm font-bold">3. Ceritakan topiknya</p>
                    <x-field label="Topik" for="topic">
                        <x-text-input id="topic" name="topic" list="topik-umum" :value="old('topic')" placeholder="mis. Persiapan SNBP" required maxlength="150" />
                        <datalist id="topik-umum">
                            @foreach (['Persiapan SNBP', 'Persiapan SNBT', 'Memilih jurusan', 'Kuliah di luar negeri', 'Beasiswa', 'Membahas hasil tes potensi'] as $topic)
                                <option value="{{ $topic }}"></option>
                            @endforeach
                        </datalist>
                    </x-field>
                    <x-field label="Pesan untuk pembimbing" for="message" optional>
                        <x-textarea id="message" name="message" rows="4" maxlength="1000">{{ old('message') }}</x-textarea>
                    </x-field>
                    <x-primary-button class="w-full">Pesan jadwal <x-icon name="arrow-right" class="size-[18px]" /></x-primary-button>
                </div>
            </form>
        @endif
    </section>

    @if ($history->isNotEmpty())
        <x-card title="Riwayat bimbingan" :padded="false">
            <ul class="divide-y divide-hairline">
                @foreach ($history as $booking)
                    <x-session-row :date="$booking->slot->starts_at" :title="$booking->topic" :status="$booking->status" muted
                                   :meta="$booking->slot->starts_at->translatedFormat('M Y').' · '.$booking->slot->mode->label().' · '.$booking->slot->advisor->name">
                        @if ($booking->outcome_notes)
                            <details class="mt-2 rounded-2xl bg-cloud p-4">
                                <summary class="cursor-pointer text-sm font-bold text-blue">Catatan dari pembimbing</summary>
                                <p class="mt-2 whitespace-pre-line text-sm leading-[1.6] text-slate">{{ $booking->outcome_notes }}</p>
                                @if ($booking->next_steps)
                                    <p class="mt-3 text-sm font-bold">Langkah berikutnya</p>
                                    <p class="whitespace-pre-line text-sm leading-[1.6] text-slate">{{ $booking->next_steps }}</p>
                                @endif
                            </details>
                        @endif
                    </x-session-row>
                @endforeach
            </ul>
        </x-card>
    @endif
</x-app-layout>
