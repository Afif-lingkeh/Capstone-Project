{{-- resources/views/admin/users/import.blade.php --}}
<x-app-layout>
    <x-slot name="header">Impor Santri</x-slot>

    <a href="{{ route('admin.users.index') }}" class="mb-5 flex w-fit items-center gap-1.5 py-1 text-sm font-bold text-blue hover:underline"><x-icon name="arrow-left" class="size-4" /> Pengguna</a>

    <div class="flex max-w-4xl flex-col gap-6">
        <x-page-intro title="Impor akun santri dari CSV" description="Setiap baris menjadi satu akun santri. Username = NIS, password acak ditampilkan sekali setelah impor untuk dibagikan ke santri." class="mb-0" />

        <x-card title="1. Siapkan file">
            <p class="mb-3 text-sm text-slate">Baris pertama berisi nama kolom berikut. Pemisah koma atau titik koma (Excel) sama-sama diterima.</p>
            <div class="mb-4 flex flex-wrap gap-2">
                @foreach ($columns as $column)
                    <code class="rounded-lg bg-pebble px-2.5 py-1 font-mono text-[13px] font-bold">{{ $column }}</code>
                @endforeach
            </div>
            <ul class="mb-5 list-disc space-y-1 pl-5 text-sm text-slate marker:text-mist">
                <li><strong class="text-ink">jenjang</strong>: smp atau sma · <strong class="text-ink">jenis_kelamin</strong>: L atau P</li>
                <li><strong class="text-ink">nama_wali</strong> boleh dikosongkan.</li>
                <li>Baris dengan NIS yang sudah terdaftar dilewati dan dilaporkan.</li>
            </ul>
            <a href="{{ route('admin.users.import.template') }}" class="btn-secondary w-fit"><x-icon name="download" class="size-[18px]" />Unduh templat CSV</a>
        </x-card>

        <form method="POST" action="{{ route('admin.users.import.store') }}" enctype="multipart/form-data" class="flex flex-col gap-5 rounded-3xl bg-white p-6 ring-1 ring-inset ring-hairline">
            @csrf
            <h2 class="text-lg font-extrabold">2. Unggah</h2>
            <x-field label="File CSV (maks. 2 MB)" for="file">
                <input id="file" name="file" type="file" accept=".csv,text/csv" required class="block w-full rounded-[14px] border-[1.5px] border-dashed border-hairline bg-cloud p-4 text-sm file:mr-4 file:rounded-xl file:border-0 file:bg-blue file:px-4 file:py-2.5 file:font-bold file:text-white">
            </x-field>
            <x-primary-button class="w-fit"><x-icon name="upload" class="size-[18px]" />Impor santri</x-primary-button>
        </form>
    </div>
</x-app-layout>
