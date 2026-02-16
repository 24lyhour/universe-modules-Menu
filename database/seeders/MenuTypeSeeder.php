<?php

namespace Modules\Menu\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Modules\Menu\Models\MenuType;

class MenuTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $menuTypes = [
            [
                'name' => 'Breakfast',
                'description' => 'Start your day with our delicious breakfast options',
                'sort_order' => 1,
                'status' => true,
            ],
            [
                'name' => 'Lunch',
                'description' => 'Satisfy your midday hunger with our lunch specials',
                'sort_order' => 2,
                'status' => true,
            ],
            [
                'name' => 'Dinner',
                'description' => 'End your day with our exquisite dinner selections',
                'sort_order' => 3,
                'status' => true,
            ],
            [
                'name' => 'Beverages',
                'description' => 'Refresh yourself with our wide range of drinks',
                'sort_order' => 4,
                'status' => true,
            ],
            [
                'name' => 'Desserts',
                'description' => 'Treat yourself with our sweet dessert options',
                'sort_order' => 5,
                'status' => true,
            ],
            [
                'name' => 'Snacks',
                'description' => 'Light bites for any time of the day',
                'sort_order' => 6,
                'status' => true,
            ],
        ];

        foreach ($menuTypes as $type) {
            MenuType::firstOrCreate(
                ['name' => $type['name']],
                array_merge($type, ['uuid' => Str::uuid()])
            );
        }
    }
}
