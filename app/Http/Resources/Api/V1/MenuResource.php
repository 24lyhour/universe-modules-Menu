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
        $schedule = $this->scheduleSummary();
        $muted = $this->isCurrentlyMuted();

        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'name' => $this->name,
            'description' => $this->description,
            'image_url' => $this->image_url,
            'status' => (bool) $this->status,

            // Schedule summary — "Available 11:00–14:00" or null if always available.
            // Out-of-schedule menus are filtered out server-side, so this is a UI hint only.
            'schedule_hint' => $schedule['hint'],

            // Mute (ad-hoc unavailability — "out of stock", "kitchen prep", etc.)
            // Muted menus ARE returned so the app can render an unavailable state;
            // is_available is the single field the UI should gate on.
            'is_available' => !$muted,
            'is_muted' => $muted,
            'muted_until' => $muted ? $this->muted_until?->toIso8601String() : null,
            'muted_reason' => $muted ? $this->muted_reason : null,

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
