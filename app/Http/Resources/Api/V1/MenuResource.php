<?php

namespace Modules\Menu\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MenuResource extends JsonResource
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
            'status' => (bool) $this->status,

            // Restaurant (Outlet)
            'restaurant' => $this->whenLoaded('outlet', function () {
                return [
                    'id' => $this->outlet->id,
                    'uuid' => $this->outlet->uuid,
                    'name' => $this->outlet->name,
                    'address' => $this->outlet->address,
                    'phone' => $this->outlet->phone,
                    'logo' => $this->outlet->logo,
                    'latitude' => $this->outlet->latitude ? (float) $this->outlet->latitude : null,
                    'longitude' => $this->outlet->longitude ? (float) $this->outlet->longitude : null,
                ];
            }),

            // Menu Type
            'menu_type' => $this->whenLoaded('menuType', function () {
                return [
                    'id' => $this->menuType->id,
                    'name' => $this->menuType->name,
                ];
            }),

            // Categories → Products (nested)
            'categories' => CategoryResource::collection($this->whenLoaded('categories')),
        ];
    }
}
