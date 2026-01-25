<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Admin\Dashboard;

Route::get('/', Dashboard::class)->name('dashboard');

use App\Livewire\Admin\Buildings\Create as BuildingsCreate;
use App\Livewire\Admin\Buildings\Edit as BuildingsEdit;
use App\Livewire\Admin\Buildings\Index as BuildingsIndex;
use App\Livewire\Admin\Buildings\Units\Create as BuildingUnitsCreate;
use App\Livewire\Admin\Buildings\Units\Edit as BuildingUnitsEdit;
use App\Livewire\Admin\Buildings\Units\Index as BuildingUnitsIndex;
use App\Livewire\Admin\Expenses\Index as ExpensesIndex;
use App\Livewire\Admin\News\Create as NewsCreate;
use App\Livewire\Admin\News\Edit as NewsEdit;
use App\Livewire\Admin\News\Index as NewsIndex;
use App\Livewire\Admin\Pools\Index as PoolsIndex;
use App\Livewire\Admin\Pools\RegisterEntry as PoolsRegisterEntry;
use App\Livewire\Admin\Pools\Settings as PoolsSettings;
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
use App\Livewire\Admin\Amenities\Create as AmenitiesCreate;
use App\Livewire\Admin\Amenities\Edit as AmenitiesEdit;
use App\Livewire\Admin\Amenities\Index as AmenitiesIndex;
use App\Livewire\Admin\Baneros\Index as BanerosIndex;

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

Route::prefix('news')->name('news.')->group(function () {
    Route::get('/', NewsIndex::class)->name('index');
    Route::get('/create', NewsCreate::class)->name('create');
    Route::get('/{news}/edit', NewsEdit::class)->name('edit');
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
    Route::get('/settings', PoolsSettings::class)->name('settings');
    Route::get('/scanner', \App\Livewire\Banero\Pools\Scanner::class)->name('scanner');
});

Route::prefix('amenities')->name('amenities.')->group(function () {
    Route::get('/', AmenitiesIndex::class)->name('index');
    Route::get('/create', AmenitiesCreate::class)->name('create');
    Route::get('/{amenity}/edit', AmenitiesEdit::class)->name('edit');
});

Route::prefix('baneros')->name('baneros.')->group(function () {
    Route::get('/', BanerosIndex::class)->name('index');
});

// SUM - Salón de Usos Múltiples
use App\Livewire\Admin\Sum\Reservations\Index as SumReservationsIndex;
use App\Livewire\Admin\Sum\Payments\Index as SumPaymentsIndex;
use App\Livewire\Admin\Sum\Settings as SumSettings;

Route::prefix('sum')->name('sum.')->group(function () {
    Route::prefix('reservations')->name('reservations.')->group(function () {
        Route::get('/', SumReservationsIndex::class)->name('index');
    });

    Route::prefix('payments')->name('payments.')->group(function () {
        Route::get('/', SumPaymentsIndex::class)->name('index');
    });

    Route::get('/settings', SumSettings::class)->name('settings');
});
