<?php

namespace Modules\Menu\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Menu\Models\Category;
use Modules\Menu\Models\Menu;
use Modules\Product\Models\Product;

class MenuCategoryProductSeeder extends Seeder
{
    /**
     * Menu type to product type allocation.
     *
     * Each menu type gets a specific subset of products.
     * 'take' = how many, 'offset' = starting position.
     * For duplicate menu types in same outlet, second menu gets 'alt_offset'.
     */
    private array $menuTypeStrategy = [
        'Breakfast' => [
            'food' => ['take' => 4, 'offset' => 0, 'alt_offset' => 4],
            'beverage' => ['take' => 3, 'offset' => 0, 'alt_offset' => 3],
            'dessert' => ['take' => 1, 'offset' => 0, 'alt_offset' => 1],
        ],
        'Lunch' => [
            'food' => ['take' => 5, 'offset' => 3],
            'beverage' => ['take' => 3, 'offset' => 1],
            'dessert' => ['take' => 2, 'offset' => 1],
        ],
        'Dinner' => [
            'food' => ['take' => 6, 'offset' => 5],
            'beverage' => ['take' => 4, 'offset' => 2],
            'dessert' => ['take' => 3, 'offset' => 1],
        ],
        'Beverages' => [
            'beverage' => ['take' => 6, 'offset' => 0],
        ],
        'Desserts' => [
            'dessert' => ['take' => 4, 'offset' => 0],
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $menus = Menu::with(['menuType', 'categories'])->orderBy('outlet_id')->orderBy('id')->get();

        if ($menus->isEmpty()) {
            $this->command->warn('No menus found. Please run MenuSeeder first.');
            return;
        }

        // Group products by outlet:product_type, sorted by id
        $productMap = Product::where('status', 'active')
            ->orderBy('id')
            ->get()
            ->groupBy(fn ($p) => $p->outlet_id . ':' . $p->product_type);

        // Track how many times a menu type appears per outlet (for alt_offset)
        $menuTypeCount = [];
        $linkedCount = 0;

        foreach ($menus as $menu) {
            $outletId = $menu->outlet_id;
            $menuTypeName = $menu->menuType?->name ?? 'default';

            // Track occurrence index of this menu type within this outlet
            $typeKey = $outletId . ':' . $menuTypeName;
            $menuTypeCount[$typeKey] = ($menuTypeCount[$typeKey] ?? 0) + 1;
            $occurrence = $menuTypeCount[$typeKey]; // 1 = first, 2 = second

            $typeStrategies = $this->menuTypeStrategy[$menuTypeName] ?? null;
            if (!$typeStrategies) {
                continue;
            }

            foreach ($menu->categories as $category) {
                $productType = $category->product_type;
                $key = $outletId . ':' . $productType;

                $allProducts = $productMap->get($key, collect());
                if ($allProducts->isEmpty()) {
                    continue;
                }

                $strategy = $typeStrategies[$productType] ?? null;
                if (!$strategy) {
                    continue;
                }

                // Use alt_offset for second occurrence of same menu type
                $offset = $strategy['offset'];
                if ($occurrence > 1 && isset($strategy['alt_offset'])) {
                    $offset = $strategy['alt_offset'];
                }

                $products = $allProducts->slice($offset, $strategy['take']);

                $sortOrder = 1;
                foreach ($products as $product) {
                    $exists = DB::table('menu_category_products')
                        ->where('category_id', $category->id)
                        ->where('product_id', $product->id)
                        ->exists();

                    if (!$exists) {
                        DB::table('menu_category_products')->insert([
                            'category_id' => $category->id,
                            'product_id' => $product->id,
                            'price_override' => null,
                            'sort_order' => $sortOrder,
                            'is_available' => true,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                        $linkedCount++;
                    }

                    $sortOrder++;
                }
            }
        }

        $this->command->info("Menu-Category-Product linked: {$linkedCount} relationships created.");
    }
}
