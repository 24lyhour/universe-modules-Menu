<?php

namespace Modules\Menu\Actions\Dashboard\V1;

use Modules\Menu\Http\Resources\Dashboard\V1\MenuResource;
use Modules\Menu\Models\Category;
use Modules\Menu\Models\Menu;

class GetMenuAssignCategoriesDataAction
{
    /**
     * Get data for assigning categories to a menu.
     */
    public function execute(Menu $menu): array
    {
        $menu->load(['outlet', 'menuType']);

        $availableCategories = Category::whereNull('menu_id')
            ->orWhere('menu_id', '!=', $menu->id)
            ->withCount('products')
            ->orderBy('name')
            ->get()
            ->map(fn ($category) => [
                'id' => $category->id,
                'name' => $category->name,
                'description' => $category->description,
                'image_url' => $category->image_url,
                'products_count' => $category->products_count,
                'current_menu_id' => $category->menu_id,
            ]);

        return [
            'menu' => (new MenuResource($menu))->resolve(),
            'availableCategories' => $availableCategories,
        ];
    }
}
