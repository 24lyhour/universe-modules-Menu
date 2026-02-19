<?php

namespace Modules\Menu\Http\Controllers\Dashboard\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Momentum\Modal\Modal;
use Modules\Menu\Actions\Dashboard\V1\CreateMenuTypeAction;
use Modules\Menu\Actions\Dashboard\V1\UpdateMenuTypeAction;
use Modules\Menu\Actions\Dashboard\V1\DeleteMenuTypeAction;
use Modules\Menu\Http\Requests\Dashboard\V1\StoreMenuTypeRequest;
use Modules\Menu\Http\Requests\Dashboard\V1\UpdateMenuTypeRequest;
use Modules\Menu\Http\Resources\Dashboard\V1\MenuTypeResource;
use Modules\Menu\Models\MenuType;
use Modules\Outlet\Models\Outlet;

class MenuTypeController extends Controller
{
    public function __construct(
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

        return Inertia::render('menu::dashboard/TypeMenu/Index', [
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
        ]);
    }

    /**
     * Show form for creating a new menu type.
     */
    public function create(): Modal
    {
        $outlets = Outlet::where('status', 'active')
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return Inertia::modal('menu::dashboard/TypeMenu/Create', [
            'outlets' => $outlets,
        ])->baseRoute('menu.menu-types.index');
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
        $outlets = Outlet::where('status', 'active')
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return Inertia::modal('menu::dashboard/TypeMenu/Edit', [
            'menuType' => new MenuTypeResource($menuType),
            'outlets' => $outlets,
        ])->baseRoute('menu.menu-types.index');
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

    /**
     * Toggle menu type status.
     */
    public function toggleStatus(Request $request, MenuType $menuType): RedirectResponse
    {
        $menuType->update([
            'status' => $request->boolean('status'),
        ]);

        return redirect()->back();
    }
}
