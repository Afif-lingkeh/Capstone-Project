{{-- resources/views/components/star.blade.php --}}
{{-- Desain "Star Ornament": bintang segi delapan (dua persegi, satu diputar 45°). Dekorasi saja. --}}
<svg viewBox="0 0 16 16" {{ $attributes->merge(['class' => 'size-4']) }} aria-hidden="true" focusable="false">
    <rect x="2.34" y="2.34" width="11.32" height="11.32" fill="currentColor" />
    <path d="M8 0 16 8 8 16 0 8Z" fill="currentColor" />
    <circle cx="8" cy="8" r="2.5" fill="#fff" />
</svg>
