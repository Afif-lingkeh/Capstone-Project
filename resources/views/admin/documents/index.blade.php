{{-- resources/views/admin/documents/index.blade.php --}}
@php($accept = '.pdf,.jpg,.jpeg,.png,.webp,.doc,.docx,.xls,.xlsx')
<x-app-layout>
    <x-slot name="header">Berkas Santri</x-slot>

    <x-page-intro title="Berkas santri" description="Periksa berkas yang diunggah santri, atau unggahkan berkas dari pesantren (mis. surat keterangan) langsung ke akun santri." />

    <div class="flex flex-col gap-6 xl:flex-row xl:items-start">
        <div class="flex flex-1 flex-col gap-4">
            <nav aria-label="Status" class="-mx-5 flex gap-2.5 overflow-x-auto px-5 pb-1 lg:mx-0 lg:px-0">
                <x-chip :href="route('admin.documents.index', request()->except(['status', 'page']))" :active="! $currentStatus" :count="$counts->sum()">Semua</x-chip>
                @foreach (\App\Enums\DocumentStatus::cases() as $status)
                    <x-chip :href="route('admin.documents.index', [...request()->except(['status', 'page']), 'status' => $status->value])" :active="$currentStatus === $status" :count="$counts[$status->value] ?? 0">{{ $status->label() }}</x-chip>
                @endforeach
            </nav>

            <form method="GET" class="flex flex-col gap-3 sm:flex-row">
                @if ($currentStatus)<input type="hidden" name="status" value="{{ $currentStatus->value }}">@endif
                <label for="q" class="sr-only">Cari</label>
                <x-text-input id="q" name="q" type="search" :value="request('q')" placeholder="Cari nama santri, NIS, atau nama berkas" class="sm:flex-1" />
                <x-select-input name="kategori" aria-label="Jenis berkas" class="sm:w-56">
                    <option value="">Semua jenis</option>
                    @foreach ($categories as $category)<option value="{{ $category->value }}" @selected(request('kategori') === $category->value)>{{ $category->label() }}</option>@endforeach
                </x-select-input>
                <button type="submit" class="btn-secondary">Filter</button>
            </form>

            <x-card :padded="false">
                <ul class="divide-y divide-hairline">
                    @forelse ($documents as $document)
                        <x-document-row :document="$document" show-owner x-data="{ open: false }">
                            <x-slot name="actions">
                                <button type="button" @click="open = ! open" :aria-expanded="open.toString()" class="btn-secondary px-4 py-2 text-sm">Periksa</button>
                                <div x-show="open" x-cloak @click.outside="open = false" class="absolute right-6 z-20 mt-2 w-80 rounded-2xl bg-white p-4 shadow-float ring-1 ring-hairline sm:top-full">
                                    <form method="POST" action="{{ route('admin.documents.review', $document) }}" class="flex flex-col gap-3" x-data="{ status: '{{ $document->status->value === 'needs_fix' ? 'needs_fix' : 'reviewed' }}' }">
                                        @csrf @method('PATCH')
                                        <fieldset class="flex flex-col gap-2">
                                            <legend class="mb-1 text-sm font-bold">Hasil pemeriksaan</legend>
                                            <label class="flex items-center gap-2 text-sm font-semibold"><input type="radio" name="status" value="reviewed" x-model="status" class="size-5 border-mist text-blue focus:ring-focus">Sudah diperiksa, sesuai</label>
                                            <label class="flex items-center gap-2 text-sm font-semibold"><input type="radio" name="status" value="needs_fix" x-model="status" class="size-5 border-mist text-blue focus:ring-focus">Perlu diperbaiki</label>
                                        </fieldset>
                                        <div x-show="status === 'needs_fix'">
                                            <label for="note-{{ $document->id }}" class="text-sm font-bold">Apa yang perlu diperbaiki?</label>
                                            <x-textarea id="note-{{ $document->id }}" name="review_note" rows="2" class="mt-1 min-h-[72px]">{{ $document->review_note }}</x-textarea>
                                        </div>
                                        <x-primary-button class="w-full py-3 text-sm">Simpan</x-primary-button>
                                    </form>
                                    <form method="POST" action="{{ route('documents.destroy', $document) }}" class="mt-3 border-t border-hairline pt-3" onsubmit="return confirm('Hapus berkas ini secara permanen?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-sm font-bold text-danger-ink hover:underline">Hapus berkas</button>
                                    </form>
                                </div>
                            </x-slot>
                        </x-document-row>
                    @empty
                        <li class="p-6"><x-empty-state icon="folder-open" title="Tidak ada berkas" class="border-0" /></li>
                    @endforelse
                </ul>
            </x-card>
            {{ $documents->links('pagination.peduli') }}
        </div>

        <form method="POST" action="{{ route('admin.documents.store') }}" enctype="multipart/form-data" class="flex flex-col gap-5 rounded-3xl bg-white p-6 ring-1 ring-inset ring-hairline xl:sticky xl:top-24 xl:w-[380px]">
            @csrf
            <h2 class="text-lg font-extrabold">Unggahkan untuk santri</h2>
            <x-field label="Santri" for="owner_id" error="owner_id">
                <x-text-input id="owner_id-search" list="daftar-santri" placeholder="Ketik nama atau NIS" aria-label="Cari santri"
                              x-data x-on:change="const o = [...document.querySelectorAll('#daftar-santri option')].find(o => o.value === $el.value); document.getElementById('owner_id').value = o ? o.dataset.id : ''" />
                <datalist id="daftar-santri">
                    @foreach ($santri as $s)<option value="{{ $s->name }} · {{ $s->username }}" data-id="{{ $s->id }}"></option>@endforeach
                </datalist>
                <input type="hidden" id="owner_id" name="owner_id" value="{{ old('owner_id', request('santri')) }}">
            </x-field>
            <x-file-drop id="admin-file" :accept="$accept" hint="Maks. 5 MB" />
            <x-field label="Jenis berkas" for="admin-category" error="category">
                <x-select-input id="admin-category" name="category">
                    @foreach ($categories as $category)<option value="{{ $category->value }}" @selected(old('category', 'surat') === $category->value)>{{ $category->label() }}</option>@endforeach
                </x-select-input>
            </x-field>
            <x-field label="Nama berkas" for="admin-title" error="title"><x-text-input id="admin-title" name="title" :value="old('title')" maxlength="150" required /></x-field>
            <x-field label="Catatan untuk santri" for="admin-note" error="note" optional><x-text-input id="admin-note" name="note" :value="old('note')" maxlength="500" /></x-field>
            <x-primary-button class="w-full"><x-icon name="upload" class="size-[18px]" />Unggah & kirim</x-primary-button>
            <p class="text-xs text-slate">Berkas dari pesantren langsung bertanda “Sudah diperiksa” dan santri diberi tahu.</p>
        </form>
    </div>
</x-app-layout>
