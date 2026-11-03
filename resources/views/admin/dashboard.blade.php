{{-- resources/views/admin/dashboard.blade.php --}}
{{-- Desain "04 Admin · Ringkasan". --}}
<x-app-layout>
    <x-slot name="header">Ringkasan</x-slot>
    <x-slot name="subtitle">{{ now()->translatedFormat('l, j F Y') }} · Semoga khidmat hari ini berkah</x-slot>

    <div class="flex flex-col gap-7">
        <div class="grid grid-cols-2 gap-4 xl:grid-cols-4">
            <x-stat-card label="Tautan terverifikasi" :value="$stats['verified']" icon="shield-check" caption="Tampil untuk santri" :href="route('admin.links.index', ['status' => 'verified'])" />
            <x-stat-card label="Menunggu verifikasi" :value="$stats['pending']" icon="clock" caption="Belum tampil ke santri" :href="route('admin.links.index', ['status' => 'pending'])" />
            <x-stat-card label="Perlu ditinjau ulang" :value="$stats['needs_review']" icon="triangle-alert" caption="Disembunyikan otomatis" :tone="$stats['needs_review'] ? 'warning' : 'default'" :href="route('admin.links.index', ['status' => 'needs_review'])" />
            <x-stat-card label="Izin orang tua" :value="$stats['without_consent']" icon="shield" caption="Santri belum tercatat" :href="route('admin.consents.index', ['status' => 'belum'])" />
        </div>

        <div class="flex flex-col gap-6 xl:flex-row xl:items-start">
            <x-card title="Yang perlu dikerjakan hari ini" :padded="false" class="flex-1">
                <ul class="divide-y divide-hairline">
                    @forelse ($tasks as [, , $title, $desc, $cta, $url, $icon])
                        <li class="flex flex-col gap-3 px-6 py-4 sm:flex-row sm:items-center sm:gap-4">
                            <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-blue-wash text-blue"><x-icon :name="$icon" class="size-[18px]" /></span>
                            <span class="flex flex-1 flex-col gap-0.5"><span class="text-[15px] font-bold">{{ $title }}</span><span class="text-sm leading-[1.5] text-slate">{{ $desc }}</span></span>
                            <a href="{{ $url }}" class="btn-secondary w-fit px-4 py-2.5 text-sm">{{ $cta }}</a>
                        </li>
                    @empty
                        <li class="flex items-center gap-3 px-6 py-8 text-sm text-slate"><x-icon name="circle-check" class="text-success" />Alhamdulillah, tidak ada tugas yang menunggu.</li>
                    @endforelse
                </ul>
            </x-card>

            <div class="flex flex-col gap-6 xl:w-[400px]">
                <x-card title="Pengecekan otomatis">
                    <p class="mb-4 text-[13px] font-medium text-slate">{{ $checks['last'] ? '24 jam terakhir · '.$checks['total'].' pengecekan' : 'Belum ada pengecekan dalam 24 jam terakhir.' }}</p>
                    <ul class="flex flex-col gap-3">
                        @foreach ($checks['by'] as $label => $count)
                            <li class="flex items-center justify-between text-sm"><span class="font-semibold">{{ $label }}</span><span class="font-extrabold tabular-nums">{{ $count }}</span></li>
                        @endforeach
                    </ul>
                    <p class="mt-4 rounded-2xl bg-cloud p-3 text-xs leading-[1.5] text-slate">Dijalankan setiap pukul 02.00. Gagal diakses 2× berturut-turut → otomatis disembunyikan dulu.</p>
                </x-card>

                <x-card title="Aktivitas tautan terbaru" :padded="false">
                    <ul class="divide-y divide-hairline">
                        @forelse ($recentLinks as $link)
                            <li class="flex items-center gap-3 px-6 py-3.5">
                                <span class="flex min-w-0 flex-1 flex-col"><span class="truncate text-sm font-bold">{{ $link->institution->name }}</span><span class="font-mono text-xs text-slate">{{ $link->domain }}</span></span>
                                <x-status-badge :status="$link->status" />
                            </li>
                        @empty
                            <li class="px-6 py-6 text-sm text-slate">Belum ada aktivitas.</li>
                        @endforelse
                    </ul>
                </x-card>
            </div>
        </div>
    </div>
</x-app-layout>
