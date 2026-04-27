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
        $mode = $data['schedule_mode'] ?? null;

        // Each mode only "owns" certain fields. Null the rest so the DB
        // doesn't carry stale data after the user switches modes.
        $usesDays = $mode === 'weekly';
        $usesTimes = in_array($mode, ['daily', 'weekly', 'date_range'], true);
        $usesDates = $mode === 'date_range';

        $scheduleData = [
            'schedule_mode'       => $mode,
            'schedule_days'       => $usesDays ? ($data['schedule_days'] ?? null) : null,
            'schedule_start_time' => $usesTimes ? ($data['schedule_start_time'] ?? null) : null,
            'schedule_end_time'   => $usesTimes ? ($data['schedule_end_time'] ?? null) : null,
            'schedule_start_date' => $usesDates ? ($data['schedule_start_date'] ?? null) : null,
            'schedule_end_date'   => $usesDates ? ($data['schedule_end_date'] ?? null) : null,
            'schedule_status'     => $data['schedule_status'] ?? false,
            'updated_by'          => Auth::id(),
        ];

        $menu->update($scheduleData);

        return $menu->fresh();
    }
}
