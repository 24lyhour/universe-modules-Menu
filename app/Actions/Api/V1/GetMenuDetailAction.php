<?php

namespace Modules\Menu\Actions\Api\V1;

use Modules\Menu\Models\Menu;
use Modules\Product\Enums\ProductStatusEnum;

class GetMenuDetailAction
{
    /**
     * Get a single menu with nested restaurant, categories, and products.
     */
    public function execute(Menu $menu): Menu
    {
        return $menu->load([
            'outlet',
            'menuType',
            'categories' => function ($q) {
                $q->where('status', true)
                  ->orderBy('sort_order');
            },
            'categories.products' => function ($q) {
                $q->where('status', ProductStatusEnum::Active)
                  ->wherePivot('is_available', true);
            },
        ]);
    }
}
