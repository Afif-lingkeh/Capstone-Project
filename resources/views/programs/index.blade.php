{{-- resources/views/programs/index.blade.php --}}
@php($filters = request()->only(['q', 'country', 'partner', 'type', 'status', 'saved']))
<x-app-layout>
    <x-slot name="header">Kerja & Magang</x-slot>

    <x-page-intro eyebrow="Kerja & magang luar negeri" title="Program resmi dari mitra pesantren"
                  description="Semua program di sini bekerja sama langsung dengan pesantren. Jangan mendaftar lewat calo atau pihak yang mengatasnamakan pesantren." />

    <form method="GET" class="mb-4 grid gap-3 rounded-3xl bg-white p-4 ring-1 ring-inset ring-hairline sm:grid-cols-2 lg:grid-cols-5">
        <div class="sm:col-span-2">
            <label for="q" class="sr-only">Cari program</label>
            <x-text-input id="q" name="q" type="search" :value="request('q')" placeholder="Cari nama program" />
        </div>
        <div>
            <label for="country" class="sr-only">Negara</label>
            <x-select-input id="country" name="country">
                <option value="">Semua negara</option>
                @foreach ($countries as $country)
                    <option value="{{ $country->id }}" @selected(request()->integer('country') === $country->id)>{{ $country->name }}</option>
                @endforeach
            </x-select-input>
        </div>
        <div>
            <label for="partner" class="sr-only">Mitra</label>
            <x-select-input id="partner" name="partner">
                <option value="">Semua mitra</option>
                @foreach ($partners as $partner)
                    <option value="{{ $partner->id }}" @selected(request()->integer('partner') === $partner->id)>{{ $partner->name }}</option>
                @endforeach
            </x-select-input>
        </div>
        <button type="submit" class="btn-primary py-3 shadow-none">Terapkan</button>
        @foreach (['type', 'status', 'saved'] as $keep)
            @if (request()->filled($keep))<input type="hidden" name="{{ $keep }}" value="{{ request($keep) }}">@endif
        @endforeach
    </form>

    <nav aria-label="Status program" class="-mx-5 mb-6 flex gap-2.5 overflow-x-auto px-5 pb-1 lg:mx-0 lg:px-0">
        <x-chip :href="route('programs.index', Arr::except($filters, ['status', 'saved']))" :active="! request('status') && ! request('saved')">Semua</x-chip>
        <x-chip :href="route('programs.index', [...Arr::except($filters, 'saved'), 'status' => 'open'])" :active="request('status') === 'open'">Masih buka</x-chip>
        <x-chip :href="route('programs.index', [...Arr::except($filters, 'saved'), 'status' => 'closed'])" :active="request('status') === 'closed'">Sudah tutup</x-chip>
        <x-chip :href="route('programs.index', [...Arr::except($filters, 'status'), 'saved' => 1])" :active="request()->boolean('saved')">
            <x-icon name="bookmark" class="size-4" /> Disimpan
        </x-chip>
    </nav>

    @if ($programs->isEmpty())
        <x-empty-state icon="briefcase-business" title="Belum ada program yang cocok">
            Coba ubah filter. Program baru dari mitra akan muncul di sini setelah diperiksa pesantren.
        </x-empty-state>
    @else
        <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($programs as $program)
                <x-program-card :program="$program" :bookmarked="in_array($program->id, $bookmarkedIds)" :interested="in_array($program->id, $interestIds)" />
            @endforeach
        </div>
        <div class="mt-6">{{ $programs->links('pagination.peduli') }}</div>
    @endif
</x-app-layout>
