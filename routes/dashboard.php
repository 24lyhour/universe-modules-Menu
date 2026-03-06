<?php

use Illuminate\Support\Facades\Route;
use Modules\Menu\Http\Controllers\Dashboard\V1\CategoryController;
use Modules\Menu\Http\Controllers\Dashboard\V1\CategoryProductController;
use Modules\Menu\Http\Controllers\Dashboard\V1\CategoryStatusController;
use Modules\Menu\Http\Controllers\Dashboard\V1\MenuCategoryController;
use Modules\Menu\Http\Controllers\Dashboard\V1\MenuController;
use Modules\Menu\Http\Controllers\Dashboard\V1\MenuScheduleController;
use Modules\Menu\Http\Controllers\Dashboard\V1\MenuStatusController;
use Modules\Menu\Http\Controllers\Dashboard\V1\MenuTypeController;
use Modules\Menu\Http\Controllers\Dashboard\V1\MenuTypeStatusController;

Route::middleware(['auth', 'verified', 'auto.permission'])->prefix('dashboard')->name('menu.')->group(function () {
    // Menus
    Route::resource('menus', MenuController::class)->names('menus');
    Route::get('menus/{menu}/delete', [MenuController::class, 'confirmDelete'])->name('menus.confirm-delete');
    Route::put('menus/{menu}/toggle-status', MenuStatusController::class)->name('menus.toggle-status');
    Route::get('menus/{menu}/schedule', [MenuScheduleController::class, 'show'])->name('menus.schedule');
    Route::put('menus/{menu}/schedule', [MenuScheduleController::class, 'update'])->name('menus.update-schedule');
    Route::get('menus/{menu}/categories/manage', [MenuCategoryController::class, 'manageCategories'])->name('menus.categories.manage');
    Route::post('menus/{menu}/categories/reorder', [MenuCategoryController::class, 'reorderCategories'])->name('menus.categories.reorder');
    Route::get('menus/{menu}/categories/assign', [MenuCategoryController::class, 'assignCategories'])->name('menus.categories.assign');
    Route::post('menus/{menu}/categories/sync', [MenuCategoryController::class, 'syncAssignedCategories'])->name('menus.categories.sync');

    // Menu Trash, Export, Import & Bulk Operations
    Route::get('menus/trash', [MenuController::class, 'trash'])->name('menus.trash');
    Route::get('menus/export', [MenuController::class, 'export'])->name('menus.export');
    Route::get('menus/import', [MenuController::class, 'import'])->name('menus.import');
    Route::post('menus/import/preview', [MenuController::class, 'previewImport'])->name('menus.import.preview');
    Route::post('menus/import/process', [MenuController::class, 'processImport'])->name('menus.import.process');
    Route::get('menus/import/template', [MenuController::class, 'template'])->name('menus.import.template');
    Route::put('menus/{uuid}/restore', [MenuController::class, 'restore'])->name('menus.restore');
    Route::delete('menus/{uuid}/force-delete', [MenuController::class, 'forceDelete'])->name('menus.force-delete');
    Route::get('menus/bulk-delete', [MenuController::class, 'confirmBulkDelete'])->name('menus.bulk-delete.confirm');
    Route::delete('menus/bulk-delete', [MenuController::class, 'bulkDelete'])->name('menus.bulk-delete');
    Route::put('menus/trash/bulk-restore', [MenuController::class, 'bulkRestore'])->name('menus.bulk-restore');
    Route::delete('menus/trash/bulk-force-delete', [MenuController::class, 'bulkForceDelete'])->name('menus.bulk-force-delete');
    Route::delete('menus/trash/empty', [MenuController::class, 'emptyTrash'])->name('menus.empty-trash');

    // Categories
    Route::resource('categories', CategoryController::class)->names('categories');
    Route::get('categories/{category}/delete', [CategoryController::class, 'confirmDelete'])->name('categories.confirm-delete');
    Route::put('categories/{category}/toggle-status', CategoryStatusController::class)->name('categories.toggle-status');
    Route::get('categories/{category}/products/manage', [CategoryProductController::class, 'manageProducts'])->name('categories.products.manage');
    Route::post('categories/{category}/products/sync', [CategoryProductController::class, 'syncProducts'])->name('categories.products.sync');
    Route::post('categories/{category}/products/reorder', [CategoryProductController::class, 'reorderProducts'])->name('categories.products.reorder');

    // Category Trash, Export, Import & Bulk Operations
    Route::get('categories/trash', [CategoryController::class, 'trash'])->name('categories.trash');
    Route::get('categories/export', [CategoryController::class, 'export'])->name('categories.export');
    Route::get('categories/import', [CategoryController::class, 'import'])->name('categories.import');
    Route::post('categories/import/preview', [CategoryController::class, 'previewImport'])->name('categories.import.preview');
    Route::post('categories/import/process', [CategoryController::class, 'processImport'])->name('categories.import.process');
    Route::get('categories/import/template', [CategoryController::class, 'template'])->name('categories.import.template');
    Route::put('categories/{uuid}/restore', [CategoryController::class, 'restore'])->name('categories.restore');
    Route::delete('categories/{uuid}/force-delete', [CategoryController::class, 'forceDelete'])->name('categories.force-delete');
    Route::get('categories/bulk-delete', [CategoryController::class, 'confirmBulkDelete'])->name('categories.bulk-delete.confirm');
    Route::delete('categories/bulk-delete', [CategoryController::class, 'bulkDelete'])->name('categories.bulk-delete');
    Route::put('categories/trash/bulk-restore', [CategoryController::class, 'bulkRestore'])->name('categories.bulk-restore');
    Route::delete('categories/trash/bulk-force-delete', [CategoryController::class, 'bulkForceDelete'])->name('categories.bulk-force-delete');
    Route::delete('categories/trash/empty', [CategoryController::class, 'emptyTrash'])->name('categories.empty-trash');

    // Menu Types
    Route::resource('menu-types', MenuTypeController::class)->names('menu-types');
    Route::get('menu-types/{menu_type}/delete', [MenuTypeController::class, 'confirmDelete'])->name('menu-types.confirm-delete');
    Route::put('menu-types/{menu_type}/toggle-status', MenuTypeStatusController::class)->name('menu-types.toggle-status');

    // Menu Type Trash, Export, Import & Bulk Operations
    Route::get('menu-types/trash', [MenuTypeController::class, 'trash'])->name('menu-types.trash');
    Route::get('menu-types/export', [MenuTypeController::class, 'export'])->name('menu-types.export');
    Route::get('menu-types/import', [MenuTypeController::class, 'import'])->name('menu-types.import');
    Route::post('menu-types/import/preview', [MenuTypeController::class, 'previewImport'])->name('menu-types.import.preview');
    Route::post('menu-types/import/process', [MenuTypeController::class, 'processImport'])->name('menu-types.import.process');
    Route::get('menu-types/import/template', [MenuTypeController::class, 'template'])->name('menu-types.import.template');
    Route::put('menu-types/{uuid}/restore', [MenuTypeController::class, 'restore'])->name('menu-types.restore');
    Route::delete('menu-types/{uuid}/force-delete', [MenuTypeController::class, 'forceDelete'])->name('menu-types.force-delete');
    Route::get('menu-types/bulk-delete', [MenuTypeController::class, 'confirmBulkDelete'])->name('menu-types.bulk-delete.confirm');
    Route::delete('menu-types/bulk-delete', [MenuTypeController::class, 'bulkDelete'])->name('menu-types.bulk-delete');
    Route::put('menu-types/trash/bulk-restore', [MenuTypeController::class, 'bulkRestore'])->name('menu-types.bulk-restore');
    Route::delete('menu-types/trash/bulk-force-delete', [MenuTypeController::class, 'bulkForceDelete'])->name('menu-types.bulk-force-delete');
    Route::delete('menu-types/trash/empty', [MenuTypeController::class, 'emptyTrash'])->name('menu-types.empty-trash');
});
