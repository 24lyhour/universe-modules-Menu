<?php

namespace Modules\Menu\Actions\Dashboard\V1;

use Modules\Menu\Models\Category;
use Modules\Menu\Models\Menu;

class ReorderMenuCategoriesAction
{
    /**
     * Reorder categories in a menu.
     */
    public function execute(Menu $menu, array $categories): void
    {
        foreach ($categories as $item) {
            Category::where('id', $item['id'])
                ->where('menu_id', $menu->id)
                ->update(['sort_order' => $item['sort_order']]);
        }
    }
}
