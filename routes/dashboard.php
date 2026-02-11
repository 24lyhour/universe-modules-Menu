<?php

use Illuminate\Support\Facades\Route;
use Modules\Menu\Http\Controllers\Dashboard\V1\CategoryController;
use Modules\Menu\Http\Controllers\Dashboard\V1\MenuController;

Route::middleware(['auth', 'verified'])->prefix('dashboard')->name('menu.')->group(function () {
    // Menus
    Route::resource('menus', MenuController::class)->names('menus');
    Route::get('menus/{menu}/delete', [MenuController::class, 'confirmDelete'])->name('menus.confirm-delete');
    Route::put('menus/{menu}/toggle-status', [MenuController::class, 'toggleStatus'])->name('menus.toggle-status');

    // Categories
    Route::resource('categories', CategoryController::class)->names('categories');
    Route::get('categories/{category}/delete', [CategoryController::class, 'confirmDelete'])->name('categories.confirm-delete');
    Route::put('categories/{category}/toggle-status', [CategoryController::class, 'toggleStatus'])->name('categories.toggle-status');
});
