{{-- resources/views/documents/index.blade.php --}}
@php($accept = '.pdf,.jpg,.jpeg,.png,.webp,.doc,.docx,.xls,.xlsx')
<x-app-layout>
    <x-slot name="header">Berkas Saya</x-slot>

    <x-page-intro eyebrow="Berkas saya" title="Simpan berkas pentingmu di satu tempat"
                  :description="'Rapor, sertifikat, dan surat siap dipakai saat mendaftar kampus atau beasiswa. '.$total.' dari '.\App\Http\Controllers\DocumentController::MAX_FILES.' berkas terpakai.'" />

    @if ($needsFix)
        <x-alert variant="danger" title="{{ $needsFix }} berkas perlu diperbaiki" class="mb-6">Baca catatan admin pada berkas bertanda merah, lalu unggah ulang versi yang benar.</x-alert>
    @endif

    <div class="flex flex-col gap-6 xl:flex-row xl:items-start">
        <form method="POST" action="{{ route('documents.store') }}" enctype="multipart/form-data" class="flex flex-col gap-5 rounded-3xl bg-white p-6 ring-1 ring-inset ring-hairline xl:sticky xl:top-24 xl:w-[400px]">
            @csrf
            <h2 class="text-lg font-extrabold">Unggah berkas</h2>
            <x-file-drop :accept="$accept" hint="PDF, gambar, Word, atau Excel · maks. 5 MB" />
            <x-field label="Jenis berkas" for="category">
                <x-select-input id="category" name="category" required>
                    @foreach ($categories as $category)
                        <option value="{{ $category->value }}" @selected(old('category') === $category->value)>{{ $category->label() }}</option>
                    @endforeach
                </x-select-input>
            </x-field>
            <x-field label="Nama berkas" for="title" hint="mis. Rapor semester 3, Piagam MTQ 2026">
                <x-text-input id="title" name="title" :value="old('title')" maxlength="150" required />
            </x-field>
            <x-field label="Catatan" for="note" optional>
                <x-text-input id="note" name="note" :value="old('note')" maxlength="500" />
            </x-field>
            <x-primary-button class="w-full"><x-icon name="upload" class="size-[18px]" />Unggah</x-primary-button>
            <p class="text-xs leading-[1.5] text-slate">Berkas hanya bisa dibuka olehmu, pembimbing akademikmu, dan admin pesantren. Jangan unggah password atau data orang lain.</p>
        </form>

        <div class="flex flex-1 flex-col gap-6">
            @if ($total === 0)
                <x-empty-state icon="folder-open" title="Belum ada berkas">Mulai dari rapor terakhirmu. Nanti tinggal unduh saat dibutuhkan untuk pendaftaran.</x-empty-state>
            @endif
            @foreach ($categories as $category)
                @if ($documents->has($category->value))
                    <x-card :title="$category->label()" :padded="false">
                        <ul class="divide-y divide-hairline">
                            @foreach ($documents[$category->value] as $document)
                                <x-document-row :document="$document">
                                    @can('delete', $document)
                                        <x-slot name="actions">
                                            <form method="POST" action="{{ route('documents.destroy', $document) }}" onsubmit="return confirm('Hapus berkas ini?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="flex size-10 items-center justify-center rounded-xl text-danger-ink hover:bg-danger-wash"><x-icon name="trash-2" class="size-[18px]" /><span class="sr-only">Hapus {{ $document->title }}</span></button>
                                            </form>
                                        </x-slot>
                                    @endcan
                                </x-document-row>
                            @endforeach
                        </ul>
                    </x-card>
                @endif
            @endforeach
        </div>
    </div>
</x-app-layout>
