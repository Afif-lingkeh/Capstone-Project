{{-- resources/views/components/institution-card.blade.php --}}
{{-- Desain "Card/Institution". Untuk contoh statis isi name/location/tag/domain/checked;
     untuk data asli kirim :institution (beserta links terverifikasi). --}}
@props([
    'institution' => null,
    'name' => null,
    'location' => null,
    'tag' => null,
    'domain' => null,
    'checked' => null,
])
@php
    $tagColors = fn (?string $type) => match ($type) {
        'pts' => 'bg-gold-wash text-gold-ink',
        'international' => 'bg-teal-wash text-teal-ink',
        'kedinasan' => 'bg-violet-wash text-violet-ink',
        default => 'bg-blue-wash text-cobalt',
    };

    if ($institution) {
        $name = $institution->name;
        $location = collect([$institution->city, $institution->country?->name])->filter()->join(', ');
        $tag = $institution->type->shortLabel();
        $tagClass = $tagColors($institution->type->value);
        $links = $institution->links;
    } else {
        $tagClass = $tagColors(null);
        $links = null;
    }
@endphp

<article {{ $attributes->class('flex flex-col gap-[18px] rounded-3xl bg-white p-6 ring-1 ring-inset ring-hairline') }}>
    <header class="flex justify-between gap-3">
        <div class="flex min-w-0 flex-1 flex-col gap-1">
            <h3 class="text-lg font-extrabold leading-snug text-ink">{{ $name }}</h3>
            @if ($location)
                <p class="text-[13px] font-medium text-slate">{{ $location }}</p>
            @endif
        </div>
        <span class="h-fit shrink-0 rounded-lg px-2.5 py-[5px] text-xs font-bold tracking-[0.025em] {{ $tagClass }}">{{ $tag }}</span>
    </header>

    @if ($links)
        @foreach ($links as $link)
            <div class="flex flex-col gap-[18px]">
                <a href="{{ $link->url }}" target="_blank" rel="noopener noreferrer"
                   class="group flex flex-col gap-1.5 rounded-2xl bg-cloud px-[18px] py-3.5 ring-1 ring-inset ring-hairline transition-colors duration-150 hover:bg-blue-wash">
                    <span class="text-[11px] font-bold uppercase tracking-[0.13em] text-slate">{{ $link->label }}</span>
                    <span class="flex items-center gap-2.5">
                        <x-icon name="lock" class="size-4 text-success" />
                        {{-- Domain ditampilkan besar: inilah yang harus dicocokkan santri. --}}
                        <span class="min-w-0 flex-1 break-all font-mono text-[22px] font-bold leading-tight text-blue">{{ $link->domain }}</span>
                        <x-icon name="external-link" class="size-[18px] text-blue" />
                        <span class="sr-only">(buka situs resmi di tab baru)</span>
                    </span>
                </a>
                <footer class="flex flex-wrap items-center justify-between gap-2">
                    <x-verified-badge :link="$link" />
                    <p class="text-xs font-medium text-slate">
                        @if ($link->last_checked_at)
                            Dicek <time datetime="{{ $link->last_checked_at->toIso8601String() }}" title="{{ $link->last_checked_at->translatedFormat('j F Y H:i') }}">{{ $link->last_checked_at->translatedFormat('j M') }}</time>
                        @else
                            Belum dicek otomatis
                        @endif
                    </p>
                </footer>
            </div>
        @endforeach
    @else
        <div class="flex flex-col gap-1.5 rounded-2xl bg-cloud px-[18px] py-3.5 ring-1 ring-inset ring-hairline">
            <span class="text-[11px] font-bold tracking-[0.13em] text-slate">SITUS RESMI</span>
            <span class="flex items-center gap-2.5">
                <x-icon name="lock" class="size-4 text-success" />
                <span class="flex-1 font-mono text-[22px] font-bold text-blue">{{ $domain }}</span>
                <x-icon name="external-link" class="size-[18px] text-blue" />
            </span>
        </div>
        <footer class="flex items-center justify-between gap-2">
            <span class="inline-flex items-center gap-1.5 rounded-full bg-success-wash py-[7px] pl-2.5 pr-3 text-xs font-bold text-success ring-1 ring-inset ring-success/20">
                <x-icon name="shield-check" class="size-[15px]" /> Resmi · Terverifikasi
            </span>
            <span class="text-xs font-medium text-slate">{{ $checked }}</span>
        </footer>
    @endif
</article>
