<?php

namespace Modules\Menu\Http\Controllers\Dashboard\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Momentum\Modal\Modal;
use Modules\Menu\Actions\Dashboard\V1\GetMenuAssignCategoriesDataAction;
use Modules\Menu\Actions\Dashboard\V1\GetMenuCategoriesManageDataAction;
use Modules\Menu\Actions\Dashboard\V1\ReorderMenuCategoriesAction;
use Modules\Menu\Actions\Dashboard\V1\SyncMenuCategoriesAction;
use Modules\Menu\Http\Requests\Dashboard\V1\ReorderMenuCategoriesRequest;
use Modules\Menu\Http\Requests\Dashboard\V1\SyncMenuCategoriesRequest;
use Modules\Menu\Models\Menu;

class MenuCategoryController extends Controller
{
    public function __construct(
        protected GetMenuCategoriesManageDataAction $getMenuCategoriesManageDataAction,
        protected GetMenuAssignCategoriesDataAction $getMenuAssignCategoriesDataAction,
        protected SyncMenuCategoriesAction $syncMenuCategoriesAction,
        protected ReorderMenuCategoriesAction $reorderMenuCategoriesAction
    ) {}

    /**
     * Display categories with products for a specific menu.
     */
    public function manageCategories(Request $request, Menu $menu): Response
    {
        $filters = $request->only(['search', 'status']);
        $data = $this->getMenuCategoriesManageDataAction->execute($menu, $filters);

        return Inertia::render('menu::dashboard/Menu/ManageCategories', $data);
    }

    /**
     * Reorder categories for a specific menu.
     */
    public function reorderCategories(ReorderMenuCategoriesRequest $request, Menu $menu): RedirectResponse
    {
        $this->reorderMenuCategoriesAction->execute($menu, $request->validated()['categories']);

        return redirect()->back()->with('success', 'Categories reordered successfully.');
    }

    /**
     * Show modal for assigning existing categories to a menu.
     */
    public function assignCategories(Menu $menu): Modal
    {
        $data = $this->getMenuAssignCategoriesDataAction->execute($menu);

        return Inertia::modal('menu::dashboard/Menu/AssignCategories', $data)
            ->baseRoute('menu.menus.categories.manage', ['menu' => $menu->id]);
    }

    /**
     * Assign categories to a menu.
     */
    public function syncAssignedCategories(SyncMenuCategoriesRequest $request, Menu $menu): RedirectResponse
    {
        $this->syncMenuCategoriesAction->execute($menu, $request->validated()['category_ids']);

        return redirect()
            ->route('menu.menus.categories.manage', ['menu' => $menu->id])
            ->with('success', 'Categories assigned successfully.');
    }
}
