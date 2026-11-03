{{-- resources/views/components/brand.blade.php --}}
{{-- Desain "Logo/Horizontal" (inverse = di atas latar gelap). --}}
@props(['inverse' => false])

<span {{ $attributes->class('flex items-center gap-2.5') }}>
    <x-application-logo />
    <span class="flex flex-col gap-[3px]">
        <span @class(['text-xl font-extrabold leading-none tracking-[0.06em]', 'text-white' => $inverse, 'text-ink' => ! $inverse])>PEDULI</span>
        <span @class(['text-[8px] font-bold leading-none tracking-[0.2em]', 'text-mist' => $inverse, 'text-slate' => ! $inverse])>SMART CAREER CENTER</span>
    </span>
</span>
