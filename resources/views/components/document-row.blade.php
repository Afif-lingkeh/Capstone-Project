{{-- resources/views/components/document-row.blade.php --}}
{{-- Satu baris berkas: ikon jenis file, judul, info, status, dan aksi lihat/unduh. Slot "actions" untuk aksi tambahan. --}}
@props(['document', 'showOwner' => false])

<li {{ $attributes->class('relative flex flex-col gap-3 px-6 py-4 sm:flex-row sm:items-center sm:gap-4') }}>
    <div class="flex min-w-0 flex-1 items-center gap-4">
        <span class="flex size-11 shrink-0 flex-col items-center justify-center rounded-xl bg-blue-wash text-blue">
            <x-icon :name="$document->icon()" class="size-[18px]" />
            <span class="text-[9px] font-extrabold leading-none">{{ $document->extension() }}</span>
        </span>
        <div class="flex min-w-0 flex-1 flex-col gap-0.5">
            <p class="truncate text-[15px] font-bold">{{ $document->title }}</p>
            <p class="truncate text-xs font-medium text-slate">
                @if ($showOwner){{ $document->owner->name }} · @endif
                {{ $document->category->label() }} · {{ $document->humanSize() }} · {{ $document->created_at->translatedFormat('j M Y') }}
                @if ($document->uploaded_by && $document->uploaded_by !== $document->owner_id) · diunggah {{ $document->uploader?->name ?? 'pesantren' }}@endif
            </p>
            @if ($document->status === \App\Enums\DocumentStatus::NeedsFix && $document->review_note)
                <p class="text-xs font-semibold text-danger-ink">Perlu diperbaiki: {{ $document->review_note }}</p>
            @elseif ($document->note)
                <p class="line-clamp-1 text-xs text-slate">{{ $document->note }}</p>
            @endif
        </div>
    </div>
    <div class="flex flex-wrap items-center gap-2">
        <x-status-badge :status="$document->status" />
        @if ($document->isImage() || $document->isPdf())
            <a href="{{ route('documents.download', [$document, 'lihat' => 1]) }}" target="_blank" rel="noopener" class="btn-ghost px-3 py-2 text-sm text-blue">Lihat<span class="sr-only"> {{ $document->title }} (tab baru)</span></a>
        @endif
        <a href="{{ route('documents.download', $document) }}" class="flex size-10 items-center justify-center rounded-xl text-blue hover:bg-pebble"><x-icon name="download" class="size-[18px]" /><span class="sr-only">Unduh {{ $document->title }}</span></a>
        {{ $actions ?? '' }}
    </div>
</li>
