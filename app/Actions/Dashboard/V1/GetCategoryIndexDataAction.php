<?php

namespace Modules\Menu\Actions\Dashboard\V1;

use Modules\Menu\Http\Resources\Dashboard\V1\CategoryResource;
use Modules\Menu\Models\Category;

class GetCategoryIndexDataAction
{
    /**
     * Get paginated categories with stats.
     */
    public function execute(int $perPage = 10, array $filters = []): array
    {
        $query = Category::withCount('products');

        if (!empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status'] === '1' || $filters['status'] === 'active');
        }

        $categories = $query->latest()->paginate($perPage);

        $stats = [
            'total' => Category::count(),
            'active' => Category::where('status', true)->count(),
            'inactive' => Category::where('status', false)->count(),
        ];

        return [
            'categories' => [
                'data' => CategoryResource::collection($categories)->resolve(),
                'meta' => [
                    'current_page' => $categories->currentPage(),
                    'last_page' => $categories->lastPage(),
                    'per_page' => $categories->perPage(),
                    'total' => $categories->total(),
                ],
            ],
            'filters' => $filters,
            'stats' => $stats,
        ];
    }
}
