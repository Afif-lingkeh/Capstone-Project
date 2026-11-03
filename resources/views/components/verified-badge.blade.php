{{-- resources/views/components/verified-badge.blade.php --}}
{{-- Desain "Badge/Official". --}}
@props(['link'])

<span {{ $attributes->class('inline-flex items-center gap-1.5 rounded-full bg-success-wash py-[7px] pl-2.5 pr-3 text-xs font-bold text-success ring-1 ring-inset ring-success/20') }}
      title="Diverifikasi tim pesantren{{ $link->verified_at ? ' pada '.$link->verified_at->translatedFormat('j F Y') : '' }}">
    <x-icon name="shield-check" class="size-[15px]" />
    Resmi · Terverifikasi
</span>
