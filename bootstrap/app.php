<?php
// bootstrap/app.php

use App\Http\Middleware\EnsureUserIsActive;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // Satu file route per peran: mudah dicari, dan proteksi role ada di satu tempat.
            Route::middleware(['web', 'auth', 'role:super-admin|pesantren-admin'])
                ->prefix('admin')->name('admin.')
                ->group(base_path('routes/admin.php'));

            Route::middleware(['web', 'auth', 'role:counselor'])
                ->prefix('konselor')->name('counselor.')
                ->group(base_path('routes/counselor.php'));

            Route::middleware(['web', 'auth', 'role:academic-advisor'])
                ->prefix('pembimbing')->name('advisor.')
                ->group(base_path('routes/advisor.php'));

            Route::middleware(['web', 'auth', 'role:alumni'])
                ->prefix('alumni-saya')->name('alumni.')
                ->group(base_path('routes/alumni.php'));

            // Route santri tanpa prefix (/program, /tes, ...) sesuai rancangan Fase 1.
            Route::middleware(['web', 'auth', 'role:santri'])
                ->group(base_path('routes/santri.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
        ]);

        $middleware->web(append: [
            EnsureUserIsActive::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
