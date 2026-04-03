<?php

namespace Modules\Menu\Actions\Api\V1;

use Illuminate\Database\Eloquent\Collection;
use Modules\Menu\Models\Menu;
use Modules\Product\Enums\ProductStatusEnum;

class GetMenusAction
{
    /**
     * Get active menus with nested restaurant, categories, and products.
     */
    public function execute(?int $outletId = null): Collection
    {
        $query = Menu::with([
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
            ])
            ->where('status', true);

        if ($outletId) {
            $query->where('outlet_id', $outletId);
        }

        return $query->orderBy('name')->get();
    }
}
