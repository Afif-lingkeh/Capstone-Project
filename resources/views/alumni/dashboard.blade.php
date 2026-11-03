{{-- resources/views/alumni/dashboard.blade.php --}}
<x-app-layout>
    <x-slot name="header">Beranda alumni</x-slot>

    <section class="relative mb-7 flex flex-col gap-6 overflow-hidden rounded-[28px] bg-gradient-to-r from-ink to-cobalt px-7 py-8 text-white lg:flex-row lg:items-center lg:px-9">
        <div aria-hidden="true" class="pointer-events-none absolute -top-28 right-20 size-[360px] rounded-full bg-teal-light/25 blur-[50px]"></div>
        <span class="relative flex size-16 shrink-0 items-center justify-center rounded-full bg-gold-wash text-xl font-bold text-gold-ink">{{ $profile->initials() }}</span>
        <div class="relative flex flex-1 flex-col gap-2">
            <p class="text-xs font-bold uppercase tracking-[0.15em] text-teal-light">Ahlan wa sahlan, kakak alumni</p>
            <p class="text-[28px] font-extrabold tracking-[-0.02em]">{{ auth()->user()->name }}</p>
            <div class="flex flex-wrap items-center gap-2">
                @if ($profile->isVerified())
                    <span class="flex items-center gap-1.5 rounded-full bg-white/10 px-3 py-1.5 text-xs font-semibold"><x-icon name="shield-check" class="size-4 text-teal-light" />Alumni terverifikasi</span>
                @elseif ($profile->verification_status === 'rejected')
                    <span class="rounded-full bg-danger-wash px-3 py-1.5 text-xs font-bold text-danger-ink">Verifikasi ditolak: {{ $profile->rejection_note }}</span>
                @else
                    <span class="rounded-full bg-gold-wash px-3 py-1.5 text-xs font-bold text-gold-ink">Menunggu verifikasi admin</span>
                @endif
                <span class="text-xs font-semibold text-hairline">Profil {{ $completion }}% lengkap</span>
            </div>
        </div>
        <a href="{{ route('alumni.profile.edit') }}" class="btn relative w-fit rounded-full bg-white text-ink hover:bg-blue-wash">Lengkapi profil</a>
    </section>

    <div class="flex flex-col gap-6 xl:flex-row xl:items-start">
        <x-card :title="'Permintaan konsultasi ('.$pending->count().')'" :padded="false" class="flex-1">
            <x-slot name="actions"><a href="{{ route('consultations.index') }}" class="text-sm font-bold text-blue hover:underline">Semua konsultasi ({{ $activeCount }} berjalan)</a></x-slot>
            <ul class="divide-y divide-hairline">
                @forelse ($pending as $consultation)
                    @php($sp = $consultation->santri->santriProfile)
                    <li class="flex flex-col gap-3 px-6 py-5">
                        <div class="flex items-center gap-3">
                            <span class="flex size-10 items-center justify-center rounded-full bg-blue-wash text-sm font-bold text-cobalt">{{ Str::upper(Str::substr($consultation->santri->name, 0, 2)) }}</span>
                            <div class="flex flex-col"><p class="text-[15px] font-bold">{{ $consultation->subject }}</p><p class="text-xs text-slate">Santri{{ $sp ? ' kelas '.$sp->class_name : '' }} · {{ $consultation->santri->gender === \App\Enums\Gender::Male ? 'putra' : 'putri' }} · {{ $consultation->created_at->diffForHumans() }}</p></div>
                        </div>
                        <p class="rounded-2xl bg-cloud p-4 text-sm leading-[1.6]">“{{ Str::limit($consultation->messages->first()?->body, 220) }}”</p>
                        <div class="flex gap-2">
                            <form method="POST" action="{{ route('consultations.respond', $consultation) }}">@csrf<input type="hidden" name="decision" value="declined"><button type="submit" class="btn-ghost py-2.5 text-sm">Tolak</button></form>
                            <form method="POST" action="{{ route('consultations.respond', $consultation) }}">@csrf<input type="hidden" name="decision" value="accepted"><x-primary-button class="py-2.5 text-sm">Terima</x-primary-button></form>
                        </div>
                    </li>
                @empty
                    <li class="px-6 py-8 text-center text-sm text-slate">Belum ada permintaan baru.</li>
                @endforelse
            </ul>
        </x-card>

        <div class="flex flex-col gap-4 xl:w-[400px]">
            <p class="text-xs font-bold uppercase tracking-[0.13em] text-slate">Pratinjau profil publik</p>
            <x-alumni-card :profile="$profile" :link="false" />
            <x-alert icon="shield" title="Demi keamanan adik-adik santri">Percakapan dapat ditinjau Admin Pesantren. Kedua pihak diberi tahu, dan setiap utas punya tombol Laporkan.</x-alert>
        </div>
    </div>
</x-app-layout>
