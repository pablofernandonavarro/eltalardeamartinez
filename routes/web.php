<?php

use App\Http\Controllers\DocumentController;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\Settings\TwoFactor;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::get('/', function () {
    $news = \App\Models\News::published()
        ->orderBy('order')
        ->orderBy('event_date', 'desc')
        ->limit(3)
        ->get();

    $amenities = \App\Models\Amenity::active()
        ->ordered()
        ->get();

    return view('landing', compact('news', 'amenities'));
})->name('home');

Route::get('/descargar-reglamento', [DocumentController::class, 'downloadRegulation'])->name('regulation.download');

// Ruta pública para aceptar invitación de residente
Route::get('/invitacion/{token}', \App\Livewire\Resident\AcceptInvitation::class)
    ->name('resident.accept-invitation');

Route::get('dashboard', function () {
    $user = auth()->user();

    if ($user->isAdmin()) {
        return redirect()->route('admin.dashboard');
    }

    if ($user->isBanero()) {
        return redirect()->route('banero.dashboard');
    }

    return redirect()->route('resident.dashboard');
})->middleware(['auth', 'verified', 'approved'])
    ->name('dashboard');

Route::middleware(['auth', 'approved'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('profile.edit');
    Route::get('settings/password', Password::class)->name('user-password.edit');
    Route::get('settings/appearance', Appearance::class)->name('appearance.edit');

    Route::get('settings/two-factor', TwoFactor::class)
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                    && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');

    // Resident routes (for Propietarios and Inquilinos)
    Route::prefix('resident')->name('resident.')->group(function () {
        Route::get('/dashboard', \App\Livewire\Resident\Dashboard::class)->name('dashboard');

        Route::get('/household', \App\Livewire\Resident\Unit\Household::class)->name('household');

        Route::prefix('pools')->name('pools.')->group(function () {
            // Ruta unificada para QR personal y day-pass
            Route::get('/my-qr', \App\Livewire\Resident\MyPoolQrUnified::class)->name('my-qr');
            
            // Redirects de las rutas antiguas a la unificada
            Route::get('/day-pass', function () {
                return redirect()->route('resident.pools.my-qr');
            })->name('day-pass');

            Route::prefix('guests')->name('guests.')->group(function () {
                Route::get('/', \App\Livewire\Resident\Pools\Guests\Index::class)->name('index'); // Legacy redirect
                Route::get('/manage', \App\Livewire\Resident\Pools\Guests\Index::class)->name('manage');
                Route::get('/used', \App\Livewire\Resident\Pools\Guests\UsedGuests::class)->name('used');
                Route::get('/create', \App\Livewire\Resident\Pools\Guests\Create::class)->name('create');
                Route::get('/{guest}/edit', \App\Livewire\Resident\Pools\Guests\Edit::class)->name('edit');
            });
        });

        // Route::get('/expenses', \App\Livewire\Resident\Expenses\Index::class)->name('expenses.index');
        // Route::get('/pools', \App\Livewire\Resident\Pools\Index::class)->name('pools.index');
    });
});
