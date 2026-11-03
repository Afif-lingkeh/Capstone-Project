{{-- resources/views/articles/show.blade.php --}}
<x-app-layout>
    <x-slot name="header">Info Kampus</x-slot>

    <div class="mx-auto flex max-w-6xl flex-col gap-10 xl:flex-row xl:items-start">
        <article class="flex max-w-3xl flex-1 flex-col gap-6">
            <a href="{{ route('articles.index', ['kategori' => $article->category->value]) }}" class="flex w-fit items-center gap-1.5 py-1 text-sm font-bold text-blue hover:underline"><x-icon name="arrow-left" class="size-4" /> {{ $article->category->label() }}</a>
            <header class="flex flex-col gap-3">
                <h1 class="text-[30px] font-extrabold leading-[1.12] tracking-[-0.02em] lg:text-[40px]">{{ $article->title }}</h1>
                <p class="text-[17px] leading-[1.6] text-slate">{{ $article->excerpt }}</p>
                <p class="text-[13px] font-semibold text-slate">{{ $article->published_at?->translatedFormat('j F Y') ?? 'Draf' }} · {{ $article->readingMinutes() }} menit membaca</p>
            </header>

            <div class="prose-peduli">{!! Str::markdown($article->body, ['html_input' => 'strip', 'allow_unsafe_links' => false]) !!}</div>

            @if ($article->source_url)
                <x-alert icon="shield-check" title="Cocokkan dengan sumber resmi">
                    Jadwal dan syarat bisa berubah. Periksa di
                    <a href="{{ $article->source_url }}" target="_blank" rel="noopener noreferrer" class="break-all font-mono font-bold text-blue underline">{{ parse_url($article->source_url, PHP_URL_HOST) }}</a><span class="sr-only"> (tab baru)</span>.
                </x-alert>
            @endif
        </article>

        <aside class="flex flex-col gap-4 xl:sticky xl:top-24 xl:w-[340px]">
            @if (Route::has('guidance.index') && auth()->user()->hasRole('santri'))
                <div class="flex flex-col gap-3 rounded-3xl bg-ink p-6 text-white">
                    <x-icon name="calendar-check" class="text-teal-light" />
                    <p class="text-lg font-extrabold">Masih bingung memilih jalur?</p>
                    <p class="text-sm leading-[1.57] text-hairline">Diskusikan bersama ustadz atau ustadzah pembimbing akademik.</p>
                    <a href="{{ route('guidance.index') }}" class="btn-primary w-fit py-3">Pesan jadwal <x-icon name="arrow-right" class="size-[18px]" /></a>
                </div>
            @endif
            @if ($related->isNotEmpty())
                <x-card title="Artikel terkait" :padded="false">
                    <ul class="divide-y divide-hairline">
                        @foreach ($related as $item)
                            <li><a href="{{ route('articles.show', $item) }}" class="block px-6 py-4 text-sm font-bold hover:bg-cloud">{{ $item->title }}</a></li>
                        @endforeach
                    </ul>
                </x-card>
            @endif
        </aside>
    </div>
</x-app-layout>
