<?php

use App\Http\Controllers\Api;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('throttle:60,1')->group(function () {
    Route::get('items', [Api\ItemController::class, 'index'])->name('api.items.index');
    Route::get('items/{item}', [Api\ItemController::class, 'show'])->name('api.items.show');
    Route::get('items/{item}/availability', [Api\ItemController::class, 'availability'])->name('api.items.availability');
    Route::get('categories', [Api\CategoryController::class, 'index'])->name('api.categories.index');
});
