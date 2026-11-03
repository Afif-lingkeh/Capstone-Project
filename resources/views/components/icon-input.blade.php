{{-- resources/views/components/icon-input.blade.php --}}
{{-- Desain "Field/Text" dengan ikon di depan. type="password" otomatis mendapat tombol tampilkan/sembunyikan. --}}
@props(['icon' => null, 'type' => 'text', 'invalid' => false])

<div @if ($type === 'password') x-data="{ show: false }" @endif
     @class([
         'flex h-12 w-full items-center gap-2.5 rounded-[14px] border-[1.5px] bg-white px-4 focus-within:border-blue focus-within:ring-2 focus-within:ring-focus',
         'border-danger' => $invalid,
         'border-hairline' => ! $invalid,
     ])>
    @if ($icon)
        <x-icon :name="$icon" class="size-[18px] text-slate" />
    @endif
    <input type="{{ $type }}" @if ($type === 'password') :type="show ? 'text' : 'password'" @endif
           @if ($invalid) aria-invalid="true" @endif
           {{ $attributes->class('h-full min-w-0 flex-1 border-0 bg-transparent p-0 text-[15px] font-medium text-ink placeholder:text-slate focus:ring-0 focus-visible:outline-none') }}>
    @if ($type === 'password')
        <button type="button" @click="show = ! show" :aria-pressed="show.toString()" aria-pressed="false"
                class="-mr-2 flex size-10 items-center justify-center rounded-xl text-slate hover:bg-pebble hover:text-ink">
            <x-icon name="eye" class="size-[18px]" x-show="! show" />
            <x-icon name="eye-off" class="size-[18px]" x-show="show" x-cloak />
            <span class="sr-only">Tampilkan password</span>
        </button>
    @endif
</div>
