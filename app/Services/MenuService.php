<?php

namespace Modules\Menu\Services;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Modules\Menu\Models\Menu;

class MenuService
{
    /**
     * Get paginated menus with filters.
     */
    public function paginate(int $perPage = 10, array $filters = []): LengthAwarePaginator
    {
        $query = Menu::query();

        // Search filter
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Status filter
        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('status', $filters['status']);
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Create a new menu.
     */
    public function create(array $data): Menu
    {
        $data['uuid'] = (string) Str::uuid();

        return Menu::create($data);
    }

    /**
     * Update a menu.
     */
    public function update(Menu $menu, array $data): Menu
    {
        $menu->update($data);

        return $menu->fresh();
    }

    /**
     * Delete a menu.
     */
    public function delete(Menu $menu): bool
    {
        return $menu->delete();
    }

    /**
     * Get menu statistics.
     */
    public function getStats(): array
    {
        return [
            'total' => Menu::count(),
            'active' => Menu::where('status', true)->count(),
            'inactive' => Menu::where('status', false)->count(),
        ];
    }

    /**
     * Update menu status.
     */
    public function updateStatus(Menu $menu, bool $status): Menu
    {
        $menu->status = $status;
        $menu->save();

        return $menu;
    }

    /**
     * Find menu by UUID.
     */
    public function findByUuid(string $uuid): ?Menu
    {
        return Menu::where('uuid', $uuid)->first();
    }
}
