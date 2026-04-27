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
        // Return all enabled menus, INCLUDING currently-muted ones.
        // The MenuResource exposes `is_available` / `is_muted` / `muted_reason`
        // so the customer app can render an "unavailable" banner and disable
        // ordering instead of hiding the menu entirely.
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

        // Schedule logic is too rich for a single SQL clause (weekly day-of-week,
        // date ranges) so we filter in PHP after fetching. Outlets typically have
        // <20 menus so the cost is negligible.
        return $query->orderBy('name')
            ->get()
            ->filter->isWithinSchedule()
            ->values();
    }
}
