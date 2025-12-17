<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function (): void {
            Route::middleware('web', 'auth', 'admin')
                ->prefix('admin')
                ->name('admin.')
                ->group(base_path('routes/admin.php'));

            Route::middleware('web', 'auth', 'approved', 'banero')
                ->prefix('banero')
                ->name('banero.')
                ->group(base_path('routes/banero.php'));
        }
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'approved' => \App\Http\Middleware\EnsureUserIsApproved::class,
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
            'banero' => \App\Http\Middleware\EnsureUserIsBanero::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
