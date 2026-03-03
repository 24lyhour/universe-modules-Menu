<?php

namespace Modules\Menu\Actions\Dashboard\V1;

use Illuminate\Support\Facades\Auth;
use Modules\Menu\Models\Menu;

class UpdateMenuScheduleAction
{
    /**
     * Update menu schedule.
     */
    public function execute(Menu $menu, array $data): Menu
    {
        $scheduleData = [
            'schedule_mode' => $data['schedule_mode'] ?? null,
            'schedule_days' => $data['schedule_days'] ?? null,
            'schedule_start_time' => $data['schedule_start_time'] ?? null,
            'schedule_end_time' => $data['schedule_end_time'] ?? null,
            'schedule_start_date' => $data['schedule_start_date'] ?? null,
            'schedule_end_date' => $data['schedule_end_date'] ?? null,
            'schedule_status' => $data['schedule_status'] ?? null,
            'updated_by' => Auth::id(),
        ];

        $menu->update($scheduleData);

        return $menu->fresh();
    }
}
