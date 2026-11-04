{{-- resources/views/santri/dashboard.blade.php --}}
{{-- Desain "03 Santri · Beranda". --}}
@php
    $quick = [
        ['Kerja & Magang', $counts['programs'] ? $counts['programs'].' program dibuka, lihat persyaratannya' : 'Program mitra luar negeri', 'Lihat program', 'programs.index', 'briefcase-business', 'bg-teal-wash text-teal-ink'],
        ['Info Kampus', $counts['latestArticle'] ? Str::limit($counts['latestArticle'], 40) : 'Panduan SNBP, SNBT, dan beasiswa', 'Baca panduan', 'articles.index', 'graduation-cap', 'bg-blue-wash text-blue'],
        ['Teman bercerita', 'Ada yang ingin diceritakan?', 'Mulai bercerita', 'counseling.index', 'heart-handshake', 'bg-violet-wash text-violet-ink'],
        ['Konsultasi Alumni', $counts['alumni'] ? $counts['alumni'].' alumni siap berbagi' : 'Jejaring alumni pesantren', 'Cari alumni', 'alumni.index', 'users', 'bg-gold-wash text-gold-ink'],
    ];
    $percent = $inProgress && $inProgress->test->questions_count ? (int) round($inProgress->answers_count / $inProgress->test->questions_count * 100) : null;
