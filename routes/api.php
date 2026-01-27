<?php

use App\Http\Controllers\Api\SumReservationsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/sum/reservations/events', [SumReservationsController::class, 'events'])
        ->name('api.sum.reservations.events');
});
