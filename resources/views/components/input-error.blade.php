@props(['messages'])

@if ($messages)
    <ul {{ $attributes->merge(['class' => 'space-y-1 text-sm font-semibold text-danger-ink']) }}>
        @foreach ((array) $messages as $message)
            <li class="flex items-start gap-1.5"><x-icon name="triangle-alert" class="mt-0.5 size-4" />{{ $message }}</li>
        @endforeach
    </ul>
@endif
