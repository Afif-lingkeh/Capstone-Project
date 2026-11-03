{{-- resources/views/components/stepper.blade.php --}}
{{-- Desain "Stepper/Request". $steps: list of ['label', 'date' => ?string, 'state' => done|current|todo]. --}}
@props(['steps'])

<ol {{ $attributes->class('flex flex-col gap-4 rounded-3xl bg-white px-6 py-6 ring-1 ring-inset ring-hairline md:flex-row md:items-center md:gap-3 md:px-8') }}>
    @foreach ($steps as $i => $step)
        <li class="flex items-center gap-3 md:contents">
            <span class="flex items-center gap-3">
                <span @class([
                    'flex size-9 shrink-0 items-center justify-center rounded-full text-sm font-extrabold',
                    'bg-success text-white' => $step['state'] === 'done',
                    'bg-blue text-white' => $step['state'] === 'current',
                    'bg-white text-slate ring-2 ring-inset ring-hairline' => $step['state'] === 'todo',
                ])>
                    @if ($step['state'] === 'done')<x-icon name="check" class="size-4" />@else{{ $i + 1 }}@endif
                </span>
                <span class="flex flex-col gap-0.5">
                    <span @class(['text-sm font-bold', 'text-slate' => $step['state'] === 'todo'])>{{ $step['label'] }}<span class="sr-only">, {{ ['done' => 'selesai', 'current' => 'sedang berlangsung', 'todo' => 'belum'][$step['state']] }}</span></span>
                    <span class="text-xs font-medium text-slate">{{ $step['date'] ?? '—' }}</span>
                </span>
            </span>
            @unless ($loop->last)
                <span aria-hidden="true" @class(['hidden h-0.5 flex-1 md:block', 'bg-success' => $step['state'] === 'done', 'bg-hairline' => $step['state'] !== 'done'])></span>
            @endunless
        </li>
    @endforeach
</ol>
