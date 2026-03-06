<?php

namespace Modules\Menu\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Menu\Models\Category;
use Modules\Product\Http\Resources\ProductResource;

/**
 * Public API controller for categories (no authentication required)
 * Used by mobile app to browse categories and their products
 */
class CategoryPublicController extends Controller
{
    /**
     * Display a listing of active categories.
     *
     * @queryParam menu_id int Filter by menu
     * @queryParam product_type string Filter by product type
     */
    public function index(Request $request): JsonResponse
    {
        $categories = Category::where('status', true)
            ->when($request->input('menu_id'), function ($q, $menuId) {
                $q->where('menu_id', $menuId);
            })
            ->when($request->input('product_type'), function ($q, $type) {
                $q->where('product_type', $type);
            })
            ->withCount(['products' => function ($q) {
                $q->where('status', 'active');
            }])
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'data' => $categories->map(fn($category) => [
                'id' => $category->id,
                'uuid' => $category->uuid,
                'name' => $category->name,
                'description' => $category->description,
                'image_url' => $category->image_url,
                'product_type' => $category->product_type,
                'sort_order' => $category->sort_order,
                'products_count' => $category->products_count,
            ]),
        ]);
    }

    /**
     * Display the specified category with its products.
     */
    public function show(int $id, Request $request): JsonResponse
    {
        $category = Category::where('status', true)
            ->findOrFail($id);

        $products = $category->products()
            ->where('status', 'active')
            ->when($request->input('search'), function ($q, $search) {
                $q->where('name', 'like', "%{$search}%");
            })
            ->when($request->input('min_price'), function ($q, $price) {
                $q->where('price', '>=', $price);
            })
            ->when($request->input('max_price'), function ($q, $price) {
                $q->where('price', '<=', $price);
            })
            ->with(['outlet', 'category'])
            ->paginate($request->integer('per_page', 15));

        return response()->json([
            'category' => [
                'id' => $category->id,
                'uuid' => $category->uuid,
                'name' => $category->name,
                'description' => $category->description,
                'image_url' => $category->image_url,
                'product_type' => $category->product_type,
            ],
            'products' => ProductResource::collection($products)->response()->getData(true),
        ]);
    }

    /**
     * Get products by category slug or ID.
     */
    public function products(string $identifier, Request $request): JsonResponse
    {
        // Find category by ID or UUID
        $category = Category::where('status', true)
            ->where(function ($q) use ($identifier) {
                $q->where('id', $identifier)
                    ->orWhere('uuid', $identifier);
            })
            ->firstOrFail();

        $products = $category->products()
            ->where('status', 'active')
            ->when($request->input('outlet_id'), function ($q, $outletId) {
                $q->where('outlet_id', $outletId);
            })
            ->when($request->input('search'), function ($q, $search) {
                $q->where('name', 'like', "%{$search}%");
            })
            ->when($request->boolean('featured'), function ($q) {
                $q->where('is_featured', true);
            })
            ->with(['outlet', 'category'])
            ->orderByPivot('sort_order')
            ->paginate($request->integer('per_page', 15));

        return response()->json(
            ProductResource::collection($products)->response()->getData(true)
        );
    }

    /**
     * Get featured categories for home page.
     */
    public function featured(Request $request): JsonResponse
    {
        $categories = Category::where('status', true)
            ->withCount(['products' => function ($q) {
                $q->where('status', 'active');
            }])
            ->having('products_count', '>', 0)
            ->orderBy('sort_order')
            ->limit($request->integer('limit', 6))
            ->get();

        return response()->json([
            'data' => $categories->map(fn($category) => [
                'id' => $category->id,
                'uuid' => $category->uuid,
                'name' => $category->name,
                'image_url' => $category->image_url,
                'products_count' => $category->products_count,
            ]),
        ]);
    }
}
