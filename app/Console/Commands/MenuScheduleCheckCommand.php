<?php

namespace Modules\Menu\Console\Commands;

use Illuminate\Console\Command;
use Modules\Menu\Models\Menu;

class MenuScheduleCheckCommand extends Command
{
    protected $signature = 'menu:schedule-check';

    protected $description = 'Check and log menu availability status based on their schedules';

    public function handle(): int
    {
        $menus = Menu::with(['outlet', 'menuType'])
            ->where('status', true)
            ->whereNotNull('schedule_mode')
            ->get();

        if ($menus->isEmpty()) {
            $this->info('No menus with schedules found.');
            return Command::SUCCESS;
        }

        $now = now();
        $currentDay = strtolower($now->format('l'));
        $currentTime = $now->format('H:i:s');

        $this->table(
            ['Menu', 'Outlet', 'Type', 'Mode', 'Hours', 'Currently'],
            $menus->map(function ($menu) use ($currentDay, $currentTime) {
                $isAvailable = $this->checkIsAvailable($menu, $currentDay, $currentTime);

                return [
                    $menu->name,
                    $menu->outlet?->name ?? '-',
                    $menu->menuType?->name ?? '-',
                    $menu->schedule_mode ?? '-',
                    $menu->schedule_start_time && $menu->schedule_end_time
                        ? $menu->schedule_start_time . ' - ' . $menu->schedule_end_time
                        : ($menu->schedule_mode === 'always' ? '24/7' : '-'),
                    $isAvailable ? '<fg=green>AVAILABLE</>' : '<fg=red>UNAVAILABLE</>',
                ];
            })->toArray()
        );

        $availableCount = $menus->filter(fn ($m) => $this->checkIsAvailable($m, $currentDay, $currentTime))->count();
        $this->info("Available: {$availableCount}/{$menus->count()} menus at {$now->format('H:i:s')}");

        return Command::SUCCESS;
    }

    private function checkIsAvailable(Menu $menu, string $currentDay, string $currentTime): bool
    {
        if (!$menu->schedule_status) {
            return false;
        }

        if ($menu->schedule_mode === 'always') {
            return true;
        }

        // Check days
        if ($menu->schedule_days) {
            $days = is_array($menu->schedule_days)
                ? $menu->schedule_days
                : json_decode($menu->schedule_days ?? '[]', true);

            if (!empty($days) && !in_array($currentDay, $days)) {
                return false;
            }
        }

        // Check time
        if ($menu->schedule_start_time && $menu->schedule_end_time) {
            return $currentTime >= $menu->schedule_start_time
                && $currentTime <= $menu->schedule_end_time;
        }

        return true;
    }
}
