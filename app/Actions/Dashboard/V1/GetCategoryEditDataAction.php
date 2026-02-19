<?php

namespace Modules\Menu\Actions\Dashboard\V1;

use Modules\Menu\Http\Resources\Dashboard\V1\CategoryResource;
use Modules\Menu\Models\Category;
use Modules\Menu\Models\Menu;

class GetCategoryEditDataAction
{
    /**
     * Get data for category edit form.
     */
    public function execute(Category $category): array
    {
        $menus = Menu::where('status', true)->get(['id', 'name']);

        return [
            'category' => new CategoryResource($category),
            'menus' => $menus,
        ];
    }
}
