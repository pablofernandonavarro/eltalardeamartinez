<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('banero.pools.scan');
})->name('dashboard');

Route::prefix('pools')->name('pools.')->group(function () {
    Route::get('/scan', \App\Livewire\Banero\Pools\Scanner::class)->name('scan');
    Route::get('/inside', \App\Livewire\Banero\Pools\Inside::class)->name('inside');
});
