<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('admin.dashboard');
})->name('dashboard');

use App\Livewire\Admin\Buildings\Create as BuildingsCreate;
use App\Livewire\Admin\Buildings\Edit as BuildingsEdit;
use App\Livewire\Admin\Buildings\Index as BuildingsIndex;
use App\Livewire\Admin\Buildings\Units\Create as BuildingUnitsCreate;
use App\Livewire\Admin\Buildings\Units\Edit as BuildingUnitsEdit;
use App\Livewire\Admin\Buildings\Units\Index as BuildingUnitsIndex;
use App\Livewire\Admin\Expenses\Index as ExpensesIndex;
use App\Livewire\Admin\Pools\Index as PoolsIndex;
use App\Livewire\Admin\Pools\RegisterEntry as PoolsRegisterEntry;
use App\Livewire\Admin\Residents\Create as ResidentsCreate;
use App\Livewire\Admin\Residents\Edit as ResidentsEdit;
use App\Livewire\Admin\Residents\Index as ResidentsIndex;
use App\Livewire\Admin\Rules\Create as RulesCreate;
use App\Livewire\Admin\Rules\Edit as RulesEdit;
use App\Livewire\Admin\Rules\Index as RulesIndex;
use App\Livewire\Admin\Units\Create as UnitsCreate;
use App\Livewire\Admin\Units\Edit as UnitsEdit;
use App\Livewire\Admin\Units\Index as UnitsIndex;
use App\Livewire\Admin\Units\Show as UnitsShow;
use App\Livewire\Admin\UnitUsers\Create as UnitUsersCreate;
use App\Livewire\Admin\UnitUsers\Edit as UnitUsersEdit;
use App\Livewire\Admin\UnitUsers\Index as UnitUsersIndex;
use App\Livewire\Admin\UnitUsers\Show as UnitUsersShow;
use App\Livewire\Admin\Users\Create as UsersCreate;
use App\Livewire\Admin\Users\Edit as UsersEdit;
use App\Livewire\Admin\Users\Index as UsersIndex;

Route::prefix('users')->name('users.')->group(function () {
    Route::get('/', UsersIndex::class)->name('index');
    Route::get('/create', UsersCreate::class)->name('create');
    Route::get('/{user}/edit', UsersEdit::class)->name('edit');
});

Route::prefix('units')->name('units.')->group(function () {
    Route::get('/', UnitsIndex::class)->name('index');
    Route::get('/create', UnitsCreate::class)->name('create');
    Route::get('/{unit}', UnitsShow::class)->name('show');
    Route::get('/{unit}/edit', UnitsEdit::class)->name('edit');
});

Route::prefix('unit-users')->name('unit-users.')->group(function () {
    Route::get('/', UnitUsersIndex::class)->name('index');
    Route::get('/create', UnitUsersCreate::class)->name('create');
    Route::get('/{unitUser}', UnitUsersShow::class)->name('show');
    Route::get('/{unitUser}/edit', UnitUsersEdit::class)->name('edit');
});

Route::prefix('residents')->name('residents.')->group(function () {
    Route::get('/', ResidentsIndex::class)->name('index');
    Route::get('/create', ResidentsCreate::class)->name('create');
    Route::get('/{resident}/edit', ResidentsEdit::class)->name('edit');
});

Route::prefix('rules')->name('rules.')->group(function () {
    Route::get('/', RulesIndex::class)->name('index');
    Route::get('/create', RulesCreate::class)->name('create');
    Route::get('/{rule}/edit', RulesEdit::class)->name('edit');
});

Route::prefix('buildings')->name('buildings.')->group(function () {
    Route::get('/', BuildingsIndex::class)->name('index');
    Route::get('/create', BuildingsCreate::class)->name('create');
    Route::get('/{building}/edit', BuildingsEdit::class)->name('edit');

    Route::prefix('{building}/units')->name('units.')->group(function () {
        Route::get('/', BuildingUnitsIndex::class)->name('index');
        Route::get('/create', BuildingUnitsCreate::class)->name('create');
        Route::get('/{unit}/edit', BuildingUnitsEdit::class)->name('edit');
    });
});

Route::prefix('expenses')->name('expenses.')->group(function () {
    Route::get('/', ExpensesIndex::class)->name('index');
    // Route::get('/create', ExpensesCreate::class)->name('create');
    // Route::get('/{expense}', ExpensesShow::class)->name('show');
});

Route::prefix('pools')->name('pools.')->group(function () {
    Route::get('/', PoolsIndex::class)->name('index');
    Route::get('/register-entry', PoolsRegisterEntry::class)->name('register-entry');
});
