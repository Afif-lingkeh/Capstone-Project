{{-- resources/views/components/audit-notice.blade.php --}}
{{-- Desain "Audit Notice": wajib terlihat di setiap halaman yang membuka data konseling. --}}
@props(['at' => null])

<div {{ $attributes->class('flex gap-3.5 rounded-2xl bg-sand px-5 py-4 ring-1 ring-inset ring-gold/25') }}>
    <x-icon name="scroll-text" class="text-gold-ink" />
    <div class="flex flex-1 flex-col gap-0.5 text-sm">
        <p class="font-bold">Akses ini tercatat.</p>
        <p class="leading-[1.5] text-slate">Dibuka oleh {{ auth()->user()->name }} · {{ ($at ?? now())->translatedFormat('j M Y H.i') }} WIB. Admin tidak dapat membuka catatan ini.</p>
    </div>
</div>
