<?php

namespace Modules\Menu\Http\Resources\Dashboard\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
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
            'description' => $this->description,
            'menu_id' => $this->menu_id,
            'image_url' => $this->image_url,
            'sort_order' => $this->sort_order,
            'status' => $this->status,
            'products_count' => $this->whenCounted('products'),
            'products' => $this->whenLoaded('products', fn () => $this->products->map(fn ($product) => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'price' => $product->price,
                'sale_price' => $product->sale_price,
                'status' => $product->status,
                'image_url' => $product->image_url,
                'pivot' => [
                    'price_override' => $product->pivot->price_override,
                    'sort_order' => $product->pivot->sort_order,
                    'is_available' => $product->pivot->is_available,
                ],
            ])),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
