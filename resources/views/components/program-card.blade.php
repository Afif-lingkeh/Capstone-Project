{{-- resources/views/components/program-card.blade.php --}}
{{-- Desain "Card/Program". --}}
@props(['program', 'bookmarked' => false, 'interested' => false])

@php($open = $program->acceptsInterest())
<article {{ $attributes->class('relative flex flex-col gap-4 rounded-3xl bg-white p-6 ring-1 ring-inset ring-hairline') }}>
    <div class="flex items-center justify-between gap-3">
        <span class="rounded-lg bg-pebble px-2.5 py-[5px] text-xs font-bold tracking-[0.025em]">{{ $program->country->flag() }} {{ $program->country->name }}</span>
        @if ($interested)
            <x-badge tone="success">Kamu berminat</x-badge>
        @else
            <x-badge :tone="$open ? 'blue' : 'neutral'">{{ $open ? 'Buka' : 'Tutup' }}</x-badge>
        @endif
    </div>

    <div class="flex flex-col gap-1">
        <h3 class="text-lg font-extrabold leading-[1.28]">
            <a href="{{ route('programs.show', $program) }}" class="after:absolute after:inset-0 hover:underline">{{ $program->title }}</a>
        </h3>
        <p class="text-[13px] font-medium text-slate">{{ $program->type->label() }} · Mitra: {{ $program->partner->name }}</p>
    </div>

    <ul class="flex flex-1 flex-col gap-2 border-y border-hairline py-3.5 text-[13px] font-medium">
        <li class="flex items-center gap-2.5"><x-icon name="calendar" class="size-4 text-slate" />{{ $program->deadline ? 'Tenggat '.$program->deadline->translatedFormat('j F Y') : 'Tenggat belum ditentukan' }}</li>
        <li class="flex items-start gap-2.5"><x-icon name="wallet" class="mt-px size-4 text-slate" /><span class="line-clamp-1">{{ Str::of($program->costs)->before("\n") }}</span></li>
        @if ($program->min_education)
            <li class="flex items-center gap-2.5"><x-icon name="graduation-cap" class="size-4 text-slate" />{{ $program->min_education }}</li>
        @endif
    </ul>

    <div class="relative z-10 flex gap-2.5">
        <form method="POST" action="{{ route('programs.bookmark', $program) }}">
            @csrf
            <button type="submit" aria-pressed="{{ $bookmarked ? 'true' : 'false' }}"
                    class="flex size-12 items-center justify-center rounded-2xl ring-1 ring-inset ring-hairline hover:bg-pebble {{ $bookmarked ? 'text-blue' : 'text-ink' }}">
                <x-icon :name="$bookmarked ? 'bookmark-check' : 'bookmark'" />
                <span class="sr-only">{{ $bookmarked ? 'Hapus dari simpanan' : 'Simpan program' }}: {{ $program->title }}</span>
            </button>
        </form>
        <a href="{{ route('programs.show', $program) }}#minat" class="btn-primary flex-1 py-3">
            {{ $interested ? 'Lihat detail' : ($open ? 'Saya berminat' : 'Lihat detail') }}
        </a>
    </div>
</article>
