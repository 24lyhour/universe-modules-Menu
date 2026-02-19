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

        // Get IDs of products already assigned to this category
        $assignedProductIds = $category->products->pluck('id')->toArray();

        // Get all active products EXCEPT those already assigned
        $allProducts = Product::where('status', 'active')
            ->whereNotIn('id', $assignedProductIds)
            ->orderBy('name')
            ->get(['id', 'name', 'sku', 'price', 'images'])
            ->map(fn ($product) => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'price' => $product->price,
                'image_url' => $product->images[0] ?? null,
            ]);

        $assignedProducts = $category->products->map(fn ($product) => [
            'id' => $product->id,
            'name' => $product->name,
            'sku' => $product->sku,
            'price' => $product->price,
            'image_url' => $product->images[0] ?? null,
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
