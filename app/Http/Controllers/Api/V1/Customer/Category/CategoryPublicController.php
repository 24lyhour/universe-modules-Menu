<?php

namespace Modules\Menu\Http\Controllers\Api\V1\Customer\Category;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Menu\Models\Category;
use Modules\Product\Http\Resources\ProductResource;

class CategoryPublicController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $categories = Category::where('status', true)
            ->when($request->input('menu_id'), fn($q, $id) => $q->where('menu_id', $id))
            ->when($request->input('product_type'), fn($q, $type) => $q->where('product_type', $type))
            ->withCount(['products' => fn($q) => $q->where('status', 'active')])
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'data' => $categories->map(fn($c) => [
                'id' => $c->id,
                'uuid' => $c->uuid,
                'name' => $c->name,
                'description' => $c->description,
                'image_url' => $c->image_url,
                'product_type' => $c->product_type,
                'sort_order' => $c->sort_order,
                'products_count' => $c->products_count,
            ]),
        ]);
    }

    public function show(int $id, Request $request): JsonResponse
    {
        $category = Category::where('status', true)->findOrFail($id);

        $products = $category->products()
            ->where('status', 'active')
            ->when($request->input('search'), fn($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->when($request->input('min_price'), fn($q, $p) => $q->where('price', '>=', $p))
            ->when($request->input('max_price'), fn($q, $p) => $q->where('price', '<=', $p))
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

    public function products(string $identifier, Request $request): JsonResponse
    {
        $category = Category::where('status', true)
            ->where(fn($q) => $q->where('id', $identifier)->orWhere('uuid', $identifier))
            ->firstOrFail();

        $products = $category->products()
            ->where('status', 'active')
            ->when($request->input('outlet_id'), fn($q, $id) => $q->where('outlet_id', $id))
            ->when($request->input('search'), fn($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->when($request->boolean('featured'), fn($q) => $q->where('is_featured', true))
            ->with(['outlet', 'category'])
            ->orderByPivot('sort_order')
            ->paginate($request->integer('per_page', 15));

        return response()->json(
            ProductResource::collection($products)->response()->getData(true)
        );
    }

    public function featured(Request $request): JsonResponse
    {
        $categories = Category::where('status', true)
            ->withCount(['products' => fn($q) => $q->where('status', 'active')])
            ->having('products_count', '>', 0)
            ->orderBy('sort_order')
            ->limit($request->integer('limit', 6))
            ->get();

        return response()->json([
            'data' => $categories->map(fn($c) => [
                'id' => $c->id,
                'uuid' => $c->uuid,
                'name' => $c->name,
                'image_url' => $c->image_url,
                'products_count' => $c->products_count,
            ]),
        ]);
    }
}
