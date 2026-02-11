<?php

namespace Modules\Menu\Actions\Dashboard;

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
