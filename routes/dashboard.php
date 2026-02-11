<?php

use Illuminate\Support\Facades\Route;
use Modules\Menu\Http\Controllers\MenuController;



/**
 * menu module dashboard routes
 */
Route::middleware(['auth', 'verified', 'module:menu'])
    ->prefix('dashboard')
    ->group(function () {
        Route::resource('menus', MenuController::class)->names('menu');
    });