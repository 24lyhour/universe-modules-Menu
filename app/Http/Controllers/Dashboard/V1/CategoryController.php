<?php

namespace Modules\Menu\Http\Controllers\Dashboard\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;
use Momentum\Modal\Modal;
use Modules\Menu\Actions\Dashboard\V1\CreateCategoryAction;
use Modules\Menu\Actions\Dashboard\V1\DeleteCategoryAction;
use Modules\Menu\Actions\Dashboard\V1\GetCategoryCreateDataAction;
use Modules\Menu\Actions\Dashboard\V1\GetCategoryEditDataAction;
use Modules\Menu\Actions\Dashboard\V1\GetCategoryIndexDataAction;
use Modules\Menu\Actions\Dashboard\V1\GetCategoryShowDataAction;
use Modules\Menu\Actions\Dashboard\V1\UpdateCategoryAction;
use Modules\Menu\Exports\CategoriesExport;
use Modules\Menu\Imports\CategoriesImport;
use Modules\Menu\Http\Requests\Dashboard\V1\StoreCategoryRequest;
use Modules\Menu\Http\Requests\Dashboard\V1\UpdateCategoryRequest;
use Modules\Menu\Http\Resources\Dashboard\V1\CategoryResource;
use Modules\Menu\Models\Category;
use Modules\Menu\Services\CategoryService;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CategoryController extends Controller
{
    protected array $duplicateOptions = [
        ['value' => 'skip', 'label' => 'Skip duplicate rows'],
        ['value' => 'update', 'label' => 'Update existing records'],
        ['value' => 'fail', 'label' => 'Fail on duplicates'],
    ];

    public function __construct(
        protected GetCategoryIndexDataAction $getCategoryIndexDataAction,
        protected GetCategoryShowDataAction $getCategoryShowDataAction,
        protected GetCategoryCreateDataAction $getCategoryCreateDataAction,
        protected GetCategoryEditDataAction $getCategoryEditDataAction,
        protected CreateCategoryAction $createCategoryAction,
        protected UpdateCategoryAction $updateCategoryAction,
        protected DeleteCategoryAction $deleteCategoryAction,
        protected CategoryService $categoryService,
    ) {
        // Authorization is handled by 'auto.permission' middleware in routes
    }

    /**
     * Display a listing of categories.
     */
    public function index(Request $request): Response
    {
        $perPage = $request->input('per_page', 10);
        $filters = $request->only(['search', 'status']);

        $data = $this->getCategoryIndexDataAction->execute($perPage, $filters);
        $data['stats'] = $this->categoryService->getStats();

        return Inertia::render('menu::dashboard/Category/Index', $data);
    }

    /**
     * Display trashed categories.
     */
    public function trash(Request $request): Response
    {
        $perPage = $request->integer('per_page', 10);
        $search = $request->string('search')->toString();

        $categories = $this->categoryService->getTrashed($perPage, $search ?: null);

        return Inertia::render('menu::dashboard/Category/Trash', [
            'categoryItems' => CategoryResource::collection($categories)->response()->getData(true),
            'filters' => $request->only(['search', 'per_page']),
            'stats' => $this->categoryService->getStats(),
        ]);
    }

    /**
     * Export categories to Excel.
     */
    public function export(Request $request): BinaryFileResponse
    {
        $filters = $request->only(['search', 'status']);

        return Excel::download(
            new CategoriesExport($filters),
            'menu-categories-' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    /**
     * Show import page.
     */
    public function import(): Response
    {
        return Inertia::render('menu::dashboard/Category/Import', [
            'duplicateOptions' => $this->duplicateOptions,
        ]);
    }

    /**
     * Preview import data.
     */
    public function previewImport(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
            'duplicate_handling' => ['required', 'string', 'in:skip,update,fail'],
        ]);

        $import = new CategoriesImport(
            $request->input('duplicate_handling', 'skip'),
            true // preview mode
        );

        Excel::import($import, $request->file('file'));

        return response()->json([
            'preview' => $import->getPreviewData(),
            'stats' => $import->getResults()['preview_stats'],
        ]);
    }

    /**
     * Process import.
     */
    public function processImport(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
            'duplicate_handling' => ['required', 'string', 'in:skip,update,fail'],
        ]);

        $import = new CategoriesImport(
            $request->input('duplicate_handling', 'skip'),
            false // not preview mode
        );

        Excel::import($import, $request->file('file'));

        $results = $import->getResults();

        $message = "Import completed: {$results['imported']} created";
        if ($results['updated'] > 0) {
            $message .= ", {$results['updated']} updated";
        }
        if ($results['skipped'] > 0) {
            $message .= ", {$results['skipped']} skipped";
        }
        if ($results['failed'] > 0) {
            $message .= ", {$results['failed']} failed";
        }

        $this->categoryService->clearStatsCache();

        return redirect()->route('menu.categories.index')
            ->with('success', $message);
    }

    /**
     * Download import template.
     */
    public function template(): BinaryFileResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $headers = ['name', 'description', 'sort_order', 'status'];
        foreach ($headers as $index => $header) {
            $col = Coordinate::stringFromColumnIndex($index + 1);
            $sheet->setCellValue("{$col}1", $header);
            $sheet->getStyle("{$col}1")->getFont()->setBold(true);
            $sheet->getStyle("{$col}1")->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('E2E8F0');
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Add sample data
        $sheet->setCellValue('A2', 'Sample Category');
        $sheet->setCellValue('B2', 'Sample description');
        $sheet->setCellValue('C2', '1');
        $sheet->setCellValue('D2', 'true');

        $filename = 'categories-import-template.xlsx';
        $tempPath = storage_path("app/temp/{$filename}");

        if (!file_exists(dirname($tempPath))) {
            mkdir(dirname($tempPath), 0755, true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        return response()->download($tempPath, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Restore a trashed category.
     */
    public function restore(string $uuid): RedirectResponse
    {
        $category = Category::onlyTrashed()->where('uuid', $uuid)->firstOrFail();
        $this->categoryService->restore($category);

        return redirect()->back()
            ->with('success', 'Category restored successfully.');
    }

    /**
     * Force delete a trashed category.
     */
    public function forceDelete(string $uuid): RedirectResponse
    {
        $category = Category::onlyTrashed()->where('uuid', $uuid)->firstOrFail();
        $this->categoryService->forceDelete($category);

        return redirect()->back()
            ->with('success', 'Category permanently deleted.');
    }

    /**
     * Bulk restore categories.
     */
    public function bulkRestore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'uuids' => ['required', 'array', 'min:1'],
            'uuids.*' => ['required', 'string', 'uuid'],
        ]);

        $count = $this->categoryService->bulkRestore($validated['uuids']);

        return redirect()->route('menu.categories.trash')
            ->with('success', "{$count} category(s) restored.");
    }

    /**
     * Bulk force delete categories.
     */
    public function bulkForceDelete(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'uuids' => ['required', 'array', 'min:1'],
            'uuids.*' => ['required', 'string', 'uuid'],
        ]);

        $count = $this->categoryService->bulkForceDelete($validated['uuids']);

        return redirect()->route('menu.categories.trash')
            ->with('success', "{$count} category(s) permanently deleted.");
    }

    /**
     * Empty trash.
     */
    public function emptyTrash(): RedirectResponse
    {
        $count = $this->categoryService->emptyTrash();

        return redirect()->route('menu.categories.trash')
            ->with('success', "{$count} category(s) permanently deleted.");
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
        $this->categoryService->clearStatsCache();

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
        $this->categoryService->clearStatsCache();

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
        $this->categoryService->clearStatsCache();

        return redirect()
            ->route('menu.categories.index')
            ->with('success', 'Category moved to trash.');
    }

    /**
     * Show bulk delete confirmation modal.
     */
    public function confirmBulkDelete(Request $request): Modal
    {
        $uuids = $request->input('uuids', []);

        $categoryItemsData = Category::whereIn('uuid', $uuids)
            ->withCount('products')
            ->get(['id', 'uuid', 'name', 'status']);

        return Inertia::modal('menu::dashboard/Category/BulkDelete', [
            'categoryItemsData' => $categoryItemsData->map(fn ($c) => [
                'id' => $c->id,
                'uuid' => $c->uuid,
                'name' => $c->name,
                'status' => $c->status,
                'products_count' => $c->products_count,
            ])->toArray(),
        ])->baseRoute('menu.categories.index');
    }

    /**
     * Bulk delete categories.
     */
    public function bulkDelete(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'uuids' => ['required', 'array', 'min:1'],
            'uuids.*' => ['required', 'string', 'uuid'],
        ]);

        $count = $this->categoryService->bulkDelete($validated['uuids']);

        return redirect()->route('menu.categories.index')
            ->with('success', "{$count} category(s) moved to trash.");
    }
}
