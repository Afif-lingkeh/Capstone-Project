{{-- resources/views/counseling/create.blade.php --}}
<x-app-layout>
    <x-slot name="header">Konseling</x-slot>

    <div class="mx-auto flex max-w-3xl flex-col gap-6">
        <a href="{{ route('counseling.index') }}" class="flex w-fit items-center gap-1.5 py-1 text-sm font-bold text-blue hover:underline"><x-icon name="arrow-left" class="size-4" /> Kembali</a>
        <div class="flex flex-col gap-2">
            <h1 class="text-[26px] font-extrabold tracking-[-0.02em] lg:text-[32px]">Ceritakan apa yang sedang kamu rasakan</h1>
            <p class="text-[15px] leading-6 text-slate">Tidak perlu panjang atau rapi. Tulis sesuai yang kamu rasakan, konselor akan menghubungimu.</p>
        </div>

        <form method="POST" action="{{ route('counseling.store') }}" class="flex flex-col gap-6 rounded-3xl bg-white p-6 ring-1 ring-inset ring-hairline sm:p-8">
            @csrf
            <fieldset>
                <legend class="mb-3 text-sm font-bold">Topiknya tentang apa?</legend>
                <div class="grid gap-2.5 sm:grid-cols-2">
                    @foreach ($topics as $topic)
                        <label class="flex min-h-[52px] cursor-pointer items-center gap-3 rounded-[14px] px-4 ring-[1.5px] ring-inset ring-hairline has-[:checked]:bg-blue-wash has-[:checked]:ring-2 has-[:checked]:ring-blue">
                            <input type="radio" name="topic" value="{{ $topic->value }}" @checked(old('topic') === $topic->value) required class="size-5 border-mist text-blue focus:ring-focus">
                            <span class="text-[15px] font-semibold">{{ $topic->label() }}</span>
                        </label>
                    @endforeach
                </div>
                <x-input-error :messages="$errors->get('topic')" class="mt-2" />
            </fieldset>

            <x-field label="Ceritamu" for="story" hint="Disimpan terenkripsi. Hanya konselor yang mendampingimu yang bisa membacanya.">
                <x-textarea id="story" name="story" rows="8" maxlength="5000" required>{{ old('story') }}</x-textarea>
            </x-field>

            <x-field label="Kapan kamu bisa ditemui?" for="preferred_time" optional>
                <x-text-input id="preferred_time" name="preferred_time" :value="old('preferred_time')" placeholder="mis. setelah Asar, atau hari Kamis" />
            </x-field>

            <fieldset>
                <legend class="mb-3 text-sm font-bold">Seberapa mendesak?</legend>
                <div class="flex flex-col gap-2.5">
                    @foreach ($urgencies as $urgency)
                        <label class="flex min-h-[52px] cursor-pointer items-center gap-3 rounded-[14px] px-4 ring-[1.5px] ring-inset ring-hairline has-[:checked]:bg-blue-wash has-[:checked]:ring-2 has-[:checked]:ring-blue">
                            <input type="radio" name="urgency" value="{{ $urgency->value }}" @checked(old('urgency', 'medium') === $urgency->value) class="size-5 border-mist text-blue focus:ring-focus">
                            <span class="text-[15px] font-semibold">{{ $urgency->santriLabel() }}</span>
                        </label>
                    @endforeach
                </div>
            </fieldset>

            <x-alert variant="warning" icon="life-buoy" title="Merasa tidak aman sekarang?">
                Jangan menunggu balasan. Segera temui ustadz/ustadzah, musyrif/musyrifah, atau orang dewasa yang kamu percaya di dekatmu.
            </x-alert>

            <x-primary-button class="w-full sm:w-fit">Kirim ke konselor <x-icon name="send" class="size-[18px]" /></x-primary-button>
        </form>
    </div>
</x-app-layout>
