{{-- resources/views/admin/chat-logs/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">Evaluasi Asisten AI</x-slot>

    <x-page-intro title="Evaluasi jawaban Asisten AI" description="Percakapan yang bertanda krisis disembunyikan dari halaman ini. Identitas santri tidak ditampilkan." />

    <div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
        <x-stat-card label="Jawaban" :value="$stats['total']" icon="sparkles" />
        <x-stat-card label="Dinilai membantu" :value="$stats['good']" icon="thumbs-up" />
        <x-stat-card label="Dinilai tidak membantu" :value="$stats['bad']" icon="thumbs-down" :tone="$stats['bad'] ? 'warning' : 'default'" />
        <x-stat-card label="Total token" :value="number_format($stats['tokens'], 0, ',', '.')" icon="chart-column" />
    </div>

    <nav aria-label="Filter penilaian" class="mb-6 flex gap-2.5">
        <x-chip :href="route('admin.chat-logs.index')" :active="! request('nilai')">Semua</x-chip>
        <x-chip :href="route('admin.chat-logs.index', ['nilai' => 'buruk'])" :active="request('nilai') === 'buruk'">Tidak membantu</x-chip>
        <x-chip :href="route('admin.chat-logs.index', ['nilai' => 'baik'])" :active="request('nilai') === 'baik'">Membantu</x-chip>
    </nav>

    <div class="flex flex-col gap-4">
        @forelse ($answers as $answer)
            @php($question = ($questions[$answer->chat_conversation_id] ?? collect())->where('id', '<', $answer->id)->last())
            <article class="flex flex-col gap-3 rounded-3xl bg-white p-6 ring-1 ring-inset ring-hairline">
                <div class="flex items-center justify-between gap-3">
                    <span class="text-xs font-semibold text-slate">{{ $answer->created_at->translatedFormat('j M Y H.i') }} · {{ ($answer->input_tokens ?? 0) + ($answer->output_tokens ?? 0) }} token</span>
                    @if ($answer->rating === 1)<x-badge tone="success">Membantu</x-badge>@elseif ($answer->rating === -1)<x-badge tone="danger">Tidak membantu</x-badge>@endif
                </div>
                @if ($question)<p class="rounded-2xl bg-blue-wash p-4 text-sm"><span class="font-bold">Pertanyaan:</span> {{ $question->content }}</p>@endif
                <p class="whitespace-pre-line text-sm leading-[1.6]">{{ $answer->content }}</p>
                @if ($answer->links)<p class="text-xs text-slate">Tautan direkomendasikan: {{ collect($answer->links)->pluck('domain')->join(', ') }}</p>@endif
            </article>
        @empty
            <x-empty-state icon="sparkles" title="Belum ada jawaban" />
        @endforelse
    </div>
    <div class="mt-4">{{ $answers->links('pagination.peduli') }}</div>
</x-app-layout>
