{{-- resources/views/pagination/peduli.blade.php --}}
{{-- Desain "Pagination": kotak 40px, halaman aktif navy. Dipakai lewat ->links('pagination.peduli'). --}}
@if ($paginator->hasPages())
    @php
        $box = 'flex size-10 items-center justify-center rounded-xl text-sm font-bold';
        $idle = $box.' text-ink ring-1 ring-inset ring-hairline transition-colors duration-150 hover:bg-pebble';
    @endphp
    <nav aria-label="Navigasi halaman" class="flex flex-col gap-3 pt-4 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-sm font-semibold text-slate">Halaman {{ $paginator->currentPage() }} dari {{ $paginator->lastPage() }}</p>

        <ul class="flex flex-wrap items-center gap-1.5">
            <li>
                @if ($paginator->onFirstPage())
                    <span class="{{ $box }} text-mist ring-1 ring-inset ring-hairline" aria-disabled="true"><x-icon name="chevron-left" class="size-[18px]" /><span class="sr-only">Sebelumnya</span></span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="{{ $idle }}"><x-icon name="chevron-left" class="size-[18px]" /><span class="sr-only">Sebelumnya</span></a>
                @endif
            </li>

            @foreach ($elements as $element)
                @if (is_string($element))
                    <li><span class="{{ $box }} text-slate" aria-hidden="true">…</span></li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        <li>
                            @if ($page == $paginator->currentPage())
                                <span aria-current="page" class="{{ $box }} bg-ink text-white">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="{{ $idle }}"><span class="sr-only">Halaman </span>{{ $page }}</a>
                            @endif
                        </li>
                    @endforeach
                @endif
            @endforeach

            <li>
                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="{{ $idle }}"><x-icon name="chevron-right" class="size-[18px]" /><span class="sr-only">Berikutnya</span></a>
                @else
                    <span class="{{ $box }} text-mist ring-1 ring-inset ring-hairline" aria-disabled="true"><x-icon name="chevron-right" class="size-[18px]" /><span class="sr-only">Berikutnya</span></span>
                @endif
            </li>
        </ul>
    </nav>
@endif
