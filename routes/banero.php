<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('banero.my-shift');
})->name('dashboard');

Route::get('/my-shift', \App\Livewire\Banero\MyShift::class)->name('my-shift');

Route::prefix('pools')->name('pools.')->group(function () {
    Route::get('/scanner', \App\Livewire\Banero\Pools\Scanner::class)->name('scanner');
    Route::get('/inside', \App\Livewire\Banero\Pools\Inside::class)->name('inside');
});
