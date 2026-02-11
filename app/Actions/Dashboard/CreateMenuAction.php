<?php

namespace Modules\Menu\Actions\Dashboard;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Modules\Menu\Models\Menu;

class CreateMenuAction
{
    /**
     * Create a new menu.
     */
    public function execute(array $data): Menu
    {
        $data['uuid'] = (string) Str::uuid();
        $data['created_by'] = Auth::id();

        return Menu::create($data);
    }
}
