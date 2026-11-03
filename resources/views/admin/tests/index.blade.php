{{-- resources/views/admin/tests/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">Bank Soal</x-slot>

    <x-page-intro title="Bank soal tes potensi" description="Soal contoh disempurnakan guru/psikolog. Tes baru tampil ke santri setelah diaktifkan.">
        <x-slot name="actions">
            <a href="{{ route('admin.tests.create') }}" class="btn-primary"><x-icon name="plus" class="size-[18px]" />Buat tes</a>
        </x-slot>
    </x-page-intro>

    @if ($tests->isEmpty())
        <x-empty-state icon="clipboard-list" title="Belum ada tes" />
    @else
        <div class="grid gap-6 md:grid-cols-2">
            @foreach ($tests as $test)
                <article class="relative flex flex-col gap-3 rounded-3xl bg-white p-6 ring-1 ring-inset ring-hairline hover:bg-cloud">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-[11px] font-bold uppercase tracking-[0.13em] text-teal-ink">{{ $test->dimension->label() }}</p>
                        <x-badge :tone="$test->is_active ? 'success' : 'neutral'">{{ $test->is_active ? 'Aktif' : 'Nonaktif' }}</x-badge>
                    </div>
                    <h2 class="text-lg font-extrabold"><a href="{{ route('admin.tests.edit', $test) }}" class="after:absolute after:inset-0">{{ $test->title }}</a></h2>
                    <p class="text-sm text-slate">{{ $test->questions_count }} soal · {{ $test->submitted_count }} kali dikumpulkan · ±{{ $test->estimated_minutes }} menit</p>
                </article>
            @endforeach
        </div>
    @endif
</x-app-layout>
