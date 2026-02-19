<?php

namespace Modules\Menu\Http\Controllers\Dashboard\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Momentum\Modal\Modal;
use Modules\Menu\Actions\Dashboard\V1\CreateMenuTypeAction;
use Modules\Menu\Actions\Dashboard\V1\DeleteMenuTypeAction;
use Modules\Menu\Actions\Dashboard\V1\GetMenuTypeCreateDataAction;
use Modules\Menu\Actions\Dashboard\V1\GetMenuTypeEditDataAction;
use Modules\Menu\Actions\Dashboard\V1\GetMenuTypeIndexDataAction;
use Modules\Menu\Actions\Dashboard\V1\UpdateMenuTypeAction;
use Modules\Menu\Http\Requests\Dashboard\V1\StoreMenuTypeRequest;
use Modules\Menu\Http\Requests\Dashboard\V1\UpdateMenuTypeRequest;
use Modules\Menu\Http\Resources\Dashboard\V1\MenuTypeResource;
use Modules\Menu\Models\MenuType;

class MenuTypeController extends Controller
{
    public function __construct(
        protected GetMenuTypeIndexDataAction $getMenuTypeIndexDataAction,
        protected GetMenuTypeCreateDataAction $getMenuTypeCreateDataAction,
        protected GetMenuTypeEditDataAction $getMenuTypeEditDataAction,
        protected CreateMenuTypeAction $createMenuTypeAction,
        protected UpdateMenuTypeAction $updateMenuTypeAction,
        protected DeleteMenuTypeAction $deleteMenuTypeAction,
    ) {}

    /**
     * Display a listing of menu types.
     */
    public function index(Request $request): Response
    {
        $perPage = $request->input('per_page', 10);
        $filters = $request->only(['search', 'status']);

        $data = $this->getMenuTypeIndexDataAction->execute($perPage, $filters);

        return Inertia::render('menu::dashboard/TypeMenu/Index', $data);
    }

    /**
     * Show form for creating a new menu type.
     */
    public function create(): Modal
    {
        $data = $this->getMenuTypeCreateDataAction->execute();

        return Inertia::modal('menu::dashboard/TypeMenu/Create', $data)
            ->baseRoute('menu.menu-types.index');
    }

    /**
     * Store a new menu type.
     */
    public function store(StoreMenuTypeRequest $request): RedirectResponse
    {
        $this->createMenuTypeAction->execute($request->validated());

        return redirect()
            ->route('menu.menu-types.index')
            ->with('success', 'Menu type created successfully.');
    }

    /**
     * Display a specific menu type.
     */
    public function show(MenuType $menuType): Response
    {
        return Inertia::render('menu::dashboard/TypeMenu/Show', [
            'menuType' => (new MenuTypeResource($menuType))->resolve(),
        ]);
    }

    /**
     * Show form for editing a menu type.
     */
    public function edit(MenuType $menuType): Modal
    {
        $data = $this->getMenuTypeEditDataAction->execute($menuType);

        return Inertia::modal('menu::dashboard/TypeMenu/Edit', $data)
            ->baseRoute('menu.menu-types.index');
    }

    /**
     * Update a menu type.
     */
    public function update(UpdateMenuTypeRequest $request, MenuType $menuType): RedirectResponse
    {
        $this->updateMenuTypeAction->execute($menuType, $request->validated());

        return redirect()
            ->route('menu.menu-types.index')
            ->with('success', 'Menu type updated successfully.');
    }

    /**
     * Show delete confirmation modal.
     */
    public function confirmDelete(MenuType $menuType): Modal
    {
        return Inertia::modal('menu::dashboard/TypeMenu/Delete', [
            'menuType' => new MenuTypeResource($menuType),
        ])->baseRoute('menu.menu-types.index');
    }

    /**
     * Delete a menu type.
     */
    public function destroy(MenuType $menuType): RedirectResponse
    {
        $this->deleteMenuTypeAction->execute($menuType);

        return redirect()
            ->route('menu.menu-types.index')
            ->with('success', 'Menu type deleted successfully.');
    }
}
