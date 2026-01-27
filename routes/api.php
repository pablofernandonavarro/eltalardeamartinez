<?php

use App\Http\Controllers\Api\SumReservationsController;
use Illuminate\Support\Facades\Route;

// Public API routes with sanctum auth
Route::middleware(['auth:sanctum'])->group(function () {
    // Add other API routes here if needed
});

// Web-based API routes with session auth (for AJAX calls from authenticated pages)
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/sum/reservations/events', [SumReservationsController::class, 'events'])
        ->name('api.sum.reservations.events');
});
