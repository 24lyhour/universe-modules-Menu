<?php

namespace Modules\Menu\Http\Controllers\Dashboard\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Momentum\Modal\Modal;
use Modules\Menu\Actions\Dashboard\V1\CreateCategoryAction;
use Modules\Menu\Actions\Dashboard\V1\UpdateCategoryAction;
use Modules\Menu\Actions\Dashboard\V1\DeleteCategoryAction;
use Modules\Menu\Http\Requests\Dashboard\V1\StoreCategoryRequest;
use Modules\Menu\Http\Requests\Dashboard\V1\UpdateCategoryRequest;
use Modules\Menu\Http\Resources\Dashboard\V1\CategoryResource;
use Modules\Menu\Models\Category;
use Modules\Menu\Models\Menu;
use Modules\Product\Models\Product;

class CategoryController extends Controller
{
    public function __construct(
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

        return Inertia::render('menu::dashboard/Category/Index', [
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
        ]);
    }

    /**
     * Show form for creating a new category.
     */
    public function create(Request $request): Modal
    {
        $menus = Menu::where('status', true)->get(['id', 'name']);
        $selectedMenuId = $request->input('menu_id');

        // Determine base route based on where user came from
        $baseRoute = $selectedMenuId
            ? 'menu.menus.categories.manage'
            : 'menu.categories.index';

        $baseRouteParams = $selectedMenuId ? ['menu' => $selectedMenuId] : [];

        return Inertia::modal('menu::dashboard/Category/Create', [
            'menus' => $menus,
            'selectedMenuId' => $selectedMenuId ? (int) $selectedMenuId : null,
        ])->baseRoute($baseRoute, $baseRouteParams);
    }

    /**
     * Store a new category.
     */
    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $this->createCategoryAction->execute($request->validated());

        // Redirect back to ManageCategories if menu_id is provided
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
        $category->load(['products' => function ($query) {
            $query->orderBy('menu_category_products.sort_order');
        }]);

        // Format products with pivot data
        $products = $category->products->map(fn ($product) => [
            'id' => $product->id,
            'name' => $product->name,
            'sku' => $product->sku,
            'price' => $product->price,
            'sale_price' => $product->sale_price,
            'status' => $product->status,
            'image_url' => $product->images[0] ?? null,
            'pivot' => [
                'price_override' => $product->pivot->price_override,
                'sort_order' => $product->pivot->sort_order,
                'is_available' => $product->pivot->is_available,
            ],
        ]);

        return Inertia::render('menu::dashboard/Category/Show', [
            'category' => (new CategoryResource($category))->resolve(),
            'products' => $products,
        ]);
    }

    /**
     * Show form for editing a category.
     */
    public function edit(Category $category): Modal
    {
        $menus = Menu::where('status', true)->get(['id', 'name']);

        return Inertia::modal('menu::dashboard/Category/Edit', [
            'category' => new CategoryResource($category),
            'menus' => $menus,
        ])->baseRoute('menu.categories.index');
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

    /**
     * Toggle category status.
     */
    public function toggleStatus(Request $request, Category $category, \Modules\Menu\Actions\Dashboard\V1\ToggleCategoryStatusAction $toggleCategoryStatusAction): RedirectResponse
    {
        $toggleCategoryStatusAction->execute($category, $request->boolean('status'));

        return redirect()->back();
    }

}
