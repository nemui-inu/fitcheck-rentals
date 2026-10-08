<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth\SocialiteController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\MarketplaceController;
use App\Http\Controllers\Owner;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::get('auth/{provider}/redirect', [SocialiteController::class, 'redirect'])->name('social.redirect');
Route::get('auth/{provider}/callback', [SocialiteController::class, 'callback'])->name('social.callback');

Route::get('marketplace', [MarketplaceController::class, 'index'])->name('marketplace.index');
Route::get('marketplace/{item}', [MarketplaceController::class, 'show'])->name('marketplace.show');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');

    Route::get('bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::get('bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');
    Route::post('marketplace/{item}/bookings', [BookingController::class, 'store'])->name('bookings.store');
    Route::patch('bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');

    Route::get('owner/setup', [Owner\OwnerProfileController::class, 'create'])->name('owner.setup');
    Route::post('owner/setup', [Owner\OwnerProfileController::class, 'store'])->name('owner.setup.store');

    Route::middleware('owner')->prefix('owner')->name('owner.')->group(function () {
        Route::get('/', Owner\DashboardController::class)->name('dashboard');

        Route::resource('items', Owner\ItemController::class)->except('show');
        Route::patch('items/{item}/status', Owner\ItemStatusController::class)->name('items.status');
        Route::patch('items/{item}/resubmit', [Owner\ItemStatusController::class, 'resubmit'])->name('items.resubmit');
        Route::post('items/{item}/images', [Owner\ItemImageController::class, 'store'])->name('items.images.store');
        Route::patch('items/{item}/images/{image}', [Owner\ItemImageController::class, 'update'])->name('items.images.update');
        Route::delete('items/{item}/images/{image}', [Owner\ItemImageController::class, 'destroy'])->name('items.images.destroy');
        Route::post('items/{item}/units', [Owner\ItemUnitController::class, 'store'])->name('items.units.store');
        Route::put('items/{item}/units/{unit}', [Owner\ItemUnitController::class, 'update'])->name('items.units.update');
        Route::delete('items/{item}/units/{unit}', [Owner\ItemUnitController::class, 'destroy'])->name('items.units.destroy');

        Route::get('bookings', [Owner\BookingController::class, 'index'])->name('bookings.index');
        Route::patch('bookings/{booking}', [Owner\BookingController::class, 'update'])->name('bookings.update');
    });

    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('categories', Admin\CategoryController::class)->except('show');

        Route::get('users', [Admin\UserController::class, 'index'])->name('users.index');
        Route::patch('users/{user}/suspend', [Admin\UserController::class, 'suspend'])->name('users.suspend');
        Route::patch('users/{user}/unsuspend', [Admin\UserController::class, 'unsuspend'])->name('users.unsuspend');

        Route::get('items', [Admin\ItemReviewController::class, 'index'])->name('items.index');
        Route::patch('items/{item}/take-down', [Admin\ItemReviewController::class, 'takeDown'])->name('items.take-down');
        Route::patch('items/{item}/approve', [Admin\ItemReviewController::class, 'approve'])->name('items.approve');
    });
});

require __DIR__.'/settings.php';
