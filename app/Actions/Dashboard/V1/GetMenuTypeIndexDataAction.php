<?php

namespace Modules\Menu\Actions\Dashboard\V1;

use Modules\Menu\Http\Resources\Dashboard\V1\MenuTypeResource;
use Modules\Menu\Models\MenuType;

class GetMenuTypeIndexDataAction
{
    /**
     * Get paginated menu types with stats.
     */
    public function execute(int $perPage = 10, array $filters = []): array
    {
        $query = MenuType::with('outlet');

        if (!empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status'] === '1' || $filters['status'] === 'active');
        }

        $menuTypes = $query->latest()->paginate($perPage);

        $stats = [
            'total' => MenuType::count(),
            'active' => MenuType::where('status', true)->count(),
            'inactive' => MenuType::where('status', false)->count(),
        ];

        return [
            'menuTypes' => [
                'data' => MenuTypeResource::collection($menuTypes)->resolve(),
                'meta' => [
                    'current_page' => $menuTypes->currentPage(),
                    'last_page' => $menuTypes->lastPage(),
                    'per_page' => $menuTypes->perPage(),
                    'total' => $menuTypes->total(),
                ],
            ],
            'filters' => $filters,
            'stats' => $stats,
        ];
    }
}
