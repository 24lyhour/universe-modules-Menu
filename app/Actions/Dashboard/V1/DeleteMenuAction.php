<?php

namespace Modules\Menu\Actions\Dashboard\V1;

use Modules\Menu\Models\Menu;

class DeleteMenuAction
{
    /**
     * Delete a menu.
     */
    public function execute(Menu $menu): bool
    {
        return $menu->delete();
    }
}
