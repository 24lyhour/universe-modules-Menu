<?php

namespace Modules\Menu\Actions\Dashboard\V1;

use Modules\Menu\Models\MenuType;

class ToggleMenuTypeStatusAction
{
    /**
     * Toggle menu type status.
     */
    public function execute(MenuType $menuType, bool $status): void
    {
        $menuType->update([
            'status' => $status,
        ]);
    }
}
