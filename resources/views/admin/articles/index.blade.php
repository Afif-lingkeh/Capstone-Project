{{-- resources/views/admin/articles/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">Artikel Kampus</x-slot>

    <x-page-intro title="Artikel info kampus" description="Panduan jalur masuk dan beasiswa untuk santri. Cantumkan sumber resmi di setiap artikel.">
        <x-slot name="actions">
            <a href="{{ route('admin.majors.index') }}" class="btn-secondary">Daftar jurusan</a>
            <a href="{{ route('admin.articles.create') }}" class="btn-primary"><x-icon name="plus" class="size-[18px]" />Tulis artikel</a>
        </x-slot>
    </x-page-intro>

    <nav aria-label="Kategori" class="-mx-5 mb-6 flex gap-2.5 overflow-x-auto px-5 pb-1 lg:mx-0 lg:px-0">
        <x-chip :href="route('admin.articles.index')" :active="! request('kategori')">Semua</x-chip>
        @foreach ($categories as $category)
            <x-chip :href="route('admin.articles.index', ['kategori' => $category->value])" :active="request('kategori') === $category->value">{{ $category->label() }}</x-chip>
        @endforeach
    </nav>

    <div class="overflow-x-auto rounded-3xl bg-white ring-1 ring-hairline">
        <table class="min-w-full divide-y divide-hairline text-sm">
            <thead class="bg-cloud text-left text-[11px] font-bold uppercase tracking-[0.12em] text-slate">
                <tr><th class="px-5 py-3">Judul</th><th class="px-5 py-3">Kategori</th><th class="px-5 py-3">Status</th><th class="px-5 py-3 text-right">Aksi</th></tr>
            </thead>
            <tbody class="divide-y divide-hairline">
                @forelse ($articles as $article)
                    <tr class="hover:bg-cloud">
                        <td class="px-5 py-4"><p class="font-bold">{{ $article->title }}</p><p class="line-clamp-1 text-xs text-slate">{{ $article->excerpt }}</p></td>
                        <td class="whitespace-nowrap px-5 py-4"><x-badge :tone="$article->category->tone()" :dot="false">{{ $article->category->label() }}</x-badge></td>
                        <td class="whitespace-nowrap px-5 py-4">
                            @if ($article->isPublished())
                                <x-badge tone="success">Terbit {{ $article->published_at->translatedFormat('j M') }}</x-badge>
                            @else
                                <x-badge tone="gold">Draf</x-badge>
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-5 py-4 text-right">
                            <a href="{{ route('articles.show', $article) }}" class="mr-3 font-bold text-slate hover:underline">Pratinjau</a>
                            <a href="{{ route('admin.articles.edit', $article) }}" class="font-bold text-blue hover:underline">Ubah<span class="sr-only"> {{ $article->title }}</span></a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-5 py-10 text-center text-slate">Belum ada artikel.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $articles->links('pagination.peduli') }}</div>
</x-app-layout>
