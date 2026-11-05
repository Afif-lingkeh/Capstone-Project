import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

// Animasi beranda dipecah ke chunk sendiri: halaman lain tidak perlu mengunduhnya.
// Kalau pengguna meminta pengurangan gerak, berkasnya tidak diunduh sama sekali;
// tanpa kelas .js-reveal seluruh isi beranda memang sudah tampil apa adanya.
if (document.querySelector('[data-home-motion]') && ! matchMedia('(prefers-reduced-motion: reduce)').matches) {
    import('./home-motion');
}
