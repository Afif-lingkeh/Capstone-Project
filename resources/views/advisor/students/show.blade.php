{{-- resources/views/advisor/students/show.blade.php --}}
<x-app-layout>
    <x-slot name="header">Santri Binaan</x-slot>

    <a href="{{ route('advisor.students.index') }}" class="mb-5 flex w-fit items-center gap-1.5 py-1 text-sm font-bold text-blue hover:underline"><x-icon name="arrow-left" class="size-4" /> Semua santri binaan</a>

    <x-page-intro :title="$student->name" :description="$student->santriProfile ? $student->santriProfile->level->label().' · Kelas '.$student->santriProfile->class_name.' · NIS '.$student->santriProfile->nis : null" />

    <div class="flex flex-col gap-6 xl:flex-row xl:items-start">
        <section class="flex flex-col gap-6 rounded-3xl bg-white p-8 ring-1 ring-inset ring-hairline xl:w-[480px]">
            <div class="flex flex-col gap-1.5">
                <p class="text-xs font-bold uppercase tracking-[0.13em] text-teal-ink">Profil jalur</p>
                <h2 class="text-2xl font-extrabold">{{ $headline ?? 'Belum ada hasil tes' }}</h2>
            </div>
            @if ($profile)<x-score-bars :scores="$profile" />@endif
        </section>

        <div class="flex flex-1 flex-col gap-6">
            <x-card title="Riwayat tes" :padded="false">
                <ul class="divide-y divide-hairline">
                    @forelse ($attempts as $attempt)
                        <li class="flex flex-col gap-3 px-6 py-4 sm:flex-row sm:items-center sm:gap-6">
                            <span class="flex flex-col sm:w-56"><span class="text-sm font-bold">{{ $attempt->test->title }}</span><span class="text-xs text-slate">{{ $attempt->submitted_at->translatedFormat('j M Y') }}</span></span>
                            <x-score-bars :scores="$attempt->scores" compact class="flex-1 sm:grid sm:grid-cols-2 sm:gap-x-6" />
                        </li>
                    @empty
                        <li class="px-6 py-6 text-sm text-slate">Belum ada tes yang dikumpulkan.</li>
                    @endforelse
                </ul>
            </x-card>

            <x-card title="Sesi bimbingan bersamamu" :padded="false">
                <ul class="divide-y divide-hairline">
                    @forelse ($bookings as $booking)
                        <x-session-row :date="$booking->slot->starts_at" :title="$booking->topic" :status="$booking->status" :muted="$booking->slot->starts_at->isPast()"
                                       :meta="$booking->slot->starts_at->translatedFormat('j M Y, H.i')">
                            @if ($booking->outcome_notes)<p class="line-clamp-2 text-sm text-slate">{{ $booking->outcome_notes }}</p>@endif
                        </x-session-row>
                    @empty
                        <li class="px-6 py-6 text-sm text-slate">Belum ada sesi.</li>
                    @endforelse
                </ul>
            </x-card>
        </div>
    </div>
</x-app-layout>
