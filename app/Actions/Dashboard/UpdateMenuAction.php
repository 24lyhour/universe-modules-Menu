<?php

namespace Modules\Menu\Actions\Dashboard;

use Illuminate\Support\Facades\Auth;
use Modules\Menu\Models\Menu;

class UpdateMenuAction
{
    /**
     * Update a menu.
     */
    public function execute(Menu $menu, array $data): Menu
    {
        $data['updated_by'] = Auth::id();

        $menu->update($data);

        return $menu->fresh();
    }
}