@endphp
<x-app-layout>
    <x-slot name="header">Beranda</x-slot>

    <div class="flex flex-col gap-7">
        <section class="relative flex flex-col gap-6 overflow-hidden rounded-[28px] bg-gradient-to-r from-ink to-cobalt px-7 py-8 text-white lg:flex-row lg:items-center lg:px-9">
            <div aria-hidden="true" class="pointer-events-none absolute -top-[120px] left-[60%] size-[360px] rounded-full bg-teal-light/25 blur-[50px]"></div>
            <x-star class="absolute left-[50%] top-6 hidden text-gold-light opacity-50 lg:block [&_circle]:fill-ink" />
            <div class="relative flex flex-1 flex-col gap-2.5">
                <p class="text-xs font-bold uppercase tracking-[0.15em] text-teal-light">Assalamu’alaikum · semoga harimu berkah</p>
                <h1 class="text-[28px] font-extrabold tracking-[-0.02em] lg:text-4xl">{{ auth()->user()->name }}</h1>
                <div class="flex flex-wrap gap-2">
                    @if ($profile)
                        <span class="rounded-full bg-white/10 px-3 py-1.5 text-xs font-semibold">NIS {{ $profile->nis }}</span>
                        <span class="rounded-full bg-white/10 px-3 py-1.5 text-xs font-semibold">{{ $profile->level->label() }} · Kelas {{ $profile->class_name }}</span>
                    @else
                        <span class="rounded-full bg-white/10 px-3 py-1.5 text-xs font-semibold">Data santri belum lengkap, hubungi admin pesantren</span>
                    @endif
                    @if (auth()->user()->gender)<span class="rounded-full bg-white/10 px-3 py-1.5 text-xs font-semibold">{{ auth()->user()->gender === \App\Enums\Gender::Male ? 'Putra' : 'Putri' }}</span>@endif
                </div>
            </div>
            @if ($inProgress)
                <a href="{{ route('attempts.show', $inProgress) }}" class="btn relative w-fit rounded-full bg-white text-ink hover:bg-blue-wash">Lanjutkan tes potensi <x-icon name="arrow-right" class="size-[18px]" /></a>
            @elseif ($nextTest)
                <form method="POST" action="{{ route('tests.start', $nextTest) }}" class="relative">@csrf<button type="submit" class="btn w-fit rounded-full bg-white text-ink hover:bg-blue-wash">Mulai tes potensi <x-icon name="arrow-right" class="size-[18px]" /></button></form>
            @endif
        </section>

        <section aria-labelledby="akses-cepat" class="flex flex-col gap-3.5">
            <h2 id="akses-cepat" class="text-lg font-extrabold">Akses cepat</h2>
            <ul class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ($quick as [$title, $desc, $cta, $route, $icon, $tile])
                    @if (Route::has($route))
                        <li class="relative flex flex-col gap-3 rounded-3xl bg-white p-[22px] ring-1 ring-inset ring-hairline hover:bg-cloud">
                            <span class="flex size-11 items-center justify-center rounded-[14px] {{ $tile }}"><x-icon :name="$icon" class="size-[22px]" /></span>
                            <h3 class="text-base font-extrabold">{{ $title }}</h3>
                            <p class="flex-1 text-sm leading-[1.5] text-slate">{{ $desc }}</p>
                            <a href="{{ route($route) }}" class="flex w-fit items-center gap-1.5 text-sm font-bold text-blue after:absolute after:inset-0">{{ $cta }} <x-icon name="arrow-right" class="size-4" /></a>
                        </li>
                    @endif
                @endforeach
            </ul>
        </section>

        <div class="flex flex-col gap-6 xl:flex-row xl:items-start">
            <div class="flex flex-1 flex-col gap-6">
                @if ($inProgress)
                    <section class="flex flex-col gap-6 rounded-3xl bg-white p-6 ring-1 ring-inset ring-hairline sm:flex-row sm:items-center">
                        <x-progress-ring :percent="$percent" />
                        <div class="flex flex-1 flex-col gap-1.5">
                            <p class="text-[11px] font-bold uppercase tracking-[0.13em] text-teal-ink">Tes potensi · {{ $inProgress->test->title }}</p>
                            <h2 class="text-xl font-extrabold">{{ $percent >= 50 ? 'Hampir selesai' : 'Tes belum selesai' }}</h2>
                            <p class="text-sm leading-[1.5] text-slate">{{ $inProgress->answers_count }} dari {{ $inProgress->test->questions_count }} soal terjawab. Lanjutkan kapan saja.</p>
                        </div>
                        <a href="{{ route('attempts.show', $inProgress) }}" class="btn-primary w-fit">Lanjutkan <x-icon name="arrow-right" class="size-[18px]" /></a>
                    </section>
                @elseif ($pathwayProfile)
                    <section class="flex flex-col gap-5 rounded-3xl bg-white p-6 ring-1 ring-inset ring-hairline">
                        <div class="flex items-center justify-between gap-3">
                            <h2 class="text-lg font-extrabold">Profil jalur kamu</h2>
                            <a href="{{ route('tests.index') }}" class="text-sm font-bold text-blue hover:underline">Detail</a>
                        </div>
                        <x-score-bars :scores="$pathwayProfile" compact class="sm:grid sm:grid-cols-2 sm:gap-x-8" />
                    </section>
                @endif

                <x-card title="Jadwal bimbingan" :padded="false">
                    <x-slot name="actions"><a href="{{ route('guidance.index') }}" class="flex items-center gap-1.5 text-[15px] font-bold text-blue hover:underline">Lihat semua <x-icon name="arrow-right" class="size-[18px]" /></a></x-slot>
                    <ul class="divide-y divide-hairline">
                        @forelse ($bookings as $booking)
                            <x-session-row :date="$booking->slot->starts_at" :title="$booking->topic" :status="$booking->status" :muted="! $booking->isUpcoming()"
                                           :meta="$booking->slot->starts_at->format('H.i').' WIB · '.$booking->slot->mode->label().' · '.$booking->slot->advisor->name" />
                        @empty
                            <li class="px-6 py-6 text-sm text-slate">Belum ada jadwal bimbingan.</li>
                        @endforelse
                    </ul>
                    <div class="flex flex-wrap items-center justify-between gap-3 bg-cloud px-6 py-4">
                        <p class="text-[13px] font-semibold text-slate">{{ $freeSlots ? "Masih ada {$freeSlots} waktu kosong minggu ini" : 'Belum ada waktu kosong minggu ini' }}</p>
                        <a href="{{ route('guidance.index') }}" class="btn-primary px-[18px] py-2.5">Pesan jadwal <x-icon name="arrow-right" class="size-[18px]" /></a>
                    </div>
                </x-card>
            </div>

            <div class="flex flex-col gap-6 xl:w-[400px]">
                <x-card title="Situs resmi terbaru" :padded="false">
                    <x-slot name="actions"><a href="{{ route('links.index') }}" class="flex items-center gap-1.5 text-[15px] font-bold text-blue hover:underline">Direktori <x-icon name="arrow-right" class="size-[18px]" /></a></x-slot>
                    <ul class="divide-y divide-hairline">
                        @forelse ($newLinks as $link)
                            <li class="flex items-center gap-3.5 px-6 py-4">
                                <div class="flex min-w-0 flex-1 flex-col gap-[3px]">
                                    <p class="text-sm font-bold">{{ $link->institution->name }}</p>
                                    <a href="{{ $link->url }}" target="_blank" rel="noopener noreferrer" class="break-all font-mono text-[13px] font-bold text-blue hover:underline">{{ $link->domain }}<span class="sr-only"> (tab baru)</span></a>
                                </div>
                                <div class="flex flex-col items-end gap-1.5">
                                    <x-badge tone="success">Resmi</x-badge>
                                    <span class="text-[11px] font-medium text-slate">{{ $link->verified_at?->diffForHumans() }}</span>
                                </div>
                            </li>
                        @empty
                            <li class="px-6 py-6 text-sm text-slate">Belum ada.</li>
                        @endforelse
                    </ul>
                </x-card>

                @unless ($hasConsent)
                    <div class="flex gap-3.5 rounded-2xl bg-sand px-5 py-4 ring-1 ring-inset ring-gold/25">
                        <x-icon name="lock" class="text-gold-ink" />
                        <div class="flex flex-1 flex-col gap-0.5 text-sm">
                            <p class="font-bold">Konseling & Asisten AI belum dibuka</p>
                            <p class="leading-[1.5] text-slate">Fitur ini menunggu izin dari orang tua atau wali. Minta mereka menghubungi admin pesantren.</p>
                        </div>
                    </div>
                @endunless
            </div>
        </div>
    </div>
</x-app-layout>
