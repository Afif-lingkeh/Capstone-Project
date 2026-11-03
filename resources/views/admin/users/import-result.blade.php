{{-- resources/views/admin/users/import-result.blade.php --}}
<x-app-layout>
    <x-slot name="header">Hasil Impor Santri</x-slot>

    <div class="flex flex-col gap-6">
        <div class="grid gap-4 sm:grid-cols-2">
            <x-stat-card label="Akun dibuat" :value="count($created)" icon="user-check" />
            <x-stat-card label="Baris dilewati" :value="count($skipped)" icon="triangle-alert" :tone="count($skipped) ? 'warning' : 'default'" />
        </div>

        @if ($created)
            <x-alert variant="warning" title="Simpan atau cetak daftar ini sekarang">Password hanya ditampilkan sekali dan tidak disimpan dalam bentuk yang bisa dibaca. Minta santri mengganti password setelah masuk pertama kali.</x-alert>
            <x-card title="Akun baru" :padded="false">
                <x-slot name="actions"><button type="button" onclick="window.print()" class="btn-secondary px-4 py-2.5 text-sm">Cetak</button></x-slot>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-hairline text-sm">
                        <thead class="bg-cloud text-left text-[11px] font-bold uppercase tracking-[0.12em] text-slate"><tr><th class="px-5 py-3">NIS (username)</th><th class="px-5 py-3">Nama</th><th class="px-5 py-3">Kelas</th><th class="px-5 py-3">Password awal</th></tr></thead>
                        <tbody class="divide-y divide-hairline">
                            @foreach ($created as $row)
                                <tr><td class="px-5 py-3 font-mono font-bold">{{ $row['nis'] }}</td><td class="px-5 py-3">{{ $row['name'] }}</td><td class="px-5 py-3">{{ $row['class'] }}</td><td class="px-5 py-3 font-mono font-bold">{{ $row['password'] }}</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-card>
        @endif

        @if ($skipped)
            <x-card title="Baris yang dilewati" :padded="false">
                <ul class="divide-y divide-hairline">
                    @foreach ($skipped as $error)
                        <li class="flex gap-4 px-6 py-3 text-sm"><span class="w-20 shrink-0 font-bold text-slate">Baris {{ $error['line'] }}</span><span class="w-28 shrink-0 font-mono">{{ $error['nis'] ?: '—' }}</span><span class="text-danger-ink">{{ $error['message'] }}</span></li>
                    @endforeach
                </ul>
            </x-card>
        @endif

        <div class="flex gap-3">
            <a href="{{ route('admin.users.import') }}" class="btn-secondary">Impor file lain</a>
            <a href="{{ route('admin.users.index') }}" class="btn-ghost">Ke daftar pengguna</a>
        </div>
    </div>
</x-app-layout>
