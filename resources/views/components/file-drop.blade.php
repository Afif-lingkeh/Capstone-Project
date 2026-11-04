{{-- resources/views/components/file-drop.blade.php --}}
{{-- Area unggah: klik atau seret file ke sini. Input file asli tetap dipakai (bisa diakses keyboard & pembaca layar). --}}
@props(['name' => 'file', 'id' => 'file', 'accept' => null, 'hint' => null])

<div x-data="{ over: false, file: null }" class="flex flex-col gap-2">
    <label for="{{ $id }}"
           @dragover.prevent="over = true" @dragleave.prevent="over = false"
           @drop.prevent="over = false; $refs.input.files = $event.dataTransfer.files; file = $event.dataTransfer.files[0] ?? null"
           :class="over ? 'border-blue bg-blue-wash' : 'border-hairline bg-cloud hover:bg-pebble'"
           class="flex cursor-pointer flex-col items-center gap-2 rounded-2xl border-[1.5px] border-dashed border-hairline bg-cloud px-5 py-7 text-center transition-colors duration-150 has-[:focus-visible]:outline has-[:focus-visible]:outline-2 has-[:focus-visible]:outline-offset-2 has-[:focus-visible]:outline-blue">
        <span class="flex size-11 items-center justify-center rounded-xl bg-white text-blue ring-1 ring-inset ring-hairline"><x-icon name="upload" /></span>
        <span x-show="! file" class="text-sm font-bold">Pilih file atau seret ke sini</span>
        <span x-show="file" x-cloak class="break-all text-sm font-bold text-ink" x-text="file ? file.name + ' · ' + (file.size >= 1048576 ? (file.size / 1048576).toFixed(1) + ' MB' : Math.max(1, Math.round(file.size / 1024)) + ' KB') : ''"></span>
        @if ($hint)<span class="text-xs text-slate">{{ $hint }}</span>@endif
        <input x-ref="input" id="{{ $id }}" name="{{ $name }}" type="file" @if ($accept) accept="{{ $accept }}" @endif required class="sr-only"
               @change="file = $event.target.files[0] ?? null" {{ $attributes }}>
    </label>
    <x-input-error :messages="$errors->get($name)" />
</div>
