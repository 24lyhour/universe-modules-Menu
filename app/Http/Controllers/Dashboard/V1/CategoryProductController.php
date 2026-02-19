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
    public function manageProducts(Category $category): Modal
    {
        $data = $this->getCategoryProductsManageDataAction->execute($category);

        return Inertia::modal('menu::dashboard/Category/ManageProducts', $data)
            ->baseRoute('menu.categories.index');
    }

    /**
     * Sync products for a category.
     */
    public function syncProducts(Request $request, Category $category): RedirectResponse
    {
        $this->syncCategoryProductsAction->execute($category, $request->all());

        return redirect()
            ->route('menu.categories.index')
            ->with('success', 'Products updated successfully.');
    }

    /**
     * Reorder products in a category.
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
