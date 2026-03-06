<?php

namespace Modules\Menu\Services;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Modules\Menu\Models\Menu;

class MenuService
{
    private const CACHE_TTL = 300; // 5 minutes
    private const CACHE_KEY_STATS = 'menu_stats';

    /**
     * Get paginated menus with filters.
     */
    public function paginate(int $perPage = 10, array $filters = []): LengthAwarePaginator
    {
        $query = Menu::with(['outlet', 'menuType'])
            ->withCount('categories')
            ->selectRaw('menus.*, (SELECT COUNT(*) FROM menu_category_products WHERE menu_category_products.category_id IN (SELECT id FROM menu_categories WHERE menu_categories.menu_id = menus.id)) as products_count');

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
     * Get paginated trashed menus.
     */
    public function getTrashed(int $perPage = 10, ?string $search = null): LengthAwarePaginator
    {
        $query = Menu::onlyTrashed()
            ->with(['outlet', 'menuType'])
            ->withCount('categories');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('deleted_at', 'desc')->paginate($perPage);
    }

    /**
     * Create a new menu.
     */
    public function create(array $data): Menu
    {
        $data['uuid'] = (string) Str::uuid();
        $menu = Menu::create($data);
        $this->clearStatsCache();

        return $menu;
    }

    /**
     * Update a menu.
     */
    public function update(Menu $menu, array $data): Menu
    {
        $menu->update($data);
        $this->clearStatsCache();

        return $menu->fresh();
    }

    /**
     * Delete a menu (soft delete).
     */
    public function delete(Menu $menu): bool
    {
        $result = $menu->delete();
        $this->clearStatsCache();
        return $result;
    }

    /**
     * Get menu statistics (cached).
     */
    public function getStats(): array
    {
        return Cache::remember(self::CACHE_KEY_STATS, self::CACHE_TTL, function () {
            return [
                'total' => Menu::count(),
                'active' => Menu::where('status', true)->count(),
                'inactive' => Menu::where('status', false)->count(),
                'trashed' => Menu::onlyTrashed()->count(),
            ];
        });
    }

    /**
     * Clear the stats cache.
     */
    public function clearStatsCache(): void
    {
        Cache::forget(self::CACHE_KEY_STATS);
    }

    /**
     * Update menu status.
     */
    public function updateStatus(Menu $menu, bool $status): Menu
    {
        $menu->status = $status;
        $menu->save();
        $this->clearStatsCache();

        return $menu;
    }

    /**
     * Find menu by UUID.
     */
    public function findByUuid(string $uuid): ?Menu
    {
        return Menu::where('uuid', $uuid)->first();
    }

    /**
     * Restore a trashed menu.
     */
    public function restore(Menu $menu): bool
    {
        $result = $menu->restore();
        $this->clearStatsCache();
        return $result;
    }

    /**
     * Force delete a menu.
     */
    public function forceDelete(Menu $menu): bool
    {
        $result = $menu->forceDelete();
        $this->clearStatsCache();
        return $result;
    }

    /**
     * Bulk delete menus.
     */
    public function bulkDelete(array $uuids): int
    {
        $count = Menu::whereIn('uuid', $uuids)->delete();
        $this->clearStatsCache();
        return $count;
    }

    /**
     * Bulk restore menus.
     */
    public function bulkRestore(array $uuids): int
    {
        $count = Menu::onlyTrashed()->whereIn('uuid', $uuids)->restore();
        $this->clearStatsCache();
        return $count;
    }

    /**
     * Bulk force delete menus.
     */
    public function bulkForceDelete(array $uuids): int
    {
        $count = Menu::onlyTrashed()->whereIn('uuid', $uuids)->forceDelete();
        $this->clearStatsCache();
        return $count;
    }

    /**
     * Empty trash.
     */
    public function emptyTrash(): int
    {
        $count = Menu::onlyTrashed()->forceDelete();
        $this->clearStatsCache();
        return $count;
    }
}
