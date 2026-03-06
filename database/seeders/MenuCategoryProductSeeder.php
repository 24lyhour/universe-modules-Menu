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
     * Map product types to category product types.
     */
    private array $productTypeMapping = [
        'food' => 'food',
        'beverage' => 'beverage',
        'dessert' => 'dessert',
    ];

    /**
     * Run the database seeds.
     *
     * Links products to menu categories based on:
     * 1. Same outlet (product.outlet_id matches menu.outlet_id)
     * 2. Matching product type (product.product_type matches category.product_type)
     */
    public function run(): void
    {
        // Get all menus with their categories, grouped by outlet
        $menus = Menu::with(['categories'])->get()->groupBy('outlet_id');

        if ($menus->isEmpty()) {
            $this->command->warn('No menus found. Please run MenuSeeder first.');
            return;
        }

        // Get all products grouped by outlet and product_type
        $products = Product::where('status', 'active')->get();

        if ($products->isEmpty()) {
            $this->command->warn('No products found. Please run ProductSeeder first.');
            return;
        }

        $linkedCount = 0;
        $sortOrder = 1;

        foreach ($products as $product) {
            $outletId = $product->outlet_id;
            $productType = $product->product_type; // food, beverage, dessert, etc.

            // Get menus for this outlet
            $outletMenus = $menus->get($outletId, collect());

            foreach ($outletMenus as $menu) {
                // Find categories that match the product type
                $matchingCategories = $menu->categories->filter(function ($category) use ($productType) {
                    return $category->product_type === $productType;
                });

                // Link product to matching categories (usually just one per menu)
                foreach ($matchingCategories->take(1) as $category) {
                    // Check if relationship already exists
                    $exists = DB::table('menu_category_products')
                        ->where('category_id', $category->id)
                        ->where('product_id', $product->id)
                        ->exists();

                    if (!$exists) {
                        DB::table('menu_category_products')->insert([
                            'category_id' => $category->id,
                            'product_id' => $product->id,
                            'price_override' => null, // Use product's default price
                            'sort_order' => $sortOrder++,
                            'is_available' => true,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                        $linkedCount++;
                    }
                }
            }
        }

        $this->command->info("Menu-Category-Product relationships seeded. Linked {$linkedCount} products to categories.");
    }
}
