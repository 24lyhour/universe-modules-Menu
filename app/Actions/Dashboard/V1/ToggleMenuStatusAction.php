<?php

namespace Modules\Menu\Actions\Dashboard\V1;

use Modules\Menu\Models\Menu;

class ToggleMenuStatusAction
{
    /**
     * Toggle menu status.
     */
    public function execute(Menu $menu, bool $status): void
    {
        $menu->update([
            'status' => $status,
        ]);
    }
}
