<?php

namespace Modules\Menu\Http\Controllers\Dashboard\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Momentum\Modal\Modal;
use Modules\Menu\Actions\Dashboard\V1\CreateCategoryAction;
use Modules\Menu\Actions\Dashboard\V1\DeleteCategoryAction;
use Modules\Menu\Actions\Dashboard\V1\GetCategoryCreateDataAction;
use Modules\Menu\Actions\Dashboard\V1\GetCategoryEditDataAction;
use Modules\Menu\Actions\Dashboard\V1\GetCategoryIndexDataAction;
use Modules\Menu\Actions\Dashboard\V1\GetCategoryShowDataAction;
use Modules\Menu\Actions\Dashboard\V1\UpdateCategoryAction;
use Modules\Menu\Http\Requests\Dashboard\V1\StoreCategoryRequest;
use Modules\Menu\Http\Requests\Dashboard\V1\UpdateCategoryRequest;
use Modules\Menu\Http\Resources\Dashboard\V1\CategoryResource;
use Modules\Menu\Models\Category;

class CategoryController extends Controller
{
    public function __construct(
        protected GetCategoryIndexDataAction $getCategoryIndexDataAction,
        protected GetCategoryShowDataAction $getCategoryShowDataAction,
        protected GetCategoryCreateDataAction $getCategoryCreateDataAction,
        protected GetCategoryEditDataAction $getCategoryEditDataAction,
        protected CreateCategoryAction $createCategoryAction,
        protected UpdateCategoryAction $updateCategoryAction,
        protected DeleteCategoryAction $deleteCategoryAction,
    ) {}

    /**
     * Display a listing of categories.
     */
    public function index(Request $request): Response
    {
        $perPage = $request->input('per_page', 10);
        $filters = $request->only(['search', 'status']);

        $data = $this->getCategoryIndexDataAction->execute($perPage, $filters);

        return Inertia::render('menu::dashboard/Category/Index', $data);
    }

    /**
     * Show form for creating a new category.
     */
    public function create(Request $request): Modal
    {
        $selectedMenuId = $request->input('menu_id') ? (int) $request->input('menu_id') : null;
        $data = $this->getCategoryCreateDataAction->execute($selectedMenuId);

        $baseRoute = $selectedMenuId
            ? 'menu.menus.categories.manage'
            : 'menu.categories.index';

        $baseRouteParams = $selectedMenuId ? ['menu' => $selectedMenuId] : [];

        return Inertia::modal('menu::dashboard/Category/Create', $data)
            ->baseRoute($baseRoute, $baseRouteParams);
    }

    /**
     * Store a new category.
     */
    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $this->createCategoryAction->execute($request->validated());

        $menuId = $request->input('menu_id');
        if ($menuId) {
            return redirect()
                ->route('menu.menus.categories.manage', ['menu' => $menuId])
                ->with('success', 'Category created successfully.');
        }

        return redirect()
            ->route('menu.categories.index')
            ->with('success', 'Category created successfully.');
    }

    /**
     * Display a specific category.
     */
    public function show(Category $category): Response
    {
        $data = $this->getCategoryShowDataAction->execute($category);

        return Inertia::render('menu::dashboard/Category/Show', $data);
    }

    /**
     * Show form for editing a category.
     */
    public function edit(Category $category): Modal
    {
        $data = $this->getCategoryEditDataAction->execute($category);

        return Inertia::modal('menu::dashboard/Category/Edit', $data)
            ->baseRoute('menu.categories.index');
    }

    /**
     * Update a category.
     */
    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $this->updateCategoryAction->execute($category, $request->validated());

        return redirect()
            ->route('menu.categories.index')
            ->with('success', 'Category updated successfully.');
    }

    /**
     * Show delete confirmation modal.
     */
    public function confirmDelete(Category $category): Modal
    {
        return Inertia::modal('menu::dashboard/Category/Delete', [
            'category' => new CategoryResource($category),
        ])->baseRoute('menu.categories.index');
    }

    /**
     * Delete a category.
     */
    public function destroy(Category $category): RedirectResponse
    {
        $this->deleteCategoryAction->execute($category);

        return redirect()
            ->route('menu.categories.index')
            ->with('success', 'Category deleted successfully.');
    }
}
