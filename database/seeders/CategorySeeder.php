<?php

namespace Modules\Menu\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Modules\Menu\Models\Category;
use Modules\Menu\Models\Menu;
use Modules\Product\Models\ProductType;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Creates categories dynamically based on:
     * 1. Menus from database
     * 2. ProductTypes from database (per outlet)
     */
    public function run(): void
    {
        // Get all menus with their outlet
        $menus = Menu::with(['outlet', 'menuType'])->get();

        if ($menus->isEmpty()) {
            $this->command->warn('No menus found. Please run MenuSeeder first.');
            return;
        }

        $createdCount = 0;

        foreach ($menus as $menu) {
            // Get ProductTypes for this menu's outlet from database
            $productTypes = ProductType::where('outlet_id', $menu->outlet_id)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get();

            if ($productTypes->isEmpty()) {
                continue;
            }

            $sortOrder = 1;

            foreach ($productTypes as $productType) {
                // Create category for each ProductType
                Category::firstOrCreate(
                    [
                        'menu_id' => $menu->id,
                        'product_type' => $productType->slug,
                    ],
                    [
                        'uuid' => Str::uuid(),
                        'menu_id' => $menu->id,
                        'name' => $productType->name,
                        'description' => $productType->description,
                        'product_type' => $productType->slug,
                        'sort_order' => $sortOrder++,
                        'status' => true,
                    ]
                );

                $createdCount++;
            }
        }

        $this->command->info("Categories seeded successfully. Created {$createdCount} categories from ProductTypes.");
    }
}
