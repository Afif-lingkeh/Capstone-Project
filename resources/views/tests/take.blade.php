{{-- resources/views/tests/take.blade.php --}}
{{-- Desain "Question Card": satu soal per layar, setiap jawaban tersimpan otomatis.
     Tombol "Kumpulkan" juga mengirim semua jawaban, jadi tidak ada yang hilang bila simpan otomatis gagal. --}}
@php
    $total = $questions->count();
    $initial = $answers->map(fn ($a) => (string) ($a->question_option_id ?? $a->value))->all();
    $firstOpen = $questions->search(fn ($q) => ! $answers->has($q->id));
@endphp
<x-app-layout>
    <x-slot name="header">Tes Potensi</x-slot>
    <x-slot name="subtitle">{{ $attempt->test->title }}</x-slot>

    <form method="POST" action="{{ route('attempts.submit', $attempt) }}" class="mx-auto flex max-w-3xl flex-col gap-6"
          x-data="testRunner({ total: {{ $total }}, start: {{ $firstOpen === false ? 0 : $firstOpen }}, answers: @js((object) $initial), url: '{{ route('attempts.answer', $attempt) }}' })">
        @csrf

        <div class="flex items-center justify-between gap-4">
            <p class="text-[13px] font-bold text-slate"><span x-text="'Soal ' + (index + 1) + ' dari ' + total">{{ $total }} soal</span></p>
            <p class="flex items-center gap-1.5 text-xs font-semibold" aria-live="polite">
                <template x-if="status === 'saving'"><span class="text-slate">Menyimpan…</span></template>
                <template x-if="status === 'saved'"><span class="flex items-center gap-1.5 text-success"><x-icon name="check" class="size-3.5" /><span x-text="'Tersimpan ' + savedAt"></span></span></template>
                <template x-if="status === 'error'"><span class="text-danger-ink">Gagal menyimpan. Periksa koneksi, jawabanmu akan dikirim saat dikumpulkan.</span></template>
            </p>
        </div>
        <div class="h-1.5 w-full overflow-hidden rounded-full bg-pebble" role="progressbar" aria-label="Soal terjawab" :aria-valuenow="answered" aria-valuemin="0" aria-valuemax="{{ $total }}">
            <div class="h-full bg-blue motion-safe:transition-[width] motion-safe:duration-300" :style="`width: ${answered / total * 100}%`" style="width: {{ $total ? $answers->count() / $total * 100 : 0 }}%"></div>
        </div>

        @foreach ($questions as $i => $question)
            <fieldset x-show="index === {{ $i }}" @if ($i !== ($firstOpen === false ? 0 : $firstOpen)) x-cloak @endif
                      class="rounded-3xl bg-white p-7 ring-1 ring-inset ring-hairline">
                <legend class="float-left mb-5 w-full text-xl font-bold leading-7"><span class="sr-only">Soal {{ $i + 1 }}: </span>{{ $question->body }}</legend>
                <div class="clear-left flex flex-col gap-2.5">
                    @php($choices = $question->type === \App\Enums\QuestionType::Likert ? collect($likert)->map(fn ($l, $v) => ['value' => $v, 'label' => $l]) : $question->options->map(fn ($o) => ['value' => $o->id, 'label' => $o->label]))
                    @foreach ($choices as $choice)
                        <label class="flex min-h-[52px] cursor-pointer items-center gap-3 rounded-[14px] bg-white px-4 py-3 ring-[1.5px] ring-inset ring-hairline transition-colors duration-150 hover:bg-cloud has-[:checked]:bg-blue-wash has-[:checked]:ring-2 has-[:checked]:ring-blue has-[:focus-visible]:outline has-[:focus-visible]:outline-2 has-[:focus-visible]:outline-offset-2 has-[:focus-visible]:outline-blue">
                            <input type="radio" name="answers[{{ $question->id }}]" value="{{ $choice['value'] }}"
                                   @checked((string) ($initial[$question->id] ?? '') === (string) $choice['value'])
                                   @change="save({{ $question->id }}, $event.target.value)"
                                   class="size-5 border-[1.5px] border-mist text-blue focus:ring-0 focus:ring-offset-0">
                            <span class="text-[15px] font-semibold">{{ $choice['label'] }}</span>
                        </label>
                    @endforeach
                </div>
            </fieldset>
        @endforeach

        <div class="flex items-center justify-between gap-3">
            <button type="button" x-show="index > 0" x-cloak @click="index--" class="btn-ghost"><x-icon name="arrow-left" class="size-[18px]" />Sebelumnya</button>
            <span x-show="index === 0"></span>
            <button type="button" x-show="index < total - 1" x-cloak @click="index++" class="btn-secondary">Berikutnya <x-icon name="arrow-right" class="size-[18px]" /></button>
            <button type="submit" x-show="index === total - 1" class="btn-primary">Kumpulkan jawaban <x-icon name="check" class="size-[18px]" /></button>
        </div>
        <p class="text-center text-[13px] text-slate">Jawabanmu tersimpan otomatis. Kamu boleh berhenti dan melanjutkan kapan saja dari menu Tes Potensi.</p>
    </form>

    <script>
        function testRunner({ total, start, answers, url }) {
            return {
                total, index: start, answers, status: null, savedAt: '',
                get answered() { return Object.keys(this.answers).length },
                async save(questionId, value) {
                    this.answers[questionId] = value;
                    this.status = 'saving';
                    try {
                        const res = await fetch(url, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                            body: JSON.stringify({ question_id: questionId, answer: value }),
                        });
                        if (! res.ok) throw new Error();
                        this.savedAt = (await res.json()).saved_at;
                        this.status = 'saved';
                        // Pindah otomatis ke soal berikutnya setelah memilih.
                        if (this.index < this.total - 1) setTimeout(() => this.index++, 250);
                    } catch (e) {
                        this.status = 'error';
                    }
                },
            };
        }
    </script>
</x-app-layout>
