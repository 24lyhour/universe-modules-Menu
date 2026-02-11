<?php

use Illuminate\Support\Facades\Route;
use Modules\Menu\Http\Controllers\Dashboard\V1\MenuController;

Route::middleware(['auth', 'verified'])->prefix('dashboard')->name('menu.')->group(function () {
    // Menus
    Route::resource('menus', MenuController::class)->names('menus');
    Route::get('menus/{menu}/delete', [MenuController::class, 'confirmDelete'])->name('menus.confirm-delete');
});
