{{-- resources/views/admin/partners/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">Mitra Luar Negeri</x-slot>

    <x-page-intro title="Mitra resmi pesantren" description="Lembaga yang bekerja sama untuk program kerja & magang luar negeri.">
        <x-slot name="actions">
            <a href="{{ route('admin.programs.index') }}" class="btn-ghost"><x-icon name="arrow-left" class="size-[18px]" />Program</a>
            <a href="{{ route('admin.partners.create') }}" class="btn-primary"><x-icon name="plus" class="size-[18px]" />Tambah mitra</a>
        </x-slot>
    </x-page-intro>

    <div class="overflow-x-auto rounded-3xl bg-white ring-1 ring-hairline">
        <table class="min-w-full divide-y divide-hairline text-sm">
            <thead class="bg-cloud text-left text-[11px] font-bold uppercase tracking-[0.12em] text-slate">
                <tr><th class="px-5 py-3">Mitra</th><th class="px-5 py-3">Negara</th><th class="px-5 py-3">Program</th><th class="px-5 py-3 text-right">Aksi</th></tr>
            </thead>
            <tbody class="divide-y divide-hairline">
                @forelse ($partners as $partner)
                    <tr class="hover:bg-cloud">
                        <td class="px-5 py-4"><p class="font-bold">{{ $partner->name }}</p>@if ($partner->website)<p class="font-mono text-xs text-slate">{{ parse_url($partner->website, PHP_URL_HOST) }}</p>@endif</td>
                        <td class="px-5 py-4">{{ $partner->country->name }}</td>
                        <td class="px-5 py-4">{{ $partner->programs_count }}</td>
                        <td class="px-5 py-4 text-right"><a href="{{ route('admin.partners.edit', $partner) }}" class="font-bold text-blue hover:underline">Ubah<span class="sr-only"> {{ $partner->name }}</span></a></td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-5 py-10 text-center text-slate">Belum ada mitra.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $partners->links('pagination.peduli') }}</div>
</x-app-layout>
