{{-- resources/views/advisor/students/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">Santri Binaan</x-slot>

    <x-page-intro title="Santri binaan" description="Profil jalur dari tes potensi terakhir setiap santri. Hanya santri binaanmu yang tampil di sini." />

    @if ($students->isEmpty())
        <x-empty-state icon="users" title="Belum ada santri binaan">Admin pesantren menentukan santri binaan untuk setiap pembimbing.</x-empty-state>
    @else
        <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($students as $row)
                @php($santri = $row['santri'])
                <article class="relative flex flex-col gap-4 rounded-3xl bg-white p-6 ring-1 ring-inset ring-hairline hover:bg-cloud">
                    <div class="flex items-center gap-3">
                        <span class="flex size-11 items-center justify-center rounded-full bg-blue-wash text-sm font-bold text-cobalt">{{ Str::upper(Str::substr($santri->name, 0, 2)) }}</span>
                        <div class="flex flex-col">
                            <h2 class="text-base font-extrabold"><a href="{{ route('advisor.students.show', $santri) }}" class="after:absolute after:inset-0">{{ $santri->name }}</a></h2>
                            <p class="text-xs font-medium text-slate">{{ $santri->santriProfile ? $santri->santriProfile->level->label().' · Kelas '.$santri->santriProfile->class_name.' · NIS '.$santri->santriProfile->nis : 'Data santri belum lengkap' }}</p>
                        </div>
                    </div>
                    @if ($row['profile'])
                        <x-score-bars :scores="$row['profile']" compact />
                    @else
                        <p class="rounded-2xl bg-cloud px-4 py-3 text-sm text-slate">Belum mengerjakan tes potensi.</p>
                    @endif
                </article>
            @endforeach
        </div>
    @endif
</x-app-layout>
