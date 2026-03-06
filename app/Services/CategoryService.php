<?php

namespace Modules\Menu\Services;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Modules\Menu\Models\Category;

class CategoryService
{
    private const CACHE_TTL = 300; // 5 minutes
    private const CACHE_KEY_STATS = 'menu_category_stats';

    /**
     * Get paginated trashed categories.
     */
    public function getTrashed(int $perPage = 10, ?string $search = null): LengthAwarePaginator
    {
        $query = Category::onlyTrashed()->withCount('products');

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        return $query->orderBy('deleted_at', 'desc')->paginate($perPage);
    }

    /**
     * Get category statistics (cached).
     */
    public function getStats(): array
    {
        return Cache::remember(self::CACHE_KEY_STATS, self::CACHE_TTL, function () {
            return [
                'total' => Category::count(),
                'active' => Category::where('status', true)->count(),
                'inactive' => Category::where('status', false)->count(),
                'trashed' => Category::onlyTrashed()->count(),
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
     * Restore a trashed category.
     */
    public function restore(Category $category): bool
    {
        $result = $category->restore();
        $this->clearStatsCache();
        return $result;
    }

    /**
     * Force delete a category.
     */
    public function forceDelete(Category $category): bool
    {
        $result = $category->forceDelete();
        $this->clearStatsCache();
        return $result;
    }

    /**
     * Bulk delete categories.
     */
    public function bulkDelete(array $uuids): int
    {
        $count = Category::whereIn('uuid', $uuids)->delete();
        $this->clearStatsCache();
        return $count;
    }

    /**
     * Bulk restore categories.
     */
    public function bulkRestore(array $uuids): int
    {
        $count = Category::onlyTrashed()->whereIn('uuid', $uuids)->restore();
        $this->clearStatsCache();
        return $count;
    }

    /**
     * Bulk force delete categories.
     */
    public function bulkForceDelete(array $uuids): int
    {
        $count = Category::onlyTrashed()->whereIn('uuid', $uuids)->forceDelete();
        $this->clearStatsCache();
        return $count;
    }

    /**
     * Empty trash.
     */
    public function emptyTrash(): int
    {
        $count = Category::onlyTrashed()->forceDelete();
        $this->clearStatsCache();
        return $count;
    }
}
