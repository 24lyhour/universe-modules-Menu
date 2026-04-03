<?php

namespace Modules\Menu\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Modules\Menu\Models\Category;
use Modules\Menu\Models\Menu;
use Modules\Product\Models\Product;

class CategorySeeder extends Seeder
{
    /**
     * Category definitions per product type.
     */
    private array $categoryMap = [
        'food' => [
            'name' => 'Food',
            'description' => 'Main dishes, snacks, and appetizers',
        ],
        'beverage' => [
            'name' => 'Beverages',
            'description' => 'Drinks, juices, and refreshments',
        ],
        'dessert' => [
            'name' => 'Desserts',
            'description' => 'Sweet treats, cakes, and pastries',
        ],
        'gadget' => [
            'name' => 'Gadgets',
            'description' => 'Tech gadgets and accessories',
        ],
        'clothing' => [
            'name' => 'Clothing',
            'description' => 'Apparel and fashion items',
        ],
        'book' => [
            'name' => 'Books',
            'description' => 'Books and literature',
        ],
    ];

    /**
     * Run the database seeds.
     *
     * Creates categories for each menu based on the product types
     * available in that menu's outlet.
     */
    public function run(): void
    {
        $menus = Menu::all();

        if ($menus->isEmpty()) {
            $this->command->warn('No menus found. Please run MenuSeeder first.');
            return;
        }

        // Get distinct product types per outlet
        $outletProductTypes = Product::where('status', 'active')
            ->selectRaw('outlet_id, product_type')
            ->distinct()
            ->get()
            ->groupBy('outlet_id')
            ->map(fn ($items) => $items->pluck('product_type')->toArray());

        if ($outletProductTypes->isEmpty()) {
            $this->command->warn('No active products found. Please run ProductSeeder first.');
            return;
        }

        $createdCount = 0;

        foreach ($menus as $menu) {
            $productTypes = $outletProductTypes->get($menu->outlet_id, []);

            $sortOrder = 1;
            foreach ($productTypes as $type) {
                $label = $this->categoryMap[$type] ?? [
                    'name' => ucfirst($type),
                    'description' => ucfirst($type) . ' items',
                ];

                $created = Category::firstOrCreate(
                    [
                        'menu_id' => $menu->id,
                        'product_type' => $type,
                    ],
                    [
                        'uuid' => (string) Str::uuid(),
                        'name' => $label['name'],
                        'description' => $label['description'],
                        'sort_order' => $sortOrder++,
                        'status' => true,
                    ]
                );

                if ($created->wasRecentlyCreated) {
                    $createdCount++;
                }
            }
        }

        $this->command->info("Categories seeded: {$createdCount} created.");
    }
}
