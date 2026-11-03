{{-- resources/views/components/crisis-banner.blade.php --}}
{{-- Desain "Banner/Crisis": empatik, tanpa menghakimi, dengan jalan keluar yang jelas. --}}
@props(['onDuty' => []])

<section role="alert" {{ $attributes->class('flex flex-col gap-4 rounded-3xl bg-danger-wash p-7 ring-[1.5px] ring-inset ring-danger-line') }}>
    <div class="flex items-center gap-3.5">
        <span class="flex size-12 items-center justify-center rounded-[14px] bg-white"><x-icon name="hand-heart" class="size-6 text-danger" /></span>
        <h2 class="text-xl font-extrabold text-danger-ink">Kamu tidak sendirian.</h2>
    </div>
    <p class="text-[15px] font-medium leading-6">
        Sepertinya kamu sedang melalui masa yang berat. Konselor {{ $onDuty ? 'piket ('.implode(', ', $onDuty).')' : 'pesantren' }} sudah diberi tahu dan akan segera menemuimu.
        Kalau kamu merasa tidak aman sekarang, segera temui ustadz/ustadzah atau musyrif/musyrifah terdekat.
    </p>
    <div class="flex flex-col gap-3 sm:flex-row">
        @if (config('peduli.crisis_hotline'))
            <a href="tel:{{ preg_replace('/[^0-9+]/', '', config('peduli.crisis_hotline')) }}" class="btn bg-danger text-white hover:bg-danger-ink"><x-icon name="life-buoy" class="size-[18px]" />Layanan krisis {{ config('peduli.crisis_hotline') }}</a>
        @else
            <span class="btn bg-white text-danger-ink ring-[1.5px] ring-inset ring-danger-line">Layanan krisis [NOMOR — diisi pesantren]</span>
        @endif
    </div>
</section>
