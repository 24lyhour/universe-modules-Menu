<?php

use Illuminate\Support\Facades\Route;
use Modules\Menu\Http\Controllers\Dashboard\V1\CategoryController;
use Modules\Menu\Http\Controllers\Dashboard\V1\MenuController;
use Modules\Menu\Http\Controllers\Dashboard\V1\MenuTypeController;
use Modules\Menu\Http\Controllers\Dashboard\V1\CategoryProductController;
use Modules\Menu\Http\Controllers\Dashboard\V1\MenuCategoryController;

Route::middleware(['auth', 'verified'])->prefix('dashboard')->name('menu.')->group(function () {
    // Menus
    Route::resource('menus', MenuController::class)->names('menus');
    Route::get('menus/{menu}/delete', [MenuController::class, 'confirmDelete'])->name('menus.confirm-delete');
    Route::put('menus/{menu}/toggle-status', [MenuController::class, 'toggleStatus'])->name('menus.toggle-status');
    Route::get('menus/{menu}/categories/manage', [MenuCategoryController::class, 'manageCategories'])->name('menus.categories.manage');
    Route::post('menus/{menu}/categories/reorder', [MenuCategoryController::class, 'reorderCategories'])->name('menus.categories.reorder');
    Route::get('menus/{menu}/categories/assign', [MenuCategoryController::class, 'assignCategories'])->name('menus.categories.assign');
    Route::post('menus/{menu}/categories/sync', [MenuCategoryController::class, 'syncAssignedCategories'])->name('menus.categories.sync');

    // Categories
    Route::resource('categories', CategoryController::class)->names('categories');
    Route::get('categories/{category}/delete', [CategoryController::class, 'confirmDelete'])->name('categories.confirm-delete');
    Route::put('categories/{category}/toggle-status', [CategoryController::class, 'toggleStatus'])->name('categories.toggle-status');
    Route::get('categories/{category}/products/manage', [CategoryProductController::class, 'manageProducts'])->name('categories.products.manage');
    Route::post('categories/{category}/products/sync', [CategoryProductController::class, 'syncProducts'])->name('categories.products.sync');
    Route::post('categories/{category}/products/reorder', [CategoryProductController::class, 'reorderProducts'])->name('categories.products.reorder');

    // Menu Types
    Route::resource('menu-types', MenuTypeController::class)->names('menu-types');
    Route::get('menu-types/{menu_type}/delete', [MenuTypeController::class, 'confirmDelete'])->name('menu-types.confirm-delete');
    Route::put('menu-types/{menu_type}/toggle-status', [MenuTypeController::class, 'toggleStatus'])->name('menu-types.toggle-status');
});
