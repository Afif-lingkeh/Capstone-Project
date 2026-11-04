{{-- resources/views/alumni/show.blade.php --}}
<x-app-layout>
    <x-slot name="header">Direktori Alumni</x-slot>

    <a href="{{ route('alumni.index') }}" class="mb-5 flex w-fit items-center gap-1.5 py-1 text-sm font-bold text-blue hover:underline"><x-icon name="arrow-left" class="size-4" /> Semua alumni</a>

    <div class="flex flex-col gap-6 xl:flex-row xl:items-start">
        <div class="flex flex-col gap-4 xl:w-[400px]">
            <x-alumni-card :profile="$profile" :link="false" />
            <div class="flex flex-col gap-3 rounded-3xl bg-white p-6 ring-1 ring-inset ring-hairline">
                <h2 class="text-base font-extrabold">Kontak yang ditampilkan</h2>
                @if ($profile->show_linkedin && $profile->linkedin_url)
                    <a href="{{ $profile->linkedin_url }}" target="_blank" rel="noopener noreferrer" class="flex items-center gap-3 text-sm font-bold text-blue hover:underline"><x-icon name="linkedin" class="size-[18px]" />LinkedIn<span class="sr-only"> (tab baru)</span></a>
                @endif
                @if ($profile->show_email && $profile->public_email)
                    <p class="flex items-center gap-3 break-all text-sm font-bold"><x-icon name="mail" class="size-[18px] text-slate" />{{ $profile->public_email }}</p>
                @endif
                @unless (($profile->show_linkedin && $profile->linkedin_url) || ($profile->show_email && $profile->public_email))
                    <p class="text-sm text-slate">Alumni ini memilih dihubungi melalui fitur konsultasi PEDULI.</p>
                @endunless
                <p class="text-xs text-slate">Nomor WhatsApp alumni tidak pernah ditampilkan.</p>
            </div>
        </div>

        <div class="flex flex-1 flex-col gap-6">
            <x-card title="Tentang">
                <dl class="grid gap-4 sm:grid-cols-2">
                    <div><dt class="text-[11px] font-bold uppercase tracking-[0.11em] text-slate">{{ $profile->activityLabel() }} di</dt><dd class="mt-1 text-[15px] font-semibold">{{ $profile->institution_name }}</dd></div>
                    <div><dt class="text-[11px] font-bold uppercase tracking-[0.11em] text-slate">Bidang</dt><dd class="mt-1 text-[15px] font-semibold">{{ $profile->field ?: '—' }}</dd></div>
                </dl>
            </x-card>

            @if ($existing)
                <x-alert title="Konsultasimu dengan alumni ini masih berjalan">
                    <a href="{{ route('consultations.show', $existing) }}" class="font-bold text-blue underline">Buka percakapan</a>
                </x-alert>
            @elseif ($canConsult)
                <form method="POST" action="{{ route('consultations.store', $profile) }}" class="flex flex-col gap-5 rounded-3xl bg-white p-6 ring-1 ring-inset ring-hairline sm:p-8">
                    @csrf
                    <h2 class="text-lg font-extrabold">Ajukan konsultasi</h2>
                    <x-field label="Topik" for="subject"><x-text-input id="subject" name="subject" :value="old('subject')" placeholder="mis. Beasiswa ke Eropa" maxlength="150" required /></x-field>
                    <x-field label="Pertanyaanmu" for="body"><x-textarea id="body" name="body" rows="5" maxlength="2000" required>{{ old('body') }}</x-textarea></x-field>
                    <x-alert icon="shield" title="Demi keamanan bersama">Percakapan dapat ditinjau Admin Pesantren. Jangan bagikan nomor HP, alamat, atau data pribadi lain.</x-alert>
                    <x-primary-button class="w-fit">Kirim pertanyaan <x-icon name="send" class="size-[18px]" /></x-primary-button>
                </form>
            @endif
        </div>
    </div>
</x-app-layout>
