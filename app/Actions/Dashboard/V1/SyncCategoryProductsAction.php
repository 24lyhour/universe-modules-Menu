<?php

namespace Modules\Menu\Actions\Dashboard\V1;

use Modules\Menu\Models\Category;
use Illuminate\Support\Facades\Validator;

class SyncCategoryProductsAction
{
    /**
     * Sync products for a category.
     */
    public function execute(Category $category, array $data): void
    {
        $validated = $this->validate($data);
        
        $syncData = [];
        
        foreach ($validated['products'] ?? [] as $index => $product) {
            $syncData[$product['id']] = [
                'price_override' => $product['price_override'] ?? null,
                'sort_order' => $product['sort_order'] ?? $index,
                'is_available' => $product['is_available'] ?? true,
            ];
        }

        $category->products()->sync($syncData);
    }

    /**
     * Validate the data.
     */
    protected function validate(array $data): array
    {
        return Validator::make($data, [
            'products' => ['nullable', 'array'],
            'products.*.id' => ['required', 'exists:products,id'],
            'products.*.price_override' => ['nullable', 'numeric', 'min:0'],
            'products.*.sort_order' => ['nullable', 'integer', 'min:0'],
            'products.*.is_available' => ['nullable', 'boolean'],
        ], [
            'products.*.id.required' => 'Product ID is required.',
            'products.*.id.exists' => 'One or more selected products do not exist.',
            'products.*.price_override.numeric' => 'Price override must be a number.',
            'products.*.price_override.min' => 'Price override must be at least 0.',
            'products.*.sort_order.integer' => 'Sort order must be an integer.',
            'products.*.sort_order.min' => 'Sort order must be at least 0.',
        ])->validate();
    }
}
