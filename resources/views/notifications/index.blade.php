{{-- resources/views/notifications/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">Notifikasi</x-slot>

    <div class="mx-auto max-w-3xl">
        <x-card title="Semua notifikasi" :padded="false">
            <x-slot name="actions">
                @if (auth()->user()->unreadNotifications()->exists())
                    <form method="POST" action="{{ route('notifications.read-all') }}">
                        @csrf
                        <button type="submit" class="btn-ghost px-3 py-2 text-sm text-blue">Tandai semua dibaca</button>
                    </form>
                @endif
            </x-slot>

            <ul class="divide-y divide-hairline">
                @forelse ($notifications as $notification)
                    @php($data = $notification->data)
                    <li>
                        <a href="{{ route('notifications.show', $notification->id) }}" @class(['flex gap-4 px-6 py-4 transition-colors duration-150 hover:bg-cloud', 'bg-blue-wash/40' => ! $notification->read_at])>
                            <span @class([
                                'flex size-10 shrink-0 items-center justify-center rounded-xl',
                                'bg-danger-wash text-danger' => ($data['tone'] ?? null) === 'danger',
                                'bg-blue-wash text-blue' => ($data['tone'] ?? null) !== 'danger',
                            ])><x-icon :name="$data['icon'] ?? 'bell'" class="size-[18px]" /></span>
                            <span class="flex min-w-0 flex-1 flex-col gap-1">
                                <span class="flex items-start justify-between gap-3">
                                    <span class="text-[15px] font-bold">{{ $data['title'] ?? 'Notifikasi' }}</span>
                                    @unless ($notification->read_at)<x-badge tone="blue">Baru</x-badge>@endunless
                                </span>
                                @if (! empty($data['body']))<span class="text-sm leading-[1.57] text-slate">{{ $data['body'] }}</span>@endif
                                <span class="text-xs font-medium text-slate">{{ $notification->created_at->diffForHumans() }}</span>
                            </span>
                        </a>
                    </li>
                @empty
                    <li class="p-6"><x-empty-state icon="bell" title="Belum ada notifikasi" class="border-0" /></li>
                @endforelse
            </ul>
        </x-card>

        <div class="mt-4">{{ $notifications->links('pagination.peduli') }}</div>
    </div>
</x-app-layout>
