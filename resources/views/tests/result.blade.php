{{-- resources/views/tests/result.blade.php --}}
<x-app-layout>
    <x-slot name="header">Hasil Tes Potensi</x-slot>
    <x-slot name="subtitle">{{ $attempt->test->title }} · {{ $attempt->submitted_at->translatedFormat('j F Y') }}</x-slot>

    <a href="{{ route('tests.index') }}" class="mb-5 flex w-fit items-center gap-1.5 py-1 text-sm font-bold text-blue hover:underline"><x-icon name="arrow-left" class="size-4" /> Semua tes</a>

    <div class="flex flex-col gap-6 xl:flex-row xl:items-start">
        <section aria-labelledby="profil" class="flex flex-col gap-6 rounded-3xl bg-white p-8 ring-1 ring-inset ring-hairline xl:w-[520px]">
            <div class="flex flex-col gap-1.5">
                <p class="text-xs font-bold uppercase tracking-[0.13em] text-teal-ink">Profil jalur kamu</p>
                <h1 id="profil" class="text-2xl font-extrabold tracking-[-0.02em]">{{ $headline }}</h1>
                <p class="text-sm leading-[1.5] text-slate">Skor menunjukkan kecocokan, bukan batasan. Banyak jurusan memadukan beberapa jalur sekaligus.</p>
            </div>
            <x-score-bars :scores="$attempt->scores" />
            <div class="flex flex-col gap-3 sm:flex-row">
                <a href="{{ route('guidance.index') }}" class="btn-primary flex-1">Diskusikan dengan pembimbing</a>
                <a href="#jurusan" class="btn-secondary">Lihat jurusan</a>
            </div>
        </section>

        <div class="flex flex-1 flex-col gap-6">
            <section id="jurusan" aria-labelledby="judul-jurusan" class="flex scroll-mt-24 flex-col gap-4">
                <h2 id="judul-jurusan" class="text-lg font-extrabold">Jurusan yang bisa kamu jelajahi</h2>
                @forelse ($top as $pathway)
                    <x-card :title="$pathway->label().' · '.$pathway->description()" :padded="false">
                        <ul class="divide-y divide-hairline">
                            @forelse ($majors[$pathway->value] ?? [] as $major)
                                <li class="flex flex-col gap-1 px-6 py-4">
                                    <p class="text-[15px] font-bold">{{ $major->name }}</p>
                                    @if ($major->description)<p class="text-sm leading-[1.5] text-slate">{{ $major->description }}</p>@endif
                                    @if ($major->career_prospects)<p class="text-[13px] font-semibold text-teal-ink">Prospek: {{ $major->career_prospects }}</p>@endif
                                </li>
                            @empty
                                <li class="px-6 py-5 text-sm text-slate">Daftar jurusan untuk jalur ini sedang disiapkan pesantren.</li>
                            @endforelse
                        </ul>
                    </x-card>
                @empty
                    <x-empty-state icon="graduation-cap" title="Belum ada rekomendasi" />
                @endforelse
            </section>

            <div class="grid gap-4 sm:grid-cols-2">
                <a href="{{ route('links.index') }}" class="flex flex-col gap-2 rounded-3xl bg-ink p-6 text-white hover:bg-ink-deep">
                    <x-icon name="shield-check" class="text-teal-light" />
                    <span class="text-base font-extrabold">Cari kampusnya di direktori resmi</span>
                    <span class="text-sm text-hairline">Pastikan situs pendaftarannya asli.</span>
                </a>
                <a href="{{ route('articles.index') }}" class="flex flex-col gap-2 rounded-3xl bg-white p-6 ring-1 ring-inset ring-hairline hover:bg-cloud">
                    <x-icon name="graduation-cap" class="text-blue" />
                    <span class="text-base font-extrabold">Pelajari jalur masuknya</span>
                    <span class="text-sm text-slate">SNBP, SNBT, mandiri, dan beasiswa.</span>
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
