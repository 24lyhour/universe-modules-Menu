<?php

namespace Modules\Menu\Actions\Dashboard\V1;

use Modules\Menu\Http\Resources\Dashboard\V1\CategoryResource;
use Modules\Menu\Models\Category;

class GetCategoryShowDataAction
{
    /**
     * Get category with products for show page.
     */
    public function execute(Category $category): array
    {
        $category->load(['products' => function ($query) {
            $query->orderBy('menu_category_products.sort_order');
        }]);

        $products = $category->products->map(fn ($product) => [
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
        ]);

        return [
            'category' => (new CategoryResource($category))->resolve(),
            'products' => $products,
        ];
    }
}
