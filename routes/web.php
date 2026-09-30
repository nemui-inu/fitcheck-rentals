<?php

use App\Http\Controllers\Owner;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');

    Route::get('owner/setup', [Owner\OwnerProfileController::class, 'create'])->name('owner.setup');
    Route::post('owner/setup', [Owner\OwnerProfileController::class, 'store'])->name('owner.setup.store');

    Route::middleware('owner')->prefix('owner')->name('owner.')->group(function () {
        Route::get('/', Owner\DashboardController::class)->name('dashboard');
    });
});

require __DIR__.'/settings.php';
