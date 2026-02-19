<?php

namespace Modules\Menu\Actions\Dashboard\V1;

use Modules\Menu\Http\Resources\Dashboard\V1\MenuResource;
use Modules\Menu\Models\Category;
use Modules\Menu\Models\Menu;

class GetMenuShowDataAction
{
    /**
     * Get data for showing a menu with categories and products.
     */
    public function execute(Menu $menu): array
    {
        $menu->load(['outlet', 'menuType']);

        $categories = Category::where('menu_id', $menu->id)
            ->with(['products' => function ($q) {
                $q->orderBy('menu_category_products.sort_order');
            }])
            ->withCount('products')
            ->orderBy('sort_order')
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
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
                    ]),
                ];
            });

        return [
            'menu' => (new MenuResource($menu))->resolve(),
            'categories' => $categories,
        ];
    }
}
