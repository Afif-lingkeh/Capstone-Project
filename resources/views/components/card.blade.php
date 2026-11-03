{{-- resources/views/components/card.blade.php --}}
{{-- Kartu putih standar. Judul + slot "actions" opsional (desain: Head dengan garis bawah). --}}
@props(['title' => null, 'padded' => true])

{{-- Ring di luar (bukan inset) agar tidak tertutup latar baris/footer di dalam kartu. --}}
<section {{ $attributes->class('overflow-hidden rounded-3xl bg-white ring-1 ring-hairline') }}>
    @if ($title || isset($actions))
        <header class="flex min-h-[64px] flex-wrap items-center justify-between gap-3 border-b border-hairline px-6 py-4">
            <h2 class="text-lg font-extrabold">{{ $title }}</h2>
            @isset($actions)<div class="flex flex-wrap items-center gap-2">{{ $actions }}</div>@endisset
        </header>
    @endif
    <div @class(['p-6' => $padded])>{{ $slot }}</div>
</section>
