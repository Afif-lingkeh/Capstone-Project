<?php
// app/Providers/AppServiceProvider.php

namespace App\Providers;

use App\Support\Navigation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Saat development, error jika lupa eager loading (mencegah masalah N+1 query).
        Model::preventLazyLoading(! $this->app->isProduction());

        // Nama hari/bulan dan "3 hari yang lalu" dalam Bahasa Indonesia.
        Carbon::setLocale(config('app.locale'));

        Password::defaults(fn () => Password::min(8)->letters()->numbers());

        View::composer(['layouts.app', '*.dashboard'], function ($view) {
            $user = auth()->user();
            $view->with('navigation', $user ? Navigation::for($user) : []);
        });
    }
}
