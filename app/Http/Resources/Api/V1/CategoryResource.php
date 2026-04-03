<?php

namespace Modules\Menu\Http\Resources\Api\V1;

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
            'image_url' => $this->image_url,
            'product_type' => $this->product_type,
            'sort_order' => (int) $this->sort_order,
            'products' => ProductResource::collection($this->whenLoaded('products')),
        ];
    }
}
