<?php

namespace Modules\Menu\Database\Seeders;

use Illuminate\Database\Seeder;

class MenuDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    /**
     * Run Menu module seeders in correct order.
     *
     * Order is important:
     * 1. MenuTypeSeeder - Creates global menu types (Breakfast, Lunch, Dinner, etc.)
     * 2. MenuSeeder - Creates menus for each outlet linked to menu types
     * 3. CategorySeeder - Creates categories linked to menus (based on outlet product types)
     * 4. MenuCategoryProductSeeder - Links products to categories (same outlet + same product type)
     */
    public function run(): void
    {
        $this->call([
            MenuTypeSeeder::class,
            MenuSeeder::class,
            CategorySeeder::class,
            MenuCategoryProductSeeder::class,
        ]);
    }
}
