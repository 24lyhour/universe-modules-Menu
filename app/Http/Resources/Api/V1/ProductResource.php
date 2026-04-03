<?php

namespace Modules\Menu\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'sku' => $this->sku,
            'price' => (float) $this->price,
            'sale_price' => $this->sale_price ? (float) $this->sale_price : null,
            'stock' => (int) $this->stock,
            'status' => $this->status,
            'is_featured' => (bool) $this->is_featured,
            'images' => $this->images,

            // Pivot data from menu_category_products
            'price_override' => $this->whenPivotLoaded('menu_category_products', function () {
                return $this->pivot->price_override ? (float) $this->pivot->price_override : null;
            }),
            'is_available' => $this->whenPivotLoaded('menu_category_products', function () {
                return (bool) $this->pivot->is_available;
            }),
            'sort_order' => $this->whenPivotLoaded('menu_category_products', function () {
                return (int) $this->pivot->sort_order;
            }),
        ];
    }
}
