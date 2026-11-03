{{-- resources/views/articles/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">Info Kampus</x-slot>

    <x-page-intro eyebrow="Info kampus" title="Panduan masuk kampus"
                  description="SNBP, SNBT, jalur mandiri, beasiswa, dan kuliah di luar negeri. Selalu cocokkan jadwal dengan sumber resmi yang dicantumkan." />

    <form method="GET" role="search" class="mb-4 flex h-14 items-center gap-3 rounded-full border-[1.5px] border-hairline bg-white pl-5 pr-2 focus-within:border-blue focus-within:ring-2 focus-within:ring-focus">
        @if ($currentCategory)<input type="hidden" name="kategori" value="{{ $currentCategory->value }}">@endif
        <x-icon name="search" class="text-slate" />
        <label for="q" class="sr-only">Cari artikel</label>
        <input id="q" type="search" name="q" value="{{ request('q') }}" placeholder="Cari artikel" class="h-full min-w-0 flex-1 border-0 bg-transparent p-0 text-[15px] font-medium placeholder:text-slate focus:ring-0 focus-visible:outline-none">
        <button type="submit" class="btn-primary rounded-full px-5 py-2.5 text-sm shadow-none">Cari</button>
    </form>

    <nav aria-label="Kategori" class="-mx-5 mb-6 flex gap-2.5 overflow-x-auto px-5 pb-1 lg:mx-0 lg:px-0">
        <x-chip :href="route('articles.index', request()->only('q'))" :active="! $currentCategory" :count="$counts->sum()">Semua</x-chip>
        @foreach ($categories as $category)
            <x-chip :href="route('articles.index', [...request()->only('q'), 'kategori' => $category->value])" :active="$currentCategory === $category" :count="$counts[$category->value] ?? 0">{{ $category->label() }}</x-chip>
        @endforeach
    </nav>

    @if ($articles->isEmpty())
        <x-empty-state icon="newspaper" title="Belum ada artikel di sini">Coba kategori lain atau kata kunci yang berbeda.</x-empty-state>
    @else
        <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($articles as $article)
                <article class="relative flex flex-col gap-3 rounded-3xl bg-white p-6 ring-1 ring-inset ring-hairline transition-colors duration-150 hover:bg-cloud">
                    <x-badge :tone="$article->category->tone()" :dot="false">{{ $article->category->label() }}</x-badge>
                    <h2 class="text-lg font-extrabold leading-snug">
                        <a href="{{ route('articles.show', $article) }}" class="after:absolute after:inset-0">{{ $article->title }}</a>
                    </h2>
                    <p class="flex-1 text-sm leading-[1.57] text-slate">{{ $article->excerpt }}</p>
                    <p class="flex items-center gap-2 text-xs font-semibold text-slate">
                        <x-icon name="clock" class="size-3.5" />{{ $article->readingMinutes() }} menit membaca · {{ $article->published_at->translatedFormat('j M Y') }}
                    </p>
                </article>
            @endforeach
        </div>
        <div class="mt-6">{{ $articles->links('pagination.peduli') }}</div>
    @endif
</x-app-layout>
