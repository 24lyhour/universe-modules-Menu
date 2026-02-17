<?php

namespace Modules\Menu\Actions\Dashboard\V1;

use Modules\Menu\Models\Category;
use Modules\Product\Models\Product;

class GetCategoryProductsManageDataAction
{
    /**
     * Get data for managing category products.
     */
    public function execute(Category $category): array
    {
        $category->load('products');

        $allProducts = Product::where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'sku', 'price', 'image_url']);

        $assignedProducts = $category->products->map(fn ($product) => [
            'id' => $product->id,
            'name' => $product->name,
            'sku' => $product->sku,
            'price' => $product->price,
            'image_url' => $product->image_url,
            'pivot' => [
                'price_override' => $product->pivot->price_override,
                'sort_order' => $product->pivot->sort_order,
                'is_available' => $product->pivot->is_available,
            ],
        ])->toArray();

        return [
            'category' => [
                'id' => $category->id,
                'name' => $category->name,
            ],
            'allProducts' => $allProducts,
            'assignedProducts' => $assignedProducts,
        ];
    }
}
