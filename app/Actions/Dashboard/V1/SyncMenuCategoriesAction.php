<?php

namespace Modules\Menu\Actions\Dashboard\V1;

use Modules\Menu\Models\Category;
use Modules\Menu\Models\Menu;

class SyncMenuCategoriesAction
{
    /**
     * Sync (assign) categories to a menu.
     */
    public function execute(Menu $menu, array $categoryIds): void
    {
        $maxSortOrder = Category::where('menu_id', $menu->id)->max('sort_order') ?? 0;

        foreach ($categoryIds as $index => $categoryId) {
            Category::where('id', $categoryId)->update([
                'menu_id' => $menu->id,
                'sort_order' => $maxSortOrder + $index + 1,
            ]);
        }
    }
}
