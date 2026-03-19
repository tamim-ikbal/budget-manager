<?php

use App\Http\Controllers\Admin\CurrencyController;
use App\Http\Controllers\CategoryController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified', 'workspace.context'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');

    Route::resource('categories', CategoryController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->scoped(['category' => 'uid']);
});

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'verified', 'admin'])
    ->group(function (): void {
        Route::inertia('/', 'admin/Dashboard')->name('dashboard');

        Route::resource('currencies', CurrencyController::class)
            ->only(['index', 'store', 'update', 'destroy'])
            ->scoped(['currency' => 'uid']);

        Route::patch('currencies/{currency:uid}/default', [CurrencyController::class, 'setDefault'])
            ->name('currencies.default.update');
    });

require __DIR__.'/settings.php';
