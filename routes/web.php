<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Owner;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');

    Route::get('owner/setup', [Owner\OwnerProfileController::class, 'create'])->name('owner.setup');
    Route::post('owner/setup', [Owner\OwnerProfileController::class, 'store'])->name('owner.setup.store');

    Route::middleware('owner')->prefix('owner')->name('owner.')->group(function () {
        Route::get('/', Owner\DashboardController::class)->name('dashboard');

        Route::resource('items', Owner\ItemController::class)->except('show');
        Route::patch('items/{item}/status', Owner\ItemStatusController::class)->name('items.status');
        Route::post('items/{item}/images', [Owner\ItemImageController::class, 'store'])->name('items.images.store');
        Route::patch('items/{item}/images/{image}', [Owner\ItemImageController::class, 'update'])->name('items.images.update');
        Route::delete('items/{item}/images/{image}', [Owner\ItemImageController::class, 'destroy'])->name('items.images.destroy');
        Route::post('items/{item}/units', [Owner\ItemUnitController::class, 'store'])->name('items.units.store');
        Route::put('items/{item}/units/{unit}', [Owner\ItemUnitController::class, 'update'])->name('items.units.update');
        Route::delete('items/{item}/units/{unit}', [Owner\ItemUnitController::class, 'destroy'])->name('items.units.destroy');
    });

    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('categories', Admin\CategoryController::class)->except('show');
    });
});

require __DIR__.'/settings.php';
