# PEDULI – Smart Career Center

Projek capstone untuk membuat aplikasi Career Center bagi santri Pesantren Modern Daarul 'Uluum Lido.

**Stack:** Laravel 12 · Blade + Tailwind CSS + Alpine.js · MySQL · Breeze · Spatie Permission · Pest

## Menjalankan di komputer sendiri

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
composer run dev
```

## Deployment

Lihat [docs/DEPLOYMENT.md](docs/DEPLOYMENT.md).
