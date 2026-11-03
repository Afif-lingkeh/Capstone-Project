{{-- resources/views/layouts/guest.blade.php --}}
{{-- Layout halaman autentikasi (desain 02 Auth): panel merek navy di kiri, formulir di kanan.
     Di HP panel merek menyusut menjadi pita di atas formulir. --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ? $title.' — ' : '' }}{{ config('app.name', 'Laravel') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800|fraunces:600i|amiri:700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-ink font-sans text-ink antialiased lg:bg-white">
    <div class="flex min-h-screen flex-col lg:flex-row">
        <aside class="relative flex flex-col gap-5 overflow-hidden bg-gradient-to-b from-ink to-ink-deep px-6 pb-9 pt-7 text-white lg:sticky lg:top-0 lg:h-screen lg:w-[560px] lg:shrink-0 lg:justify-between lg:p-14">
            <div aria-hidden="true" class="pointer-events-none absolute -top-[140px] left-[200px] size-80 rounded-full bg-blue/35 blur-[60px] lg:left-[260px] lg:size-[480px]"></div>
            <div aria-hidden="true" class="pointer-events-none absolute -left-40 top-[560px] hidden size-[420px] rounded-full bg-teal/25 blur-[55px] lg:block"></div>

            <a href="{{ route('home') }}" aria-label="PEDULI, ke beranda" class="relative w-fit">
                <x-brand inverse />
            </a>

            <div class="relative flex flex-col gap-6">
                <x-star class="hidden text-gold-light opacity-70 [&_circle]:fill-ink lg:block" />
                <p class="text-[30px] font-extrabold leading-[1.17] tracking-[-0.03em] lg:text-[44px] lg:leading-[1.09]">
                    Satu akun untuk<br>setiap <em class="font-accent text-[1.07em] font-semibold text-teal-light">langkahmu.</em>
                </p>
                <ul class="hidden flex-col gap-6 lg:flex">
                    @foreach ([['shield-check', 'Situs kampus yang sudah diperiksa pesantren'], ['calendar-check', 'Bimbingan kuliah dan teman bercerita'], ['clipboard-list', 'Mengenal kekuatan yang Allah titipkan']] as [$icon, $text])
                        <li class="flex items-center gap-3 text-[15px] font-semibold text-hairline">
                            <span class="flex size-9 items-center justify-center rounded-[10px] bg-white/[.08]"><x-icon :name="$icon" class="size-[18px] text-teal-light" /></span>
                            {{ $text }}
                        </li>
                    @endforeach
                </ul>
            </div>

            <p class="relative hidden flex-col gap-1.5 lg:flex">
                <span lang="ar" dir="rtl" class="w-fit font-arabic text-[26px] font-bold text-gold-light">مَنْ جَدَّ وَجَدَ</span>
                <span class="text-[13px] font-medium text-mist">Siapa bersungguh-sungguh, ia akan sampai.</span>
            </p>
        </aside>

        <main class="flex flex-1 flex-col items-center rounded-t-[28px] bg-white px-6 pb-8 pt-7 lg:justify-center lg:rounded-none lg:p-14">
            <div class="flex w-full max-w-[420px] flex-col gap-6">
                {{ $slot }}
            </div>
        </main>
    </div>
</body>
</html>
