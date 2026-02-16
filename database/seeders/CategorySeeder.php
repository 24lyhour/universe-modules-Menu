<?php

namespace Modules\Menu\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Modules\Menu\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            // Food categories
            [
                'name' => 'Appetizers',
                'description' => 'Start your meal with these delicious starters',
                'sort_order' => 1,
                'status' => true,
            ],
            [
                'name' => 'Main Course',
                'description' => 'Our signature main dishes',
                'sort_order' => 2,
                'status' => true,
            ],
            [
                'name' => 'Soups & Salads',
                'description' => 'Fresh and healthy options',
                'sort_order' => 3,
                'status' => true,
            ],
            [
                'name' => 'Pasta & Noodles',
                'description' => 'Italian and Asian noodle dishes',
                'sort_order' => 4,
                'status' => true,
            ],
            [
                'name' => 'Rice Dishes',
                'description' => 'Fried rice and rice bowls',
                'sort_order' => 5,
                'status' => true,
            ],
            [
                'name' => 'Grilled & BBQ',
                'description' => 'Grilled meats and barbecue specialties',
                'sort_order' => 6,
                'status' => true,
            ],
            [
                'name' => 'Seafood',
                'description' => 'Fresh catches from the sea',
                'sort_order' => 7,
                'status' => true,
            ],
            [
                'name' => 'Vegetarian',
                'description' => 'Delicious meat-free options',
                'sort_order' => 8,
                'status' => true,
            ],
            // Beverage categories
            [
                'name' => 'Hot Drinks',
                'description' => 'Coffee, tea, and other hot beverages',
                'sort_order' => 9,
                'status' => true,
            ],
            [
                'name' => 'Cold Drinks',
                'description' => 'Refreshing cold beverages',
                'sort_order' => 10,
                'status' => true,
            ],
            [
                'name' => 'Smoothies & Juices',
                'description' => 'Fresh fruit blends',
                'sort_order' => 11,
                'status' => true,
            ],
            // Dessert categories
            [
                'name' => 'Cakes & Pastries',
                'description' => 'Sweet baked goods',
                'sort_order' => 12,
                'status' => true,
            ],
            [
                'name' => 'Ice Cream',
                'description' => 'Frozen treats',
                'sort_order' => 13,
                'status' => true,
            ],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(
                ['name' => $category['name']],
                array_merge($category, ['uuid' => Str::uuid()])
            );
        }
    }
}
