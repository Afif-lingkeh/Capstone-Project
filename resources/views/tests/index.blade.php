{{-- resources/views/tests/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">Tes Potensi</x-slot>

    <x-page-intro eyebrow="Tes potensi & pemetaan jalur" title="Kenali kekuatan yang Allah titipkan"
                  description="Jawab dengan jujur, tidak ada jawaban benar atau salah. Hasilnya berupa profil empat jalur, bukan satu label." />

    <div class="flex flex-col gap-6 xl:flex-row xl:items-start">
        <section aria-labelledby="profil" class="flex flex-col gap-6 rounded-3xl bg-white p-8 ring-1 ring-inset ring-hairline xl:w-[520px]">
            <div class="flex flex-col gap-1.5">
                <p class="text-xs font-bold uppercase tracking-[0.13em] text-teal-ink">Profil jalur kamu</p>
                <h2 id="profil" class="text-2xl font-extrabold tracking-[-0.02em]">{{ $headline ?? 'Belum ada hasil' }}</h2>
                <p class="text-sm leading-[1.5] text-slate">
                    {{ $profile ? 'Skor menunjukkan kecocokan, bukan batasan. Diskusikan dengan pembimbingmu.' : 'Selesaikan minimal satu tes untuk melihat profilmu di sini.' }}
                </p>
            </div>
            @if ($profile)
                <x-score-bars :scores="$profile" />
                <div class="flex flex-col gap-3 sm:flex-row">
                    @if (Route::has('guidance.index'))
                        <a href="{{ route('guidance.index') }}" class="btn-primary flex-1">Diskusikan dengan pembimbing</a>
                    @endif
                    @if ($history->isNotEmpty())
                        <a href="{{ route('attempts.result', $history->first()) }}" class="btn-secondary">Lihat jurusan</a>
                    @endif
                </div>
            @endif
        </section>

        <div class="flex flex-1 flex-col gap-4">
            @forelse ($tests as $test)
                @php
                    $mine = $attempts->get($test->id, collect());
                    $inProgress = $mine->firstWhere('submitted_at', null);
                    $done = $mine->whereNotNull('submitted_at')->first();
                    $percent = $inProgress && $test->questions_count ? (int) round($inProgress->answers_count / $test->questions_count * 100) : null;
                @endphp
                <article class="flex flex-col gap-5 rounded-3xl bg-white p-6 ring-1 ring-inset ring-hairline sm:flex-row sm:items-center">
                    @if ($percent !== null)
                        <x-progress-ring :percent="$percent" />
                    @else
                        <span class="flex size-14 shrink-0 items-center justify-center rounded-2xl {{ $done ? 'bg-success-wash text-success' : 'bg-warning-wash text-warning' }}"><x-icon :name="$done ? 'circle-check' : 'clipboard-list'" class="size-6" /></span>
                    @endif
                    <div class="flex flex-1 flex-col gap-1.5">
                        <p class="text-[11px] font-bold uppercase tracking-[0.13em] text-teal-ink">{{ $test->dimension->label() }} · {{ $test->questions_count }} soal · ±{{ $test->estimated_minutes }} menit</p>
                        <h2 class="text-xl font-extrabold">{{ $test->title }}</h2>
                        <p class="text-sm leading-[1.5] text-slate">
                            @if ($inProgress)
                                {{ $inProgress->answers_count }} dari {{ $test->questions_count }} soal terjawab. Lanjutkan kapan saja.
                            @elseif ($done)
                                Selesai {{ $done->submitted_at->translatedFormat('j F Y') }}. Kamu boleh mengulang untuk melihat perkembangan.
                            @else
                                {{ $test->description }}
                            @endif
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        @if ($done && ! $inProgress)
                            <a href="{{ route('attempts.result', $done) }}" class="btn-secondary py-3">Lihat hasil</a>
                        @endif
                        <form method="POST" action="{{ route('tests.start', $test) }}">
                            @csrf
                            <button type="submit" class="btn-primary py-3">{{ $inProgress ? 'Lanjutkan' : ($done ? 'Kerjakan ulang' : 'Mulai') }} <x-icon name="arrow-right" class="size-[18px]" /></button>
                        </form>
                    </div>
                </article>
            @empty
                <x-empty-state icon="clipboard-list" title="Belum ada tes yang dibuka">Tes potensi akan muncul di sini setelah disiapkan pesantren.</x-empty-state>
            @endforelse

            @if ($history->isNotEmpty())
                <x-card title="Riwayat perkembangan" :padded="false">
                    <ul class="divide-y divide-hairline">
                        @foreach ($history as $attempt)
                            <li class="flex flex-col gap-3 px-6 py-4 sm:flex-row sm:items-center sm:gap-6">
                                <a href="{{ route('attempts.result', $attempt) }}" class="flex flex-col sm:w-56">
                                    <span class="text-sm font-bold hover:underline">{{ $attempt->test->title }}</span>
                                    <span class="text-xs font-medium text-slate">{{ $attempt->submitted_at->translatedFormat('j M Y') }}</span>
                                </a>
                                <x-score-bars :scores="$attempt->scores" compact class="flex-1 sm:grid sm:grid-cols-2 sm:gap-x-6" />
                            </li>
                        @endforeach
                    </ul>
                </x-card>
            @endif
        </div>
    </div>
</x-app-layout>
