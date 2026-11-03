{{-- resources/views/admin/shifts/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">Piket Konselor</x-slot>

    <x-page-intro title="Jadwal piket konselor" description="Konselor yang sedang piket menerima peringatan krisis lebih dulu. Tanpa piket, semua konselor diberi tahu." />

    <div class="flex flex-col gap-6 xl:flex-row xl:items-start">
        <form method="POST" action="{{ route('admin.shifts.store') }}" class="flex flex-col gap-5 rounded-3xl bg-white p-6 ring-1 ring-inset ring-hairline xl:sticky xl:top-24 xl:w-[380px]">
            @csrf
            <h2 class="text-lg font-extrabold">Tambah piket</h2>
            <x-field label="Konselor" for="counselor_id">
                <x-select-input id="counselor_id" name="counselor_id" required>
                    <option value="">Pilih konselor</option>
                    @foreach ($counselors as $counselor)<option value="{{ $counselor->id }}" @selected((string) old('counselor_id') === (string) $counselor->id)>{{ $counselor->name }}</option>@endforeach
                </x-select-input>
            </x-field>
            <x-field label="Mulai tanggal" for="date"><x-text-input id="date" name="date" type="date" :value="old('date', now()->toDateString())" required /></x-field>
            <div class="grid grid-cols-2 gap-4">
                <x-field label="Jam mulai" for="start_time"><x-text-input id="start_time" name="start_time" type="time" :value="old('start_time', '07:00')" required /></x-field>
                <x-field label="Jam selesai" for="end_time"><x-text-input id="end_time" name="end_time" type="time" :value="old('end_time', '21:00')" required /></x-field>
            </div>
            <x-field label="Ulangi berapa hari berikutnya" for="repeat_days" hint="0 = hanya tanggal itu"><x-text-input id="repeat_days" name="repeat_days" type="number" min="0" max="30" :value="old('repeat_days', 0)" required /></x-field>
            <x-primary-button>Simpan piket</x-primary-button>
        </form>

        <div class="flex flex-1 flex-col gap-6">
            <div class="flex flex-col gap-2 rounded-3xl bg-ink p-6 text-white">
                <p class="text-xs font-bold uppercase tracking-[0.13em] text-teal-light">Sedang piket sekarang</p>
                <p class="text-base font-bold">{{ $onDuty->isNotEmpty() ? $onDuty->pluck('counselor.name')->join(', ') : 'Tidak ada — peringatan dikirim ke semua konselor.' }}</p>
            </div>
            @forelse ($shifts as $date => $dayShifts)
                <x-card :title="\Illuminate\Support\Carbon::parse($date)->translatedFormat('l, j F Y')" :padded="false">
                    <ul class="divide-y divide-hairline">
                        @foreach ($dayShifts as $shift)
                            <li class="flex items-center gap-4 px-6 py-3">
                                <span class="w-32 text-sm font-bold">{{ $shift->starts_at->format('H.i') }}–{{ $shift->ends_at->format('H.i') }}</span>
                                <span class="flex-1 text-sm">{{ $shift->counselor->name }}</span>
                                <form method="POST" action="{{ route('admin.shifts.destroy', $shift) }}">@csrf @method('DELETE')
                                    <button type="submit" class="flex size-9 items-center justify-center rounded-lg text-danger-ink hover:bg-danger-wash"><x-icon name="trash-2" class="size-4" /><span class="sr-only">Hapus piket {{ $shift->counselor->name }}</span></button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                </x-card>
            @empty
                <x-empty-state icon="calendar-clock" title="Belum ada jadwal piket" />
            @endforelse
        </div>
    </div>
</x-app-layout>
