<?php

namespace Modules\Menu\Http\Controllers\Dashboard\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Momentum\Modal\Modal;
use Modules\Menu\Http\Resources\Dashboard\V1\CategoryResource;
use Modules\Menu\Http\Resources\Dashboard\V1\MenuResource;
use Modules\Menu\Models\Category;
use Modules\Menu\Models\Menu;

class MenuCategoryController extends Controller
{
    /**
     * Display categories with products for a specific menu.
     */
    public function manageCategories(Request $request, Menu $menu): Response
    {
        $menu->load(['outlet', 'menuType']);
        $filters = $request->only(['search', 'status']);

        $query = Category::where('menu_id', $menu->id)
            ->with(['products' => function ($q) {
                $q->orderBy('menu_category_products.sort_order');
            }])
            ->withCount('products');

        if (!empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status'] === '1' || $filters['status'] === 'active');
        }

        $categories = $query->orderBy('sort_order')->get();

        // Format categories with products
        $categoriesData = $categories->map(function ($category) {
            return [
                'id' => $category->id,
                'uuid' => $category->uuid,
                'name' => $category->name,
                'description' => $category->description,
                'image_url' => $category->image_url,
                'sort_order' => $category->sort_order,
                'status' => $category->status,
                'products_count' => $category->products_count,
                'products' => $category->products->map(fn ($product) => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'price' => $product->price,
                    'sale_price' => $product->sale_price,
                    'status' => $product->status,
                    'image_url' => $product->images[0] ?? null,
                    'pivot' => [
                        'price_override' => $product->pivot->price_override,
                        'sort_order' => $product->pivot->sort_order,
                        'is_available' => $product->pivot->is_available,
                    ],
                ]),
            ];
        });

        $stats = [
            'total' => Category::where('menu_id', $menu->id)->count(),
            'active' => Category::where('menu_id', $menu->id)->where('status', true)->count(),
            'inactive' => Category::where('menu_id', $menu->id)->where('status', false)->count(),
        ];

        return Inertia::render('menu::dashboard/Menu/ManageCategories', [
            'menu' => (new MenuResource($menu))->resolve(),
            'categories' => $categoriesData,
            'filters' => $filters,
            'stats' => $stats,
        ]);
    }

    /**
     * Reorder categories for a specific menu.
     */
    public function reorderCategories(Request $request, Menu $menu): RedirectResponse
    {
        $request->validate([
            'categories' => ['required', 'array'],
            'categories.*.id' => ['required', 'integer', 'exists:menu_categories,id'],
            'categories.*.sort_order' => ['required', 'integer', 'min:0'],
        ]);

        foreach ($request->categories as $item) {
            Category::where('id', $item['id'])
                ->where('menu_id', $menu->id)
                ->update(['sort_order' => $item['sort_order']]);
        }

        return redirect()->back()->with('success', 'Categories reordered successfully.');
    }

    /**
     * Show modal for assigning existing categories to a menu.
     */
    public function assignCategories(Menu $menu): Modal
    {
        $menu->load(['outlet', 'menuType']);

        // Get categories not assigned to any menu or assigned to other menus
        $availableCategories = Category::whereNull('menu_id')
            ->orWhere('menu_id', '!=', $menu->id)
            ->withCount('products')
            ->orderBy('name')
            ->get()
            ->map(fn ($category) => [
                'id' => $category->id,
                'name' => $category->name,
                'description' => $category->description,
                'image_url' => $category->image_url,
                'products_count' => $category->products_count,
                'current_menu_id' => $category->menu_id,
            ]);

        return Inertia::modal('menu::dashboard/Menu/AssignCategories', [
            'menu' => (new MenuResource($menu))->resolve(),
            'availableCategories' => $availableCategories,
        ])->baseRoute('menu.menus.categories.manage', ['menu' => $menu->id]);
    }

    /**
     * Assign categories to a menu.
     */
    public function syncAssignedCategories(Request $request, Menu $menu): RedirectResponse
    {
        $request->validate([
            'category_ids' => ['required', 'array'],
            'category_ids.*' => ['required', 'integer', 'exists:menu_categories,id'],
        ]);

        // Get max sort order for this menu
        $maxSortOrder = Category::where('menu_id', $menu->id)->max('sort_order') ?? 0;

        // Assign categories to this menu
        foreach ($request->category_ids as $index => $categoryId) {
            Category::where('id', $categoryId)->update([
                'menu_id' => $menu->id,
                'sort_order' => $maxSortOrder + $index + 1,
            ]);
        }

        return redirect()
            ->route('menu.menus.categories.manage', ['menu' => $menu->id])
            ->with('success', 'Categories assigned successfully.');
    }
}
