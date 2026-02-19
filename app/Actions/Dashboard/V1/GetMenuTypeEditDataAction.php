<?php

namespace Modules\Menu\Actions\Dashboard\V1;

use Modules\Menu\Http\Resources\Dashboard\V1\MenuTypeResource;
use Modules\Menu\Models\MenuType;
use Modules\Outlet\Models\Outlet;

class GetMenuTypeEditDataAction
{
    /**
     * Get data for menu type edit form.
     */
    public function execute(MenuType $menuType): array
    {
        $outlets = Outlet::where('status', 'active')
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return [
            'menuType' => new MenuTypeResource($menuType),
            'outlets' => $outlets,
        ];
    }
}
