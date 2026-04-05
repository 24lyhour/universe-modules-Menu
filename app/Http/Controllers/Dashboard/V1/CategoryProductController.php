<?php

namespace Modules\Menu\Http\Controllers\Dashboard\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Momentum\Modal\Modal;
use Modules\Menu\Actions\Dashboard\V1\GetCategoryProductsManageDataAction;
use Modules\Menu\Actions\Dashboard\V1\SyncCategoryProductsAction;
use Illuminate\Http\Request;
use Modules\Menu\Models\Category;
use Modules\Product\Models\Product;

class CategoryProductController extends Controller
{
    public function __construct(
        protected GetCategoryProductsManageDataAction $getCategoryProductsManageDataAction,
        protected SyncCategoryProductsAction $syncCategoryProductsAction
    ) {}

    /**
     * Show modal for managing products in a category.
     */
    public function manageProducts(Request $request, Category $category): Modal
    {
        $data = $this->getCategoryProductsManageDataAction->execute($category);
        $returnTo = $request->input('return_to');
        $menuId = $request->input('menu_id');

        // Determine base route based on where user came from
        if ($returnTo === 'menu' && $menuId) {
            $baseRoute = 'menu.menus.categories.manage';
            $baseRouteParams = ['menu' => $menuId];
        } else {
            $baseRoute = 'menu.categories.index';
            $baseRouteParams = [];
        }

        // Pass return info to the modal
        $data['returnTo'] = $returnTo;
        $data['menuId'] = $menuId ?: null;

        return Inertia::modal('menu::dashboard/Category/ManageProducts', $data)
            ->baseRoute($baseRoute, $baseRouteParams);
    }

    /**
     * Sync products for a category.
     */
    public function syncProducts(Request $request, Category $category): RedirectResponse
    {
        $this->syncCategoryProductsAction->execute($category, $request->all());

        $returnTo = $request->input('return_to');
        $menuId = $request->input('menu_id');

        // Redirect based on where user came from
        if ($returnTo === 'menu' && $menuId) {
            return redirect()
                ->route('menu.menus.categories.manage', ['menu' => $menuId])
                ->with('success', 'Products updated successfully.');
        }

        return redirect()
            ->route('menu.categories.index')
            ->with('success', 'Products updated successfully.');
    }

    /**
     * Toggle product availability in a category.
     */
    public function toggleAvailability(Request $request, Category $category, Product $product): RedirectResponse
    {
        $request->validate([
            'is_available' => ['required', 'boolean'],
        ]);

        $category->products()->updateExistingPivot($product->id, [
            'is_available' => $request->boolean('is_available'),
        ]);

        return redirect()->back()->with('success', 'Product availability updated.');
    }

    /**
     * Reorder products for a category.
     */
    public function reorderProducts(Request $request, Category $category): RedirectResponse
    {
        $request->validate([
            'products' => ['required', 'array'],
            'products.*.id' => ['required', 'integer'],
            'products.*.sort_order' => ['required', 'integer', 'min:0'],
        ]);

        foreach ($request->products as $item) {
            $category->products()->updateExistingPivot($item['id'], [
                'sort_order' => $item['sort_order'],
            ]);
        }

        return redirect()->back()->with('success', 'Products reordered successfully.');
    }
}
