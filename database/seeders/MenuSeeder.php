<?php

namespace Modules\Menu\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Modules\Menu\Models\Menu;
use Modules\Menu\Models\MenuType;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $breakfastType = MenuType::where('name', 'Breakfast')->first();
        $lunchType = MenuType::where('name', 'Lunch')->first();
        $dinnerType = MenuType::where('name', 'Dinner')->first();
        $beverageType = MenuType::where('name', 'Beverages')->first();
        $dessertType = MenuType::where('name', 'Desserts')->first();

        $menus = [
            // Breakfast menus
            [
                'name' => 'Morning Special',
                'description' => 'Our signature breakfast menu featuring classic favorites',
                'menu_type_id' => $breakfastType?->id,
                'status' => true,
                'schedule_mode' => 'daily',
                'schedule_start_time' => '06:00:00',
                'schedule_end_time' => '11:00:00',
                'schedule_status' => true,
            ],
            [
                'name' => 'Weekend Brunch',
                'description' => 'Special brunch menu available on weekends',
                'menu_type_id' => $breakfastType?->id,
                'status' => true,
                'schedule_mode' => 'weekly',
                'schedule_days' => json_encode(['saturday', 'sunday']),
                'schedule_start_time' => '08:00:00',
                'schedule_end_time' => '14:00:00',
                'schedule_status' => true,
            ],
            // Lunch menus
            [
                'name' => 'Business Lunch',
                'description' => 'Quick and satisfying lunch options for busy professionals',
                'menu_type_id' => $lunchType?->id,
                'status' => true,
                'schedule_mode' => 'daily',
                'schedule_start_time' => '11:00:00',
                'schedule_end_time' => '15:00:00',
                'schedule_status' => true,
            ],
            [
                'name' => 'Set Lunch Menu',
                'description' => 'Value set meals with appetizer, main, and drink',
                'menu_type_id' => $lunchType?->id,
                'status' => true,
                'schedule_mode' => 'weekly',
                'schedule_days' => json_encode(['monday', 'tuesday', 'wednesday', 'thursday', 'friday']),
                'schedule_start_time' => '11:30:00',
                'schedule_end_time' => '14:00:00',
                'schedule_status' => true,
            ],
            // Dinner menus
            [
                'name' => 'Evening Dinner',
                'description' => 'Our full dinner menu with premium selections',
                'menu_type_id' => $dinnerType?->id,
                'status' => true,
                'schedule_mode' => 'daily',
                'schedule_start_time' => '17:00:00',
                'schedule_end_time' => '22:00:00',
                'schedule_status' => true,
            ],
            [
                'name' => 'Chef\'s Special',
                'description' => 'Exclusive dishes crafted by our head chef',
                'menu_type_id' => $dinnerType?->id,
                'status' => true,
                'schedule_mode' => 'daily',
                'schedule_start_time' => '18:00:00',
                'schedule_end_time' => '21:00:00',
                'schedule_status' => true,
            ],
            // Beverage menus
            [
                'name' => 'Drinks Menu',
                'description' => 'Full selection of beverages',
                'menu_type_id' => $beverageType?->id,
                'status' => true,
                'schedule_mode' => 'always',
                'schedule_status' => true,
            ],
            [
                'name' => 'Happy Hour',
                'description' => 'Special drink prices during happy hour',
                'menu_type_id' => $beverageType?->id,
                'status' => true,
                'schedule_mode' => 'daily',
                'schedule_start_time' => '16:00:00',
                'schedule_end_time' => '19:00:00',
                'schedule_status' => true,
            ],
            // Dessert menus
            [
                'name' => 'Dessert Menu',
                'description' => 'Sweet endings to your meal',
                'menu_type_id' => $dessertType?->id,
                'status' => true,
                'schedule_mode' => 'always',
                'schedule_status' => true,
            ],
        ];

        foreach ($menus as $menu) {
            if ($menu['menu_type_id']) {
                Menu::firstOrCreate(
                    ['name' => $menu['name']],
                    array_merge($menu, ['uuid' => Str::uuid()])
                );
            }
        }
    }
}
