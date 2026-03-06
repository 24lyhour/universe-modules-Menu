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
use Modules\Menu\Actions\Dashboard\V1\CreateMenuAction;
use Modules\Menu\Actions\Dashboard\V1\DeleteMenuAction;
use Modules\Menu\Actions\Dashboard\V1\GetMenuShowDataAction;
use Modules\Menu\Actions\Dashboard\V1\UpdateMenuAction;
use Modules\Menu\Exports\MenusExport;
use Modules\Menu\Imports\MenusImport;
use Modules\Menu\Http\Requests\Dashboard\V1\StoreMenuRequest;
use Modules\Menu\Http\Requests\Dashboard\V1\UpdateMenuRequest;
use Modules\Menu\Http\Resources\Dashboard\V1\MenuResource;
use Modules\Menu\Models\Menu;
use Modules\Menu\Models\MenuType;
use Modules\Menu\Services\MenuService;
use Modules\Outlet\Models\Outlet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MenuController extends Controller
{
    protected array $duplicateOptions = [
        ['value' => 'skip', 'label' => 'Skip duplicate rows'],
        ['value' => 'update', 'label' => 'Update existing records'],
        ['value' => 'fail', 'label' => 'Fail on duplicates'],
    ];

    public function __construct(
        private MenuService $menuService
    ) {
        // Authorization is handled by 'auto.permission' middleware in routes
    }

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
     * Display trashed menus.
     */
    public function trash(Request $request): Response
    {
        $perPage = $request->integer('per_page', 10);
        $search = $request->string('search')->toString();

        $menus = $this->menuService->getTrashed($perPage, $search ?: null);

        return Inertia::render('menu::dashboard/Menu/Trash', [
            'menuItems' => MenuResource::collection($menus)->response()->getData(true),
            'filters' => $request->only(['search', 'per_page']),
            'stats' => $this->menuService->getStats(),
        ]);
    }

    /**
     * Export menus to Excel.
     */
    public function export(Request $request): BinaryFileResponse
    {
        $filters = $request->only(['search', 'status']);

        return Excel::download(
            new MenusExport($filters),
            'menus-' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    /**
     * Show import page.
     */
    public function import(): Response
    {
        return Inertia::render('menu::dashboard/Menu/Import', [
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

        $import = new MenusImport(
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

        $import = new MenusImport(
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

        $this->menuService->clearStatsCache();

        return redirect()->route('menu.menus.index')
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
        $headers = ['name', 'description', 'status'];
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
        $sheet->setCellValue('A2', 'Sample Menu');
        $sheet->setCellValue('B2', 'Sample description');
        $sheet->setCellValue('C2', 'true');

        $filename = 'menus-import-template.xlsx';
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
     * Restore a trashed menu.
     */
    public function restore(string $uuid): RedirectResponse
    {
        $menu = Menu::onlyTrashed()->where('uuid', $uuid)->firstOrFail();
        $this->menuService->restore($menu);

        return redirect()->back()
            ->with('success', 'Menu restored successfully.');
    }

    /**
     * Force delete a trashed menu.
     */
    public function forceDelete(string $uuid): RedirectResponse
    {
        $menu = Menu::onlyTrashed()->where('uuid', $uuid)->firstOrFail();
        $this->menuService->forceDelete($menu);

        return redirect()->back()
            ->with('success', 'Menu permanently deleted.');
    }

    /**
     * Bulk restore menus.
     */
    public function bulkRestore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'uuids' => ['required', 'array', 'min:1'],
            'uuids.*' => ['required', 'string', 'uuid'],
        ]);

        $count = $this->menuService->bulkRestore($validated['uuids']);

        return redirect()->route('menu.menus.trash')
            ->with('success', "{$count} menu(s) restored.");
    }

    /**
     * Bulk force delete menus.
     */
    public function bulkForceDelete(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'uuids' => ['required', 'array', 'min:1'],
            'uuids.*' => ['required', 'string', 'uuid'],
        ]);

        $count = $this->menuService->bulkForceDelete($validated['uuids']);

        return redirect()->route('menu.menus.trash')
            ->with('success', "{$count} menu(s) permanently deleted.");
    }

    /**
     * Empty trash.
     */
    public function emptyTrash(): RedirectResponse
    {
        $count = $this->menuService->emptyTrash();

        return redirect()->route('menu.menus.trash')
            ->with('success', "{$count} menu(s) permanently deleted.");
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
    public function show(Menu $menu, GetMenuShowDataAction $action): Response
    {
        $data = $action->execute($menu);

        return Inertia::render('menu::dashboard/Menu/Show', $data);
    }

    /**
     * Show form for editing a menu.
     */
    public function edit(Menu $menu): Modal
    {
        $menu->load(['outlet', 'menuType']);

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
        $menu->load(['outlet', 'menuType']);

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
            ->with('success', 'Menu moved to trash.');
    }

    /**
     * Show bulk delete confirmation modal.
     */
    public function confirmBulkDelete(Request $request): Modal
    {
        $uuids = $request->input('uuids', []);

        $menuItemsData = Menu::whereIn('uuid', $uuids)
            ->withCount('categories')
            ->get(['id', 'uuid', 'name', 'status']);

        return Inertia::modal('menu::dashboard/Menu/BulkDelete', [
            'menuItemsData' => $menuItemsData->map(fn ($m) => [
                'id' => $m->id,
                'uuid' => $m->uuid,
                'name' => $m->name,
                'status' => $m->status,
                'categories_count' => $m->categories_count,
            ])->toArray(),
        ])->baseRoute('menu.menus.index');
    }

    /**
     * Bulk delete menus.
     */
    public function bulkDelete(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'uuids' => ['required', 'array', 'min:1'],
            'uuids.*' => ['required', 'string', 'uuid'],
        ]);

        $count = $this->menuService->bulkDelete($validated['uuids']);

        return redirect()->route('menu.menus.index')
            ->with('success', "{$count} menu(s) moved to trash.");
    }
}
