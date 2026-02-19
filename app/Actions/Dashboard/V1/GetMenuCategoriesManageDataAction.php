<?php

namespace Modules\Menu\Actions\Dashboard\V1;

use Modules\Menu\Http\Resources\Dashboard\V1\MenuResource;
use Modules\Menu\Models\Category;
use Modules\Menu\Models\Menu;

class GetMenuCategoriesManageDataAction
{
    /**
     * Get data for managing categories in a menu.
     */
    public function execute(Menu $menu, array $filters = []): array
    {
        $menu->load(['outlet', 'menuType']);

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

        return [
            'menu' => (new MenuResource($menu))->resolve(),
            'categories' => $categoriesData,
            'filters' => $filters,
            'stats' => $stats,
        ];
    }
}
