{{-- resources/views/chat/index.blade.php --}}
{{-- Desain "AI Chat": gelembung pesan, kartu tautan resmi, penilaian membantu/tidak, saran pertanyaan. --}}
@php($suggestions = ['Bagaimana cara daftar SNBP?', 'Syarat magang ke Jepang', 'Beasiswa kuliah di luar negeri', 'Arti hasil tes potensiku'])
<x-app-layout>
    <x-slot name="header">Asisten AI</x-slot>

    @if (session('crisis'))
        <x-crisis-banner :on-duty="$onDuty" class="mb-6" />
    @endif

    @if (! $hasConsent)
        <div class="mx-auto flex max-w-2xl flex-col gap-4 rounded-3xl bg-sand p-7 ring-1 ring-inset ring-gold/25">
            <span class="flex size-12 items-center justify-center rounded-2xl bg-white"><x-icon name="lock" class="size-6 text-gold-ink" /></span>
            <h1 class="text-xl font-extrabold">Asisten AI belum dibuka</h1>
            <p class="text-[15px] leading-6 text-slate">Fitur ini menunggu izin dari orang tua atau wali. Minta mereka menghubungi admin pesantren.</p>
        </div>
    @else
        <div class="flex flex-col gap-6 xl:flex-row xl:items-start">
            <aside class="hidden flex-col gap-3 xl:flex xl:w-[260px]">
                <a href="{{ route('chat.index', ['baru' => 1]) }}" class="btn-secondary w-full"><x-icon name="plus" class="size-[18px]" />Percakapan baru</a>
                <nav aria-label="Riwayat percakapan" class="flex flex-col gap-1">
                    @foreach ($conversations as $item)
                        <a href="{{ route('chat.show', $item) }}" @if ($conversation?->is($item)) aria-current="page" @endif
                           class="truncate rounded-xl px-3 py-2.5 text-sm font-semibold text-slate hover:bg-white aria-[current=page]:bg-white aria-[current=page]:text-ink aria-[current=page]:ring-1 aria-[current=page]:ring-hairline">{{ $item->title }}</a>
                    @endforeach
                </nav>
            </aside>

            @php($current = request()->boolean('baru') ? null : $conversation)
            <section aria-labelledby="judul-asisten" class="mx-auto flex w-full max-w-3xl flex-1 flex-col overflow-hidden rounded-3xl bg-white ring-1 ring-hairline">
                <header class="flex items-center gap-3 border-b border-hairline px-6 py-[18px]">
                    <span class="flex size-10 items-center justify-center rounded-[20px] bg-ink"><x-icon name="sparkles" class="size-5 text-teal-light" /></span>
                    <div class="flex flex-1 flex-col gap-0.5">
                        <h1 id="judul-asisten" class="text-[15px] font-extrabold">Asisten PEDULI</h1>
                        <p class="text-xs font-medium text-slate">Menjawab dari direktori terverifikasi</p>
                    </div>
                    <a href="{{ route('chat.index', ['baru' => 1]) }}" class="btn-ghost px-3 py-2 text-sm xl:hidden"><x-icon name="plus" class="size-4" />Baru</a>
                </header>

                <ol class="flex min-h-[320px] flex-col gap-3.5 bg-cloud p-6" aria-live="polite">
                    @forelse ($current?->messages ?? [] as $message)
                        @if ($message->role === 'user')
                            <li class="flex justify-end"><p class="max-w-[85%] whitespace-pre-line rounded-[18px_18px_4px_18px] bg-blue px-4 py-3 text-[15px] font-medium leading-[1.53] text-white"><span class="sr-only">Kamu: </span>{{ $message->content }}</p></li>
                        @else
                            <li class="flex flex-col gap-2.5">
                                <div @class(['max-w-[90%] whitespace-pre-line rounded-[18px_18px_18px_4px] px-4 py-3 text-[15px] font-medium leading-[1.53] ring-1 ring-inset', 'bg-danger-wash ring-danger-line' => $message->is_crisis, 'bg-white ring-hairline' => ! $message->is_crisis])><span class="sr-only">Asisten: </span>{{ $message->content }}</div>
                                @foreach ($message->links ?? [] as $link)
                                    <a href="{{ $link['url'] }}" target="_blank" rel="noopener noreferrer" class="flex max-w-md items-center gap-3 rounded-2xl bg-white p-3.5 ring-[1.5px] ring-inset ring-success hover:bg-success-wash">
                                        <span class="flex size-10 items-center justify-center rounded-xl bg-success-wash"><x-icon name="shield-check" class="size-5 text-success" /></span>
                                        <span class="flex flex-1 flex-col gap-0.5"><span class="text-sm font-bold">{{ $link['name'] }}</span><span class="font-mono text-[13px] font-bold text-blue">{{ $link['domain'] }}</span></span>
                                        <x-icon name="external-link" class="size-4 text-blue" /><span class="sr-only">(situs resmi terverifikasi, tab baru)</span>
                                    </a>
                                @endforeach
                                @unless ($message->is_crisis)
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-semibold text-slate">Membantu?</span>
                                        @foreach ([1 => 'thumbs-up', -1 => 'thumbs-down'] as $value => $icon)
                                            <form method="POST" action="{{ route('chat.rate', $message) }}">
                                                @csrf <input type="hidden" name="rating" value="{{ $value }}">
                                                <button type="submit" aria-pressed="{{ $message->rating === $value ? 'true' : 'false' }}"
                                                        @class(['flex size-9 items-center justify-center rounded-[10px] ring-1 ring-inset', 'bg-blue-wash text-cobalt ring-blue' => $message->rating === $value, 'bg-white text-slate ring-hairline hover:bg-pebble' => $message->rating !== $value])>
                                                    <x-icon :name="$icon" class="size-4" /><span class="sr-only">{{ $value === 1 ? 'Membantu' : 'Tidak membantu' }}</span>
                                                </button>
                                            </form>
                                        @endforeach
                                    </div>
                                @endunless
                            </li>
                        @endif
                    @empty
                        <li class="flex flex-1 flex-col items-center justify-center gap-2 py-10 text-center">
                            <x-icon name="sparkles" class="size-8 text-teal" />
                            <p class="text-base font-extrabold">Assalamu’alaikum! Ada yang ingin ditanyakan?</p>
                            <p class="max-w-md text-sm text-slate">Tanya soal kampus, jurusan, jalur masuk, program luar negeri, atau hasil tes potensimu.</p>
                        </li>
                    @endforelse
                </ol>

                @if (! $configured)
                    <div class="p-6"><x-alert variant="warning" title="Asisten AI belum diaktifkan">Pesantren belum memasang kunci layanan AI. Sementara itu, silakan bertanya langsung kepada pembimbing akademik.</x-alert></div>
                @else
                    <form method="POST" action="{{ route('chat.send') }}" x-data="{ text: @js(old('message', '')), sending: false }" @submit="sending = true" class="flex flex-col gap-3 px-6 pb-4 pt-4">
                        @csrf
                        @if ($current)<input type="hidden" name="conversation_id" value="{{ $current->id }}">@endif
                        @if (! $current || $current->messages->isEmpty())
                            <div class="-mx-6 flex gap-2 overflow-x-auto px-6 pb-1">
                                @foreach ($suggestions as $suggestion)
                                    <button type="button" @click="text = @js($suggestion); $nextTick(() => $refs.input.focus())" class="shrink-0 rounded-full px-[18px] py-3 text-[13px] font-semibold ring-[1.5px] ring-inset ring-hairline hover:bg-pebble">{{ $suggestion }}</button>
                                @endforeach
                            </div>
                        @endif
                        <div class="flex items-end gap-2.5 rounded-[28px] py-1.5 pl-5 pr-1.5 ring-[1.5px] ring-inset ring-hairline focus-within:ring-2 focus-within:ring-blue">
                            <label for="message" class="sr-only">Tulis pertanyaanmu</label>
                            <textarea id="message" name="message" x-ref="input" x-model="text" rows="1" maxlength="1500" required placeholder="Tulis pertanyaanmu…"
                                      @keydown.enter="if (! $event.shiftKey) { $event.preventDefault(); $el.form.requestSubmit() }"
                                      class="max-h-40 min-h-[44px] flex-1 resize-none border-0 bg-transparent px-0 py-2.5 text-[15px] font-medium placeholder:text-slate focus:ring-0 focus-visible:outline-none"></textarea>
                            <button type="submit" :disabled="sending || ! text.trim()" class="flex size-11 shrink-0 items-center justify-center rounded-full bg-blue text-white hover:bg-cobalt disabled:opacity-50">
                                <x-icon name="send" class="size-[18px]" x-show="! sending" /><x-icon name="loader-circle" class="size-[18px] motion-safe:animate-spin" x-show="sending" x-cloak />
                                <span class="sr-only">Kirim</span>
                            </button>
                        </div>
                        <p class="text-center text-xs font-medium text-slate">Asisten AI bisa keliru dan bukan pengganti konselor. {{ $remaining }} pesan tersisa hari ini.</p>
                    </form>
                @endif
            </section>
        </div>
    @endif
</x-app-layout>
