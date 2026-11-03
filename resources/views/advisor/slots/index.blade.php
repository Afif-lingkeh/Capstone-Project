{{-- resources/views/advisor/slots/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">Slot Jadwal</x-slot>

    <x-page-intro title="Slot bimbingan" description="Buka waktu yang bisa dipesan santri. Slot yang bertabrakan dengan slot lain otomatis dilewati." />

    <div class="flex flex-col gap-6 xl:flex-row xl:items-start">
        <form method="POST" action="{{ route('advisor.slots.store') }}" class="flex flex-col gap-5 rounded-3xl bg-white p-6 ring-1 ring-inset ring-hairline xl:sticky xl:top-24 xl:w-[400px]">
            @csrf
            <h2 class="text-lg font-extrabold">Buka slot baru</h2>
            <div class="grid grid-cols-2 gap-4">
                <x-field label="Tanggal" for="date" class="col-span-2">
                    <x-text-input id="date" name="date" type="date" :value="old('date', now()->addDay()->toDateString())" :min="now()->toDateString()" required />
                </x-field>
                <x-field label="Mulai" for="start_time">
                    <x-text-input id="start_time" name="start_time" type="time" :value="old('start_time', '09:00')" required />
                </x-field>
                <x-field label="Durasi" for="duration">
                    <x-select-input id="duration" name="duration">
                        @foreach ([30, 45, 60, 90] as $minutes)
                            <option value="{{ $minutes }}" @selected((int) old('duration', 30) === $minutes)>{{ $minutes }} menit</option>
                        @endforeach
                    </x-select-input>
                </x-field>
                <x-field label="Jumlah slot berurutan" for="count">
                    <x-text-input id="count" name="count" type="number" min="1" max="8" :value="old('count', 4)" required />
                </x-field>
                <x-field label="Ulangi mingguan" for="repeat_weeks" hint="0 = tidak diulang">
                    <x-text-input id="repeat_weeks" name="repeat_weeks" type="number" min="0" max="8" :value="old('repeat_weeks', 0)" required />
                </x-field>
                <x-field label="Cara" for="mode" class="col-span-2">
                    <x-select-input id="mode" name="mode">
                        @foreach ($modes as $mode)
                            <option value="{{ $mode->value }}" @selected(old('mode') === $mode->value)>{{ $mode->label() }}</option>
                        @endforeach
                    </x-select-input>
                </x-field>
                <x-field label="Tempat atau tautan rapat" for="location" class="col-span-2" optional>
                    <x-text-input id="location" name="location" :value="old('location')" placeholder="Ruang BK atau https://meet…" />
                </x-field>
            </div>
            <x-primary-button class="w-full"><x-icon name="calendar-plus" class="size-[18px]" />Buka slot</x-primary-button>
        </form>

        <div class="flex flex-1 flex-col gap-6">
            @forelse ($slots as $date => $daySlots)
                <x-card :title="\Illuminate\Support\Carbon::parse($date)->translatedFormat('l, j F Y')" :padded="false">
                    <ul class="divide-y divide-hairline">
                        @foreach ($daySlots as $slot)
                            <li class="flex flex-wrap items-center gap-4 px-6 py-4">
                                <span class="w-28 text-[15px] font-bold">{{ $slot->starts_at->format('H.i') }}–{{ $slot->ends_at->format('H.i') }}</span>
                                <span class="flex flex-1 flex-col gap-0.5 text-sm">
                                    @if ($slot->activeBooking)
                                        <span class="font-bold">{{ $slot->activeBooking->santri->name }}</span>
                                        <span class="text-slate">{{ $slot->activeBooking->topic }}</span>
                                    @else
                                        <span class="text-slate">Belum dipesan · {{ $slot->mode->label() }}</span>
                                    @endif
                                </span>
                                @if ($slot->activeBooking)
                                    <x-badge tone="blue">Dipesan</x-badge>
                                @else
                                    <form method="POST" action="{{ route('advisor.slots.destroy', $slot) }}">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="flex size-10 items-center justify-center rounded-xl text-danger-ink hover:bg-danger-wash"><x-icon name="trash-2" class="size-[18px]" /><span class="sr-only">Hapus slot {{ $slot->starts_at->format('H.i') }}</span></button>
                                    </form>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </x-card>
            @empty
                <x-empty-state icon="calendar-plus" title="Belum ada slot yang akan datang">Buka slot lewat formulir di samping.</x-empty-state>
            @endforelse
        </div>
    </div>
</x-app-layout>
