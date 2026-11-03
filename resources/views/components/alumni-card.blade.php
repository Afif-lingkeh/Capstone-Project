{{-- resources/views/components/alumni-card.blade.php --}}
{{-- Desain "Card/Alumni". Nama ditampilkan singkat (nama depan + inisial) dan tanpa nomor HP. --}}
@props(['profile', 'link' => true])

<article {{ $attributes->class('relative flex flex-col gap-4 rounded-3xl bg-white p-6 ring-1 ring-inset ring-hairline') }}>
    <div class="flex items-center gap-3.5">
        <span class="flex size-12 shrink-0 items-center justify-center rounded-full bg-gold-wash text-base font-bold text-gold-ink">{{ $profile->initials() }}</span>
        <div class="flex min-w-0 flex-col gap-0.5">
            <h3 class="text-base font-extrabold">
                @if ($link)<a href="{{ route('alumni.show', $profile) }}" class="after:absolute after:inset-0 hover:underline">{{ $profile->publicName() }}</a>@else{{ $profile->publicName() }}@endif
            </h3>
            <p class="text-[13px] font-medium text-slate">Angkatan {{ $profile->graduation_year }}@if ($profile->location()) · {{ $profile->location() }}@endif</p>
        </div>
    </div>
    <p class="flex-1 text-sm leading-[1.57] text-slate">{{ $profile->bio ?: $profile->activityLabel().' di '.$profile->institution_name.($profile->field ? ' · '.$profile->field : '') }}</p>
    <div class="flex flex-wrap gap-2">
        @if ($profile->isAbroad())<span class="rounded-lg bg-teal-wash px-2.5 py-[5px] text-xs font-bold text-teal-ink">Luar Negeri</span>@endif
        @if ($profile->pathway)<span class="rounded-lg bg-blue-wash px-2.5 py-[5px] text-xs font-bold text-cobalt">{{ $profile->pathway->label() }}</span>@endif
        <span class="rounded-lg bg-pebble px-2.5 py-[5px] text-xs font-bold">{{ $profile->activityLabel() }}</span>
    </div>
    {{ $slot }}
</article>
