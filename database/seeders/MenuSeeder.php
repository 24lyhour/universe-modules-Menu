<?php

namespace Modules\Menu\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Modules\Menu\Models\Menu;
use Modules\Menu\Models\MenuType;
use Modules\Outlet\Models\Outlet;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all outlets
        $outlets = Outlet::all();

        if ($outlets->isEmpty()) {
            $this->command->warn('No outlets found. Please run OutletSeeder first.');
            return;
        }

        $breakfastType = MenuType::where('name', 'Breakfast')->first();
        $lunchType = MenuType::where('name', 'Lunch')->first();
        $dinnerType = MenuType::where('name', 'Dinner')->first();
        $beverageType = MenuType::where('name', 'Beverages')->first();
        $dessertType = MenuType::where('name', 'Desserts')->first();

        $menuTemplates = [
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
            // Beverage menus
            [
                'name' => 'Drinks Menu',
                'description' => 'Full selection of beverages',
                'menu_type_id' => $beverageType?->id,
                'status' => true,
                'schedule_mode' => 'always',
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

        $createdCount = 0;

        // Create menus for each outlet
        foreach ($outlets as $outlet) {
            foreach ($menuTemplates as $template) {
                if ($template['menu_type_id']) {
                    $menuName = $template['name'] . ' - ' . $outlet->name;

                    Menu::firstOrCreate(
                        ['name' => $menuName, 'outlet_id' => $outlet->id],
                        array_merge($template, [
                            'uuid' => Str::uuid(),
                            'outlet_id' => $outlet->id,
                            'name' => $menuName,
                        ])
                    );

                    $createdCount++;
                }
            }
        }

        $this->command->info("Menus seeded successfully. Created menus for {$outlets->count()} outlets.");
    }
}
