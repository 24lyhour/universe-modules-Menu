<?php

namespace Modules\Menu\Services;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Modules\Menu\Models\MenuType;

class MenuTypeService
{
    private const CACHE_TTL = 300; // 5 minutes
    private const CACHE_KEY_STATS = 'menu_type_stats';

    /**
     * Get paginated trashed menu types.
     */
    public function getTrashed(int $perPage = 10, ?string $search = null): LengthAwarePaginator
    {
        $query = MenuType::onlyTrashed()->with('outlet')->withCount('menus');

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        return $query->orderBy('deleted_at', 'desc')->paginate($perPage);
    }

    /**
     * Get menu type statistics (cached).
     */
    public function getStats(): array
    {
        return Cache::remember(self::CACHE_KEY_STATS, self::CACHE_TTL, function () {
            return [
                'total' => MenuType::count(),
                'active' => MenuType::where('status', true)->count(),
                'inactive' => MenuType::where('status', false)->count(),
                'trashed' => MenuType::onlyTrashed()->count(),
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
     * Restore a trashed menu type.
     */
    public function restore(MenuType $menuType): bool
    {
        $result = $menuType->restore();
        $this->clearStatsCache();
        return $result;
    }

    /**
     * Force delete a menu type.
     */
    public function forceDelete(MenuType $menuType): bool
    {
        $result = $menuType->forceDelete();
        $this->clearStatsCache();
        return $result;
    }

    /**
     * Bulk delete menu types.
     */
    public function bulkDelete(array $uuids): int
    {
        $count = MenuType::whereIn('uuid', $uuids)->delete();
        $this->clearStatsCache();
        return $count;
    }

    /**
     * Bulk restore menu types.
     */
    public function bulkRestore(array $uuids): int
    {
        $count = MenuType::onlyTrashed()->whereIn('uuid', $uuids)->restore();
        $this->clearStatsCache();
        return $count;
    }

    /**
     * Bulk force delete menu types.
     */
    public function bulkForceDelete(array $uuids): int
    {
        $count = MenuType::onlyTrashed()->whereIn('uuid', $uuids)->forceDelete();
        $this->clearStatsCache();
        return $count;
    }

    /**
     * Empty trash.
     */
    public function emptyTrash(): int
    {
        $count = MenuType::onlyTrashed()->forceDelete();
        $this->clearStatsCache();
        return $count;
    }
}
