<?php

namespace Modules\Menu\Actions\Dashboard\V1;

use Modules\Menu\Models\Menu;

class GetCategoryCreateDataAction
{
    /**
     * Get data for category create form.
     */
    public function execute(?int $selectedMenuId = null): array
    {
        $menus = Menu::where('status', true)->get(['id', 'name']);

        return [
            'menus' => $menus,
            'selectedMenuId' => $selectedMenuId,
        ];
    }
}
