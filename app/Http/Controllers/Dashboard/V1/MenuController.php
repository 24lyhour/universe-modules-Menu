<?php

namespace Modules\Menu\Http\Controllers\Dashboard\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Momentum\Modal\Modal;
use Modules\Menu\Actions\Dashboard\CreateMenuAction;
use Modules\Menu\Actions\Dashboard\DeleteMenuAction;
use Modules\Menu\Actions\Dashboard\UpdateMenuAction;
use Modules\Menu\Http\Requests\Dashboard\V1\StoreMenuRequest;
use Modules\Menu\Http\Requests\Dashboard\V1\UpdateMenuRequest;
use Modules\Menu\Http\Resources\MenuResource;
use Modules\Menu\Models\Menu;
use Modules\Menu\Services\MenuService;

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
        return Inertia::modal('menu::dashboard/Menu/Create')
            ->baseRoute('menu.menus.index');
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
        return Inertia::modal('menu::dashboard/Menu/Edit', [
            'menu' => (new MenuResource($menu))->resolve(),
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
}
