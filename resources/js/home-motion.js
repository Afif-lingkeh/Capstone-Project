// resources/js/home-motion.js
// Animasi beranda. Dimuat terpisah dari app.js supaya halaman lain tidak ikut mengunduhnya.
import { animate, hover, inView, scroll, stagger } from 'motion';

// Gerak tenang tanpa pantulan berlebih: ini layanan pesantren, bukan mainan.
const enter = { type: 'spring', bounce: 0, visualDuration: 0.5 };
const enterSoft = { type: 'spring', bounce: 0.18, visualDuration: 0.55 };

// Hanya hero yang disembunyikan lewat CSS (.js-reveal), karena cuma bagian itu
// yang sudah terlihat saat halaman dibuka. Kelasnya dilepas begitu animasi hero
// selesai; kalau animasinya tidak pernah jalan, pengaman di blade yang melepas.
function unhideHero() {
    document.documentElement.classList.remove('js-reveal');
}

function alreadyVisible(element) {
    const rect = element.getBoundingClientRect();

    return rect.top < window.innerHeight && rect.bottom > 0;
}

// Jalankan sekali saja, lalu lepas pengamatannya.
function onceInView(element, run, options) {
    let stop;
    let done = false;

    stop = inView(element, () => {
        if (done) {
            return;
        }

        done = true;
        run(element);

        if (stop) {
            stop();
        }
    }, options);
}

// Hero muncul berurutan mengikuti arah baca: label, judul, penjelasan, tombol.
function hero() {
    const running = [];
    const copy = document.querySelector('[data-hero-copy]');

    if (copy) {
        running.push(animate([...copy.children], { opacity: [0, 1], y: [14, 0] }, { ...enter, delay: stagger(0.07) }));
    }

    const photo = document.querySelectorAll('[data-hero-photo]');

    if (photo.length) {
        running.push(animate([...photo], { opacity: [0, 1], y: [10, 0] }, { ...enter, visualDuration: 0.7, delay: 0.1 }));
    }

    // Kartu contoh menyusul setelah fotonya, supaya terbaca sebagai bagian dari foto.
    const cards = document.querySelectorAll('[data-hero-card]');

    if (cards.length) {
        running.push(animate([...cards], { opacity: [0, 1], y: [18, 0], scale: [0.96, 1] }, {
            ...enterSoft,
            delay: stagger(0.1, { startDelay: 0.35 }),
        }));
    }

    Promise.all(running.map((animation) => animation.finished)).then(unhideHero, unhideHero);
}

// Yang disembunyikan hanya elemen yang memang masih di bawah layar, jadi tidak
// ada kedipan dan tidak ada isi yang ikut hilang kalau animasinya gagal jalan.
function hide(elements) {
    const pending = elements.filter((element) => ! alreadyVisible(element));

    pending.forEach((element) => {
        element.style.opacity = '0';
    });

    return pending;
}

// IntersectionObserver bisa ada tapi tidak pernah memanggil balik (skrip putus
// di tengah jalan, peramban yang dipalsukan). Kalau sampai batas waktu belum
// ada satu pun panggilan, semua yang tadi disembunyikan dikembalikan terlihat.
function revealIfObserverSilent(elements) {
    let alive = false;
    const probe = new IntersectionObserver(() => {
        alive = true;
        probe.disconnect();
    });

    probe.observe(document.documentElement);

    setTimeout(() => {
        if (alive) {
            return;
        }

        probe.disconnect();
        elements.forEach((element) => element.style.removeProperty('opacity'));
    }, 1200);
}

function reveals() {
    // Tanpa pengamat sama sekali, biarkan halaman tampil apa adanya.
    if (! ('IntersectionObserver' in window)) {
        return;
    }

    const hidden = [];

    document.querySelectorAll('[data-reveal]').forEach((element) => {
        const pending = hide([element]);

        if (! pending.length) {
            return;
        }

        hidden.push(...pending);

        onceInView(element, () => {
            animate(element, { opacity: [0, 1], y: [16, 0] }, enter);
        }, { amount: 0.25 });
    });

    document.querySelectorAll('[data-reveal-stagger]').forEach((list) => {
        const items = [...list.children];
        const pending = hide(items);

        if (! pending.length) {
            return;
        }

        hidden.push(...pending);

        onceInView(list, () => {
            animate(items, { opacity: [0, 1], y: [16, 0] }, { ...enter, delay: stagger(0.06) });
        }, { amount: 0.15 });
    });

    revealIfObserverSilent(hidden);
}

// Lingkaran cahaya di hero bergeser pelan saat digulir: memberi kesan kedalaman.
function glow() {
    const layer = document.querySelector('[data-hero-glow]');

    if (! layer) {
        return;
    }

    scroll(animate(layer, { y: [0, 120] }, { ease: 'linear' }), {
        target: layer.closest('section'),
        offset: ['start start', 'end start'],
    });
}

// Kartu terangkat sedikit saat disentuh kursor. Pakai scale, bukan y, supaya
// tidak bertabrakan dengan animasi masuk yang sudah memakai y.
function lift() {
    hover('[data-lift]', (element) => {
        animate(element, { scale: 1.02 }, { type: 'spring', bounce: 0.2, visualDuration: 0.18 });

        return () => animate(element, { scale: 1 }, { type: 'spring', bounce: 0.2, visualDuration: 0.25 });
    });
}

hero();
reveals();
glow();
lift();
