<?php

namespace Modules\Menu\Http\Controllers\Api\V1\Customer\Menu;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Menu\Actions\Api\V1\GetMenuDetailAction;
use Modules\Menu\Actions\Api\V1\GetMenusAction;
use Modules\Menu\Http\Resources\Api\V1\MenuResource;
use Modules\Menu\Models\Menu;
use Modules\Menu\Models\MenuType;

class MenuController extends Controller
{
    /**
     * List all active menus with restaurant, categories, and products.
     *
     * GET /api/v1/menus
     * GET /api/v1/menus?outlet_id=1
     */
    public function index(Request $request, GetMenusAction $action): JsonResponse
    {
        $outletId = $request->input('outlet_id') ? (int) $request->input('outlet_id') : null;

        $menus = $action->execute($outletId);

        return response()->json([
            'success' => true,
            'data' => MenuResource::collection($menus),
        ]);
    }

    /**
     * List all active menu types.
     *
     * GET /api/v1/menu-types
     */
    public function menuTypes(): JsonResponse
    {
        $types = MenuType::where('status', true)
            ->orderBy('sort_order')
            ->get(['id', 'uuid', 'name', 'description', 'image_url', 'sort_order']);

        return response()->json([
            'data' => $types,
        ]);
    }

    /**
     * Show a single menu with full nested data.
     *
     * GET /api/v1/menus/{uuid}
     */
    public function show(Menu $menu, GetMenuDetailAction $action): JsonResponse
    {
        $menu = $action->execute($menu);

        return response()->json([
            'success' => true,
            'data' => new MenuResource($menu),
        ]);
    }
}
