{{-- resources/views/admin/consents/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">Persetujuan Wali</x-slot>

    <x-page-intro title="Persetujuan orang tua/wali" description="Konseling dan Asisten AI baru terbuka untuk santri setelah persetujuan wali dicatat di sini." />

    <div class="flex flex-col gap-6 xl:flex-row xl:items-start">
        <div class="flex flex-1 flex-col gap-4">
            <form method="GET" class="flex flex-col gap-3 sm:flex-row">
                <label for="q" class="sr-only">Cari santri</label>
                <x-text-input id="q" name="q" type="search" :value="request('q')" placeholder="Cari nama atau NIS" class="sm:flex-1" />
                <x-select-input name="status" aria-label="Status persetujuan" class="sm:w-48">
                    <option value="">Semua</option>
                    <option value="belum" @selected(request('status') === 'belum')>Belum ada</option>
                    <option value="sudah" @selected(request('status') === 'sudah')>Sudah ada</option>
                </x-select-input>
                <button type="submit" class="btn-secondary">Filter</button>
            </form>

            <div class="overflow-x-auto rounded-3xl bg-white ring-1 ring-hairline">
                <table class="min-w-full divide-y divide-hairline text-sm">
                    <thead class="bg-cloud text-left text-[11px] font-bold uppercase tracking-[0.12em] text-slate">
                        <tr><th class="px-5 py-3">Santri</th><th class="px-5 py-3">Persetujuan</th><th class="px-5 py-3 text-right">Aksi</th></tr>
                    </thead>
                    <tbody class="divide-y divide-hairline">
                        @forelse ($santri as $user)
                            @php($consent = $user->guardianConsents->firstWhere('revoked_at', null))
                            <tr>
                                <td class="px-5 py-4"><p class="font-bold">{{ $user->name }}</p><p class="text-xs text-slate">NIS {{ $user->santriProfile?->nis ?? '—' }}</p></td>
                                <td class="px-5 py-4">
                                    @if ($consent)
                                        <x-badge tone="success">Ada</x-badge>
                                        <p class="mt-1 text-xs text-slate">{{ $consent->guardian_name }} ({{ $consent->relationship }}) · {{ $consent->method }} · {{ $consent->consented_at->translatedFormat('j M Y') }}</p>
                                    @else
                                        <x-badge tone="gold">Belum ada</x-badge>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-5 py-4 text-right">
                                    @if ($consent)
                                        <form method="POST" action="{{ route('admin.consents.revoke', $consent) }}" onsubmit="return confirm('Cabut persetujuan? Konseling & Asisten AI santri ini akan terkunci.')">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="font-bold text-danger-ink hover:underline">Cabut<span class="sr-only"> persetujuan {{ $user->name }}</span></button>
                                        </form>
                                    @else
                                        <a href="{{ route('admin.consents.index', [...request()->query(), 'santri' => $user->id]) }}#catat" class="font-bold text-blue hover:underline">Catat<span class="sr-only"> persetujuan {{ $user->name }}</span></a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-5 py-10 text-center text-slate">Tidak ada santri.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $santri->links('pagination.peduli') }}
        </div>

        <form id="catat" method="POST" action="{{ route('admin.consents.store') }}" class="flex scroll-mt-24 flex-col gap-5 rounded-3xl bg-white p-6 ring-1 ring-inset ring-hairline xl:sticky xl:top-24 xl:w-[400px]">
            @csrf
            <h2 class="text-lg font-extrabold">Catat persetujuan</h2>
            @if ($selected)
                <input type="hidden" name="santri_id" value="{{ $selected->id }}">
                <p class="rounded-2xl bg-cloud p-4 text-sm"><span class="font-bold">{{ $selected->name }}</span><br><span class="text-slate">NIS {{ $selected->santriProfile?->nis ?? '—' }}</span></p>
                <x-field label="Nama orang tua/wali" for="guardian_name"><x-text-input id="guardian_name" name="guardian_name" :value="old('guardian_name', $selected->santriProfile?->guardian_name)" required /></x-field>
                <x-field label="Hubungan" for="relationship"><x-text-input id="relationship" name="relationship" list="hubungan" :value="old('relationship')" required /></x-field>
                <datalist id="hubungan"><option value="Ayah"></option><option value="Ibu"></option><option value="Wali"></option></datalist>
                <x-field label="Cara persetujuan" for="method">
                    <x-select-input id="method" name="method">
                        <option value="tertulis">Tertulis (formulir bertanda tangan)</option>
                        <option value="daring">Daring (formulir online)</option>
                        <option value="lisan">Lisan (disaksikan petugas)</option>
                    </x-select-input>
                </x-field>
                <x-field label="Tanggal persetujuan" for="consented_at"><x-text-input id="consented_at" name="consented_at" type="date" :value="old('consented_at', now()->toDateString())" :max="now()->toDateString()" required /></x-field>
                <x-field label="Catatan" for="notes" optional><x-text-input id="notes" name="notes" :value="old('notes')" placeholder="mis. nomor arsip formulir" /></x-field>
                <p class="text-xs text-slate">Mengacu kebijakan privasi versi {{ config('peduli.privacy_policy_version') }}.</p>
                <x-primary-button>Simpan persetujuan</x-primary-button>
            @else
                <p class="text-sm text-slate">Pilih “Catat” pada santri di daftar.</p>
            @endif
        </form>
    </div>
</x-app-layout>
