{{-- resources/views/components/quick-links.blade.php --}}
@props(['items' => []])

@php($links = collect($items)->reject(fn ($item) => str_ends_with($item['route'], 'dashboard')))

@if ($links->isNotEmpty())
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($links as $link)
            <a href="{{ route($link['route']) }}" class="block rounded-lg bg-white p-4 shadow-sm ring-1 ring-gray-200 transition hover:ring-emerald-500">
                <p class="font-medium text-gray-900">{{ $link['label'] }}</p>
                @if ($link['description'])
                    <p class="mt-1 text-sm text-gray-600">{{ $link['description'] }}</p>
                @endif
            </a>
        @endforeach
    </div>
@else
    <p class="rounded-lg border border-dashed border-gray-300 bg-white p-6 text-sm text-gray-600">
        Menu modul akan muncul di sini setelah modulnya aktif.
    </p>
@endif
