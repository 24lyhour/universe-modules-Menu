<?php

use Illuminate\Support\Facades\Route;
use Modules\Menu\Http\Controllers\Api\V1\Customer\Category\CategoryPublicController;
use Modules\Menu\Http\Controllers\Api\V1\Customer\Menu\MenuController;

/*
|--------------------------------------------------------------------------
| Public Routes (no authentication required)
|--------------------------------------------------------------------------
| These routes are for mobile app to browse menus and categories
*/
Route::prefix('v1')->group(function () {
    // Menu with nested restaurant → categories → products
    Route::get('menus', [MenuController::class, 'index'])
        ->name('menu.public.index');
    Route::get('menus/{menu}', [MenuController::class, 'show'])
        ->name('menu.public.show');

    // Menu types (Breakfast, Lunch, Dinner, etc.)
    Route::get('menu-types', [MenuController::class, 'menuTypes'])
        ->name('menu.public.types');

    // Public category listing
    Route::get('categories', [CategoryPublicController::class, 'index'])
        ->name('category.public.index');

    // Featured categories for home page
    Route::get('categories-featured', [CategoryPublicController::class, 'featured'])
        ->name('category.public.featured');

    // Single category detail with products
    Route::get('categories/{id}', [CategoryPublicController::class, 'show'])
        ->name('category.public.show');

    // Category products
    Route::get('categories/{identifier}/products', [CategoryPublicController::class, 'products'])
        ->name('category.public.products');
});

/*
|--------------------------------------------------------------------------
| Protected Routes (requires authentication)
|--------------------------------------------------------------------------
| These routes are for admin/dashboard API
| Currently managed via Dashboard controllers
*/
// Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
//     Route::apiResource('admin/menus', MenuController::class)->names('menu.admin');
//     Route::apiResource('admin/categories', CategoryController::class)->names('category.admin');
// });
