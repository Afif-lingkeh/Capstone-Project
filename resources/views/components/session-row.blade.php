{{-- resources/views/components/session-row.blade.php --}}
{{-- Desain "List/Session": tile tanggal + judul + keterangan + status. Slot "actions" opsional. --}}
@props(['date', 'title', 'meta' => null, 'status' => null, 'muted' => false])

<li {{ $attributes->class('flex flex-col gap-4 px-6 py-[18px] sm:flex-row sm:items-center sm:gap-5') }}>
    <div class="flex flex-1 items-center gap-5">
        <x-date-tile :date="$date" :muted="$muted" />
        <div class="flex min-w-0 flex-1 flex-col gap-1">
            <p class="text-base font-bold">{{ $title }}</p>
            @if ($meta)<p class="text-[13px] font-medium text-slate">{{ $meta }}</p>@endif
            {{ $slot }}
        </div>
        @if ($status)<x-status-badge :status="$status" class="hidden sm:inline-flex" />@endif
    </div>
    @if ($status)<x-status-badge :status="$status" class="sm:hidden" />@endif
    @isset($actions)<div class="flex flex-wrap gap-2">{{ $actions }}</div>@endisset
</li>
