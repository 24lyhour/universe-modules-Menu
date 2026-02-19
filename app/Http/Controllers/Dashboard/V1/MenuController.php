<?php

namespace Modules\Menu\Http\Controllers\Dashboard\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Momentum\Modal\Modal;
use Modules\Menu\Actions\Dashboard\V1\CreateMenuAction;
use Modules\Menu\Actions\Dashboard\V1\DeleteMenuAction;
use Modules\Menu\Actions\Dashboard\V1\UpdateMenuAction;
use Modules\Menu\Http\Requests\Dashboard\V1\StoreMenuRequest;
use Modules\Menu\Http\Requests\Dashboard\V1\UpdateMenuRequest;
use Modules\Menu\Http\Resources\Dashboard\V1\MenuResource;
use Modules\Menu\Http\Resources\Dashboard\V1\CategoryResource;
use Modules\Menu\Models\Category;
use Modules\Menu\Models\Menu;
use Modules\Menu\Models\MenuType;
use Modules\Menu\Services\MenuService;
use Modules\Outlet\Models\Outlet;

class MenuController extends Controller
{
    public function __construct(
        private MenuService $menuService
    ) {}

    /**
     * Display a listing of menus.
     */
    public function index(Request $request): Response
    {
        $perPage = $request->input('per_page', 10);
        $filters = $request->only(['search', 'status']);

        $menus = $this->menuService->paginate($perPage, $filters);
        $stats = $this->menuService->getStats();

        return Inertia::render('menu::dashboard/Menu/Index', [
            'menuItems' => MenuResource::collection($menus)->response()->getData(true),
            'filters' => $filters,
            'stats' => $stats,
        ]);
    }

    /**
     * Show form for creating a new menu.
     */
    public function create(): Modal
    {
        $outlets = Outlet::where('status', 'active')
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        $menuTypes = MenuType::where('status', true)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return Inertia::modal('menu::dashboard/Menu/Create', [
            'outlets' => $outlets,
            'menuTypes' => $menuTypes,
        ])->baseRoute('menu.menus.index');
    }

    /**
     * Store a new menu.
     */
    public function store(StoreMenuRequest $request, CreateMenuAction $action): RedirectResponse
    {
        $action->execute($request->validated());

        return redirect()
            ->route('menu.menus.index')
            ->with('success', 'Menu created successfully.');
    }

    /**
     * Display a specific menu.
     */
    public function show(Menu $menu): Response
    {
        return Inertia::render('menu::dashboard/Menu/Show', [
            'menu' => (new MenuResource($menu))->resolve(),
        ]);
    }

    /**
     * Show form for editing a menu.
     */
    public function edit(Menu $menu): Modal
    {
        $outlets = Outlet::where('status', 'active')
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        $menuTypes = MenuType::where('status', true)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return Inertia::modal('menu::dashboard/Menu/Edit', [
            'menu' => (new MenuResource($menu))->resolve(),
            'outlets' => $outlets,
            'menuTypes' => $menuTypes,
        ])->baseRoute('menu.menus.index');
    }

    /**
     * Update a menu.
     */
    public function update(UpdateMenuRequest $request, Menu $menu, UpdateMenuAction $action): RedirectResponse
    {
        $action->execute($menu, $request->validated());

        return redirect()
            ->route('menu.menus.index')
            ->with('success', 'Menu updated successfully.');
    }

    /**
     * Show delete confirmation modal.
     */
    public function confirmDelete(Menu $menu): Modal
    {
        return Inertia::modal('menu::dashboard/Menu/Delete', [
            'menu' => (new MenuResource($menu))->resolve(),
        ])->baseRoute('menu.menus.index');
    }

    /**
     * Delete a menu.
     */
    public function destroy(Menu $menu, DeleteMenuAction $action): RedirectResponse
    {
        $action->execute($menu);

        return redirect()
            ->route('menu.menus.index')
            ->with('success', 'Menu deleted successfully.');
    }

    /**
     * Toggle menu status.
     */
    public function toggleStatus(Request $request, Menu $menu): RedirectResponse
    {
        $menu->update([
            'status' => $request->boolean('status'),
        ]);

        return redirect()->back();
    }

    /**
     * Display categories for a specific menu.
     */
    public function categories(Request $request, Menu $menu): Response
    {
        $filters = $request->only(['search', 'status']);

        $query = Category::where('menu_id', $menu->id)
            ->with(['products' => function ($q) {
                $q->orderBy('menu_category_products.sort_order');
            }])
            ->withCount('products')
            ->orderBy('sort_order');

        if (!empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status'] === '1' || $filters['status'] === 'active');
        }

        $categories = $query->get();

        $stats = [
            'total' => Category::where('menu_id', $menu->id)->count(),
            'active' => Category::where('menu_id', $menu->id)->where('status', true)->count(),
            'inactive' => Category::where('menu_id', $menu->id)->where('status', false)->count(),
        ];

        return Inertia::render('menu::dashboard/Menu/ManageCategories', [
            'menu' => (new MenuResource($menu))->resolve(),
            'categories' => CategoryResource::collection($categories)->resolve(),
            'filters' => $filters,
            'stats' => $stats,
        ]);
    }

    /**
     * Show form to assign existing categories to a menu.
     */
    public function assignCategories(Menu $menu): Response
    {
        // Get categories not assigned to this menu
        $availableCategories = Category::whereNull('menu_id')
            ->orWhere('menu_id', '!=', $menu->id)
            ->orderBy('name')
            ->get();

        return Inertia::render('menu::dashboard/Menu/AssignCategories', [
            'menu' => (new MenuResource($menu))->resolve(),
            'availableCategories' => CategoryResource::collection($availableCategories)->resolve(),
        ]);
    }

    /**
     * Store assigned categories to a menu.
     */
    public function storeAssignedCategories(Request $request, Menu $menu): RedirectResponse
    {
        $request->validate([
            'category_ids' => ['required', 'array'],
            'category_ids.*' => ['required', 'integer', 'exists:menu_categories,id'],
        ]);

        $maxSortOrder = Category::where('menu_id', $menu->id)->max('sort_order') ?? 0;

        foreach ($request->category_ids as $index => $categoryId) {
            Category::where('id', $categoryId)->update([
                'menu_id' => $menu->id,
                'sort_order' => $maxSortOrder + $index + 1,
            ]);
        }

        return redirect()
            ->route('menu.menus.categories.manage', $menu)
            ->with('success', 'Categories assigned successfully.');
    }

    /**
     * Reorder categories for a specific menu.
     */
    public function reorderCategories(Request $request, Menu $menu): RedirectResponse
    {
        $request->validate([
            'categories' => ['required', 'array'],
            'categories.*.id' => ['required', 'integer', 'exists:menu_categories,id'],
            'categories.*.sort_order' => ['required', 'integer', 'min:0'],
        ]);

        foreach ($request->categories as $item) {
            Category::where('id', $item['id'])
                ->where('menu_id', $menu->id)
                ->update(['sort_order' => $item['sort_order']]);
        }

        return redirect()->back()->with('success', 'Categories reordered successfully.');
    }
}
