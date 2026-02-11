<?php

namespace Modules\Menu\Http\Resources;

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
            'status' => $this->status,
            'schedule_mode' => $this->schedule_mode,
            'schedule_days' => $this->schedule_days,
            'schedule_start_time' => $this->schedule_start_time,
            'schedule_end_time' => $this->schedule_end_time,
            'schedule_start_date' => $this->schedule_start_date,
            'schedule_end_date' => $this->schedule_end_date,
            'schedule_status' => $this->schedule_status,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
