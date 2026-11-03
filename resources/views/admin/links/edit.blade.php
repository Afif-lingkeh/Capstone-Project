{{-- resources/views/admin/links/edit.blade.php --}}
<x-app-layout>
    <x-slot name="header">Tautan: {{ $link->label }} · {{ $link->institution->name }}</x-slot>

    <div class="grid gap-6 xl:grid-cols-2">
        <div class="space-y-6">
            {{-- Ringkasan status --}}
            <section class="rounded-3xl bg-white p-6 ring-1 ring-inset ring-hairline">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <h2 class="text-lg font-extrabold">Status</h2>
                    <x-status-badge :status="$link->status" />
                </div>
                <dl class="mt-4 space-y-2 text-sm">
                    <div>
                        <dt class="text-slate">Domain</dt>
                        <dd class="font-mono font-semibold text-ink">{{ $link->domain }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate">URL</dt>
                        <dd class="break-all font-mono">
                            <a href="{{ $link->url }}" target="_blank" rel="noopener noreferrer" class="text-blue hover:underline">{{ $link->url }}</a>
                            @unless ($link->usesHttps())
                                <span class="ml-1 rounded-lg bg-warning-wash px-1.5 py-0.5 text-xs text-warning">tanpa HTTPS</span>
                            @endunless
                        </dd>
                    </div>
                    @if ($link->status === App\Enums\LinkStatus::Verified)
                        <div>
                            <dt class="text-slate">Diverifikasi</dt>
                            <dd>{{ $link->verifier?->name ?? 'Pengguna terhapus' }}, {{ $link->verified_at->translatedFormat('j F Y H:i') }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate">Sumber verifikasi</dt>
                            <dd class="whitespace-pre-line">{{ $link->verification_source }}</dd>
                        </div>
                    @endif
                    @if ($link->review_note)
                        <div>
                            <dt class="text-slate">Catatan penolakan</dt>
                            <dd class="whitespace-pre-line">{{ $link->review_note }}</dd>
                        </div>
                    @endif
                </dl>

                @if ($link->status === App\Enums\LinkStatus::NeedsReview)
                    <p class="mt-4 rounded-xl bg-danger-wash p-3 text-sm text-danger-ink ring-1 ring-danger-line">
                        Pengecekan otomatis menemukan masalah, sehingga tautan ini <strong>disembunyikan dari santri</strong>.
                        Periksa riwayat pengecekan di bawah, perbarui URL jika situsnya pindah, lalu verifikasi ulang atau tolak.
                    </p>
                @endif
            </section>

            {{-- Verifikasi / tolak --}}
            @can('verify', $link)
                @if ($link->status !== App\Enums\LinkStatus::Verified)
                    <section class="rounded-3xl bg-white p-6 ring-1 ring-inset ring-hairline">
                        <h2 class="text-lg font-extrabold">Verifikasi</h2>
                        <p class="mt-1 text-sm text-slate">
                            Pastikan domain cocok dengan sumber tepercaya, misalnya profil institusi di PDDikti, situs kementerian, atau surat/kontak resmi kampus.
                            Tuliskan sumber yang Anda pakai agar bisa diperiksa ulang.
                        </p>

                        <form method="POST" action="{{ route('admin.links.verify', $link) }}" class="mt-4 space-y-3">
                            @csrf
                            <div>
                                <x-input-label for="verification_source" value="Sumber verifikasi" />
                                <textarea id="verification_source" name="verification_source" rows="3" required
                                          placeholder="mis. Domain dicocokkan dengan profil kampus di PDDikti pada 17 Oktober 2026"
                                          class="field mt-1 h-auto py-3">{{ old('verification_source') }}</textarea>
                                <x-input-error :messages="$errors->get('verification_source')" class="mt-2" />
                            </div>
                            <x-primary-button>Verifikasi & Tampilkan ke Santri</x-primary-button>
                        </form>

                        @if ($link->status !== App\Enums\LinkStatus::Rejected)
                            <form method="POST" action="{{ route('admin.links.reject', $link) }}" class="mt-6 space-y-3 border-t border-hairline pt-4">
                                @csrf
                                <div>
                                    <x-input-label for="review_note" value="Alasan penolakan" />
                                    <textarea id="review_note" name="review_note" rows="2" required
                                              class="field mt-1 h-auto py-3">{{ old('review_note') }}</textarea>
                                    <x-input-error :messages="$errors->get('review_note')" class="mt-2" />
                                </div>
                                <x-danger-button>Tolak Tautan</x-danger-button>
                            </form>
                        @endif
                    </section>
                @endif
            @endcan

            {{-- Ubah data --}}
            <form method="POST" action="{{ route('admin.links.update', $link) }}">
                @csrf
                @method('PUT')
                @include('admin.links.partials.form')
            </form>
        </div>

        <div class="space-y-6">
            {{-- Riwayat pengecekan --}}
            <section class="rounded-3xl bg-white p-6 ring-1 ring-inset ring-hairline">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <h2 class="text-lg font-extrabold">Riwayat Pengecekan</h2>
                    <form method="POST" action="{{ route('admin.links.check', $link) }}">
                        @csrf
                        <x-secondary-button type="submit">Cek Sekarang</x-secondary-button>
                    </form>
                </div>
                <p class="mt-1 text-xs text-slate">Tautan terverifikasi dicek otomatis setiap malam jika pengecekan terakhir lebih dari {{ config('peduli.link_check.interval_days') }} hari lalu.</p>

                <ul class="mt-4 divide-y divide-hairline text-sm">
                    @forelse ($link->checkLogs as $log)
                        <li class="py-3">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <x-status-badge :status="$log->result" />
                                <span class="text-xs text-slate">{{ $log->checked_at->translatedFormat('j M Y H:i') }}</span>
                            </div>
                            <p class="mt-1 text-xs text-slate">
                                HTTP {{ $log->http_status ?? '—' }}
                                @if ($log->response_ms) · {{ $log->response_ms }} ms @endif
                            </p>
                            @if ($log->final_url && $log->final_url !== $link->url)
                                <p class="mt-1 break-all font-mono text-xs text-slate">→ {{ $log->final_url }}</p>
                            @endif
                            @if ($log->error_message)
                                <p class="mt-1 break-all text-xs text-danger-ink">{{ $log->error_message }}</p>
                            @endif
                        </li>
                    @empty
                        <li class="py-3 text-slate">Belum pernah dicek.</li>
                    @endforelse
                </ul>
            </section>

            @can('delete', $link)
                <section class="rounded-3xl bg-white p-6 ring-1 ring-inset ring-hairline">
                    <h2 class="text-lg font-extrabold text-danger-ink">Hapus Tautan</h2>
                    <p class="mt-1 text-sm text-slate">Riwayat pengecekannya ikut terhapus.</p>
                    <form method="POST" action="{{ route('admin.links.destroy', $link) }}" class="mt-3" onsubmit="return confirm('Hapus tautan ini?')">
                        @csrf
                        @method('DELETE')
                        <x-danger-button>Hapus</x-danger-button>
                    </form>
                </section>
            @endcan
        </div>
    </div>
</x-app-layout>
