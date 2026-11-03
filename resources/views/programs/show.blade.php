{{-- resources/views/programs/show.blade.php --}}
@php($open = $program->acceptsInterest())
<x-app-layout>
    <x-slot name="header">Kerja & Magang</x-slot>

    <nav aria-label="Breadcrumb" class="mb-5">
        <a href="{{ route('programs.index') }}" class="flex w-fit items-center gap-1.5 py-1 text-sm font-bold text-blue hover:underline"><x-icon name="arrow-left" class="size-4" /> Semua program</a>
    </nav>

    <div class="flex flex-col gap-6 xl:flex-row xl:items-start">
        <article class="flex flex-1 flex-col gap-6">
            <header class="flex flex-col gap-3">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="rounded-lg bg-pebble px-2.5 py-[5px] text-xs font-bold">{{ $program->country->flag() }} {{ $program->country->name }}</span>
                    <span class="rounded-lg bg-teal-wash px-2.5 py-[5px] text-xs font-bold text-teal-ink">{{ $program->type->label() }}</span>
                    <x-badge :tone="$open ? 'blue' : 'neutral'">{{ $open ? 'Buka' : 'Tutup' }}</x-badge>
                </div>
                <h1 class="text-[28px] font-extrabold leading-tight tracking-[-0.02em] lg:text-4xl">{{ $program->title }}</h1>
                <p class="text-[17px] leading-[1.6] text-slate">{{ $program->summary }}</p>
            </header>

            <x-card title="Persyaratan">
                <div class="whitespace-pre-line text-[15px] leading-[1.7] text-slate">{{ $program->requirements }}</div>
            </x-card>

            <x-card title="Biaya">
                <div class="whitespace-pre-line text-[15px] leading-[1.7] text-slate">{{ $program->costs }}</div>
                <p class="mt-4 text-[13px] font-medium text-slate">Biaya resmi selalu dibayar ke rekening atas nama lembaga mitra, bukan ke rekening pribadi.</p>
            </x-card>

            <x-card title="Tentang mitra">
                <p class="text-base font-bold">{{ $program->partner->name }}</p>
                @if ($program->partner->description)<p class="mt-1 text-[15px] leading-[1.6] text-slate">{{ $program->partner->description }}</p>@endif
                <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
                    @if ($program->partner->website)
                        <div><dt class="font-semibold text-slate">Situs resmi</dt><dd><a href="{{ $program->partner->website }}" target="_blank" rel="noopener noreferrer" class="break-all font-mono font-bold text-blue hover:underline">{{ parse_url($program->partner->website, PHP_URL_HOST) }}</a></dd></div>
                    @endif
                    @if ($program->partner->contact_email)
                        <div><dt class="font-semibold text-slate">Email resmi</dt><dd class="break-all font-bold">{{ $program->partner->contact_email }}</dd></div>
                    @endif
                </dl>
            </x-card>
        </article>

        <aside id="minat" class="flex scroll-mt-24 flex-col gap-4 xl:sticky xl:top-24 xl:w-[380px]">
            <div class="flex flex-col gap-4 rounded-3xl bg-white p-6 ring-1 ring-inset ring-hairline">
                <ul class="flex flex-col gap-3 text-sm font-medium">
                    <li class="flex items-center gap-3"><x-icon name="calendar" class="text-slate" />{{ $program->deadline ? 'Tenggat '.$program->deadline->translatedFormat('l, j F Y') : 'Tenggat belum ditentukan' }}</li>
                    @if ($program->min_education)<li class="flex items-center gap-3"><x-icon name="graduation-cap" class="text-slate" />{{ $program->min_education }}</li>@endif
                    <li class="flex items-start gap-3"><x-icon name="info" class="text-slate" /><span>Kontak resmi: {{ $program->official_contact }}</span></li>
                    @if ($program->official_url)
                        <li class="flex items-center gap-3"><x-icon name="external-link" class="text-slate" /><a href="{{ $program->official_url }}" target="_blank" rel="noopener noreferrer" class="break-all font-bold text-blue hover:underline">Pengumuman resmi<span class="sr-only"> (tab baru)</span></a></li>
                    @endif
                </ul>

                @if ($interest)
                    <x-alert variant="success" title="Kamu sudah menyatakan berminat">
                        {{ $interest->followed_up_at ? 'Admin pesantren sudah menindaklanjuti minatmu.' : 'Admin pesantren akan menghubungimu, insyaallah.' }}
                    </x-alert>
                    <form method="POST" action="{{ route('programs.withdraw', $program) }}" onsubmit="return confirm('Batalkan minatmu pada program ini?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn-ghost w-full text-danger-ink">Batalkan minat</button>
                    </form>
                @elseif ($open)
                    <form method="POST" action="{{ route('programs.interest', $program) }}" class="flex flex-col gap-3">
                        @csrf
                        <x-field label="Pesan untuk admin" for="note" optional hint="Mis. pertanyaan soal syarat atau jadwal.">
                            <x-textarea id="note" name="note" rows="3" maxlength="500" class="min-h-[96px]">{{ old('note') }}</x-textarea>
                        </x-field>
                        <x-primary-button class="w-full">Saya berminat <x-icon name="arrow-right" class="size-[18px]" /></x-primary-button>
                    </form>
                @else
                    <x-alert variant="warning" title="Pendaftaran sudah ditutup">Simpan program ini untuk memantau periode berikutnya.</x-alert>
                @endif

                <form method="POST" action="{{ route('programs.bookmark', $program) }}">
                    @csrf
                    <button type="submit" class="btn-secondary w-full" aria-pressed="{{ $bookmarked ? 'true' : 'false' }}">
                        <x-icon :name="$bookmarked ? 'bookmark-check' : 'bookmark'" class="size-[18px]" />{{ $bookmarked ? 'Tersimpan' : 'Simpan program' }}
                    </button>
                </form>
            </div>
        </aside>
    </div>
</x-app-layout>
