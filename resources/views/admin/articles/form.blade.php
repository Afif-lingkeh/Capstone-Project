{{-- resources/views/admin/articles/form.blade.php --}}
@php($editing = $article->exists)
<x-app-layout>
    <x-slot name="header">{{ $editing ? 'Ubah Artikel' : 'Tulis Artikel' }}</x-slot>

    <form method="POST" action="{{ $editing ? route('admin.articles.update', $article) : route('admin.articles.store') }}" class="flex max-w-4xl flex-col gap-6">
        @csrf
        @if ($editing) @method('PUT') @endif

        <x-card>
            <div class="grid gap-5 sm:grid-cols-2">
                <x-field label="Judul" for="title" class="sm:col-span-2">
                    <x-text-input id="title" name="title" :value="old('title', $article->title)" required />
                </x-field>
                <x-field label="Kategori" for="category">
                    <x-select-input id="category" name="category" required>
                        @foreach ($categories as $category)
                            <option value="{{ $category->value }}" @selected(old('category', $article->category?->value) === $category->value)>{{ $category->label() }}</option>
                        @endforeach
                    </x-select-input>
                </x-field>
                <x-field label="Sumber resmi" for="source_url" optional hint="mis. https://snpmb.id">
                    <x-text-input id="source_url" name="source_url" type="url" :value="old('source_url', $article->source_url)" />
                </x-field>
                <x-field label="Ringkasan" for="excerpt" class="sm:col-span-2" hint="Maks. 300 karakter, tampil di daftar artikel.">
                    <x-textarea id="excerpt" name="excerpt" rows="2" maxlength="300" class="min-h-[80px]" required>{{ old('excerpt', $article->excerpt) }}</x-textarea>
                </x-field>
                <x-field label="Isi artikel" for="body" class="sm:col-span-2" hint="Format Markdown: ## Subjudul, **tebal**, - daftar, 1. langkah, > catatan penting.">
                    <x-textarea id="body" name="body" rows="18" class="font-mono text-sm" required>{{ old('body', $article->body) }}</x-textarea>
                </x-field>
            </div>
        </x-card>

        <label class="flex items-start gap-3 rounded-3xl bg-white p-6 ring-1 ring-inset ring-hairline">
            <input type="hidden" name="publish" value="0">
            <input type="checkbox" name="publish" value="1" @checked(old('publish', $article->published_at !== null)) class="mt-0.5 size-[22px] rounded-[7px] border-[1.5px] border-mist text-blue focus:ring-focus">
            <span class="flex flex-col gap-0.5"><span class="text-[15px] font-bold">Terbitkan</span><span class="text-sm text-slate">Artikel langsung bisa dibaca semua pengguna.</span></span>
        </label>

        <div class="flex flex-wrap items-center gap-3">
            <x-primary-button>Simpan artikel</x-primary-button>
            <a href="{{ route('admin.articles.index') }}" class="btn-ghost">Batal</a>
        </div>
    </form>

    @if ($editing)
        <form method="POST" action="{{ route('admin.articles.destroy', $article) }}" class="mt-10 max-w-4xl border-t border-hairline pt-6" onsubmit="return confirm('Hapus artikel ini?')">
            @csrf @method('DELETE')
            <x-danger-button>Hapus artikel</x-danger-button>
        </form>
    @endif
</x-app-layout>
