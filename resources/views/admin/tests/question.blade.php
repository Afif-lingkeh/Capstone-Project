{{-- resources/views/admin/tests/question.blade.php --}}
@php
    $editing = $question->exists;
    $type = old('type', $question->type->value);
    $options = old('options', $question->options->map(fn ($o) => ['label' => $o->label, 'weights' => $o->weights])->all() ?: [['label' => '', 'weights' => []], ['label' => '', 'weights' => []]]);
@endphp
<x-app-layout>
    <x-slot name="header">{{ $editing ? 'Ubah Soal' : 'Tambah Soal' }}</x-slot>
    <x-slot name="subtitle">{{ $test->title }}</x-slot>

    <form method="POST" action="{{ $editing ? route('admin.questions.update', [$test, $question]) : route('admin.questions.store', $test) }}"
          class="flex max-w-4xl flex-col gap-6" x-data="{ type: '{{ $type }}', options: @js(array_values($options)) }">
        @csrf
        @if ($editing) @method('PUT') @endif

        <x-card>
            <div class="flex flex-col gap-5">
                <fieldset>
                    <legend class="mb-2 text-sm font-bold">Jenis soal</legend>
                    <div class="flex flex-wrap gap-3">
                        @foreach (\App\Enums\QuestionType::cases() as $qt)
                            <label class="flex items-center gap-2 rounded-xl px-4 py-3 text-sm font-semibold ring-[1.5px] ring-inset ring-hairline has-[:checked]:bg-blue-wash has-[:checked]:ring-blue">
                                <input type="radio" name="type" value="{{ $qt->value }}" x-model="type" @checked($type === $qt->value) class="size-5 border-mist text-blue focus:ring-focus">{{ $qt->label() }}
                            </label>
                        @endforeach
                    </div>
                </fieldset>
                <x-field label="Pertanyaan atau pernyataan" for="body">
                    <x-textarea id="body" name="body" rows="3" required>{{ old('body', $question->body) }}</x-textarea>
                </x-field>
                <x-field label="Jalur yang diukur" for="pathway" x-show="type === 'likert'" hint="Jawaban “Sangat setuju” = skor penuh untuk jalur ini.">
                    <x-select-input id="pathway" name="pathway">
                        <option value="">Pilih jalur</option>
                        @foreach ($pathways as $pathway)
                            <option value="{{ $pathway->value }}" @selected(old('pathway', $question->pathway?->value) === $pathway->value)>{{ $pathway->label() }}</option>
                        @endforeach
                    </x-select-input>
                </x-field>
            </div>
        </x-card>

        <x-card title="Opsi jawaban & bobot jalur" x-show="type === 'choice'" x-cloak>
            <p class="mb-4 text-sm text-slate">Bobot 0–5 untuk setiap jalur. Opsi yang paling mencerminkan suatu jalur diberi bobot tertinggi di jalur itu.</p>
            <x-input-error :messages="$errors->get('options')" class="mb-3" />
            <div class="flex flex-col gap-4">
                <template x-for="(option, i) in options" :key="i">
                    <div class="flex flex-col gap-3 rounded-2xl bg-cloud p-4">
                        <div class="flex items-end gap-3">
                            <div class="flex flex-1 flex-col gap-2">
                                <label :for="'opt-' + i" class="text-sm font-bold" x-text="'Opsi ' + (i + 1)"></label>
                                <input :id="'opt-' + i" type="text" :name="`options[${i}][label]`" x-model="option.label" class="field" :required="type === 'choice'">
                            </div>
                            <button type="button" @click="options.splice(i, 1)" x-show="options.length > 2" class="flex size-12 items-center justify-center rounded-2xl text-danger-ink hover:bg-danger-wash">
                                <x-icon name="trash-2" class="size-[18px]" /><span class="sr-only">Hapus opsi</span>
                            </button>
                        </div>
                        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                            @foreach ($pathways as $pathway)
                                <label class="flex flex-col gap-1 text-xs font-bold text-slate">
                                    {{ $pathway->label() }}
                                    <input type="number" min="0" max="5" :name="`options[${i}][weights][{{ $pathway->value }}]`" x-model="option.weights['{{ $pathway->value }}']" class="field h-10 text-sm">
                                </label>
                            @endforeach
                        </div>
                    </div>
                </template>
                <button type="button" @click="options.push({ label: '', weights: {} })" x-show="options.length < 6" class="btn-ghost w-fit text-blue"><x-icon name="plus" class="size-[18px]" />Tambah opsi</button>
            </div>
        </x-card>

        <div class="flex flex-wrap items-center gap-3">
            <x-primary-button>Simpan soal</x-primary-button>
            <a href="{{ route('admin.tests.edit', $test) }}" class="btn-ghost">Batal</a>
        </div>
    </form>
</x-app-layout>
