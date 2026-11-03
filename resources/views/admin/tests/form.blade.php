{{-- resources/views/admin/tests/form.blade.php --}}
@php($editing = $test->exists)
<x-app-layout>
    <x-slot name="header">{{ $editing ? 'Ubah Tes' : 'Buat Tes' }}</x-slot>

    <a href="{{ route('admin.tests.index') }}" class="mb-5 flex w-fit items-center gap-1.5 py-1 text-sm font-bold text-blue hover:underline"><x-icon name="arrow-left" class="size-4" /> Bank soal</a>

    <div class="flex flex-col gap-6 xl:flex-row xl:items-start">
        <form method="POST" action="{{ $editing ? route('admin.tests.update', $test) : route('admin.tests.store') }}" class="flex flex-col gap-5 rounded-3xl bg-white p-6 ring-1 ring-inset ring-hairline xl:sticky xl:top-24 xl:w-[400px]">
            @csrf
            @if ($editing) @method('PUT') @endif
            <h2 class="text-lg font-extrabold">Data tes</h2>
            <x-field label="Judul" for="title"><x-text-input id="title" name="title" :value="old('title', $test->title)" required /></x-field>
            <x-field label="Dimensi" for="dimension">
                <x-select-input id="dimension" name="dimension">
                    @foreach ($dimensions as $dimension)
                        <option value="{{ $dimension->value }}" @selected(old('dimension', $test->dimension?->value) === $dimension->value)>{{ $dimension->label() }}</option>
                    @endforeach
                </x-select-input>
            </x-field>
            <x-field label="Perkiraan waktu (menit)" for="estimated_minutes"><x-text-input id="estimated_minutes" name="estimated_minutes" type="number" min="1" max="180" :value="old('estimated_minutes', $test->estimated_minutes)" required /></x-field>
            <x-field label="Deskripsi untuk santri" for="description" optional><x-textarea id="description" name="description" rows="3">{{ old('description', $test->description) }}</x-textarea></x-field>
            <label class="flex items-center gap-3">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $test->is_active)) class="size-[22px] rounded-[7px] border-[1.5px] border-mist text-blue focus:ring-focus">
                <span class="text-[15px] font-bold">Aktifkan (tampil ke santri)</span>
            </label>
            <x-primary-button class="w-full">{{ $editing ? 'Simpan perubahan' : 'Buat tes' }}</x-primary-button>
        </form>

        @if ($editing)
            <div class="flex flex-1 flex-col gap-4">
                <x-card :title="'Soal ('.$test->questions->count().')'" :padded="false">
                    <x-slot name="actions">
                        <a href="{{ route('admin.questions.create', [$test, 'jenis' => 'likert']) }}" class="btn-secondary px-4 py-2.5 text-sm"><x-icon name="plus" class="size-4" />Pernyataan Likert</a>
                        <a href="{{ route('admin.questions.create', [$test, 'jenis' => 'choice']) }}" class="btn-secondary px-4 py-2.5 text-sm"><x-icon name="plus" class="size-4" />Pilihan ganda</a>
                    </x-slot>
                    <ol class="divide-y divide-hairline">
                        @forelse ($test->questions as $question)
                            <li class="flex items-start gap-4 px-6 py-4">
                                <span class="w-7 shrink-0 pt-0.5 text-sm font-extrabold text-blue">{{ $loop->iteration }}</span>
                                <div class="flex flex-1 flex-col gap-1.5">
                                    <p class="text-[15px] font-semibold">{{ $question->body }}</p>
                                    <p class="text-xs font-medium text-slate">
                                        {{ $question->type->label() }}
                                        @if ($question->pathway) · mengukur {{ $question->pathway->label() }}@endif
                                        @if ($question->options->isNotEmpty()) · {{ $question->options->count() }} opsi @endif
                                    </p>
                                </div>
                                <a href="{{ route('admin.questions.edit', [$test, $question]) }}" class="text-sm font-bold text-blue hover:underline">Ubah<span class="sr-only"> soal {{ $loop->iteration }}</span></a>
                                <form method="POST" action="{{ route('admin.questions.destroy', [$test, $question]) }}" onsubmit="return confirm('Hapus soal ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="flex size-9 items-center justify-center rounded-lg text-danger-ink hover:bg-danger-wash"><x-icon name="trash-2" class="size-4" /><span class="sr-only">Hapus soal {{ $loop->iteration }}</span></button>
                                </form>
                            </li>
                        @empty
                            <li class="px-6 py-8 text-center text-sm text-slate">Belum ada soal.</li>
                        @endforelse
                    </ol>
                </x-card>

                <form method="POST" action="{{ route('admin.tests.destroy', $test) }}" onsubmit="return confirm('Hapus tes ini beserta semua soalnya?')">
                    @csrf @method('DELETE')
                    <x-danger-button>Hapus tes</x-danger-button>
                </form>
            </div>
        @endif
    </div>
</x-app-layout>
