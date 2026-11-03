{{-- resources/views/components/page-intro.blade.php --}}
{{-- Pembuka halaman: eyebrow + judul + deskripsi, dengan aksi di kanan. --}}
@props(['eyebrow' => null, 'title', 'description' => null])

<div {{ $attributes->class('mb-7 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between') }}>
    <div class="flex max-w-3xl flex-col gap-2">
        @if ($eyebrow)<p class="eyebrow text-teal-ink">{{ $eyebrow }}</p>@endif
        <h1 class="text-[26px] font-extrabold leading-tight tracking-[-0.02em] lg:text-[32px]">{{ $title }}</h1>
        @if ($description)<p class="text-[15px] leading-6 text-slate">{{ $description }}</p>@endif
    </div>
    @isset($actions)<div class="flex flex-wrap gap-3">{{ $actions }}</div>@endisset
</div>
