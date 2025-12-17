<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Cierre automático de ingresos abiertos (fin de día)
app()->booted(function () {
    /** @var Schedule $schedule */
    $schedule = app(Schedule::class);

    // A las 23:59 se cierran ingresos abiertos de días anteriores
    $schedule->command('pools:close-open-entries')->dailyAt('23:59');
});
