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
use Modules\Menu\Actions\Dashboard\V1\CreateMenuTypeAction;
use Modules\Menu\Actions\Dashboard\V1\DeleteMenuTypeAction;
use Modules\Menu\Actions\Dashboard\V1\GetMenuTypeCreateDataAction;
use Modules\Menu\Actions\Dashboard\V1\GetMenuTypeEditDataAction;
use Modules\Menu\Actions\Dashboard\V1\GetMenuTypeIndexDataAction;
use Modules\Menu\Actions\Dashboard\V1\UpdateMenuTypeAction;
use Modules\Menu\Exports\MenuTypesExport;
use Modules\Menu\Imports\MenuTypesImport;
use Modules\Menu\Http\Requests\Dashboard\V1\StoreMenuTypeRequest;
use Modules\Menu\Http\Requests\Dashboard\V1\UpdateMenuTypeRequest;
use Modules\Menu\Http\Resources\Dashboard\V1\MenuTypeResource;
use Modules\Menu\Models\MenuType;
use Modules\Menu\Services\MenuTypeService;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MenuTypeController extends Controller
{
    protected array $duplicateOptions = [
        ['value' => 'skip', 'label' => 'Skip duplicate rows'],
        ['value' => 'update', 'label' => 'Update existing records'],
        ['value' => 'fail', 'label' => 'Fail on duplicates'],
    ];

    public function __construct(
        protected GetMenuTypeIndexDataAction $getMenuTypeIndexDataAction,
        protected GetMenuTypeCreateDataAction $getMenuTypeCreateDataAction,
        protected GetMenuTypeEditDataAction $getMenuTypeEditDataAction,
        protected CreateMenuTypeAction $createMenuTypeAction,
        protected UpdateMenuTypeAction $updateMenuTypeAction,
        protected DeleteMenuTypeAction $deleteMenuTypeAction,
        protected MenuTypeService $menuTypeService,
    ) {
        // Authorization is handled by 'auto.permission' middleware in routes
    }

    /**
     * Display a listing of menu types.
     */
    public function index(Request $request): Response
    {
        $perPage = $request->input('per_page', 10);
        $filters = $request->only(['search', 'status']);

        $data = $this->getMenuTypeIndexDataAction->execute($perPage, $filters);
        $data['stats'] = $this->menuTypeService->getStats();

        return Inertia::render('menu::dashboard/TypeMenu/Index', $data);
    }

    /**
     * Display trashed menu types.
     */
    public function trash(Request $request): Response
    {
        $perPage = $request->integer('per_page', 10);
        $search = $request->string('search')->toString();

        $menuTypes = $this->menuTypeService->getTrashed($perPage, $search ?: null);

        return Inertia::render('menu::dashboard/TypeMenu/Trash', [
            'menuTypeItems' => MenuTypeResource::collection($menuTypes)->response()->getData(true),
            'filters' => $request->only(['search', 'per_page']),
            'stats' => $this->menuTypeService->getStats(),
        ]);
    }

    /**
     * Export menu types to Excel.
     */
    public function export(Request $request): BinaryFileResponse
    {
        $filters = $request->only(['search', 'status']);

        return Excel::download(
            new MenuTypesExport($filters),
            'menu-types-' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    /**
     * Show import page.
     */
    public function import(): Response
    {
        return Inertia::render('menu::dashboard/TypeMenu/Import', [
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

        $import = new MenuTypesImport(
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

        $import = new MenuTypesImport(
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

        $this->menuTypeService->clearStatsCache();

        return redirect()->route('menu.menu-types.index')
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
        $sheet->setCellValue('A2', 'Sample Menu Type');
        $sheet->setCellValue('B2', 'Sample description');
        $sheet->setCellValue('C2', '1');
        $sheet->setCellValue('D2', 'true');

        $filename = 'menu-types-import-template.xlsx';
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
     * Restore a trashed menu type.
     */
    public function restore(string $uuid): RedirectResponse
    {
        $menuType = MenuType::onlyTrashed()->where('uuid', $uuid)->firstOrFail();
        $this->menuTypeService->restore($menuType);

        return redirect()->back()
            ->with('success', 'Menu type restored successfully.');
    }

    /**
     * Force delete a trashed menu type.
     */
    public function forceDelete(string $uuid): RedirectResponse
    {
        $menuType = MenuType::onlyTrashed()->where('uuid', $uuid)->firstOrFail();
        $this->menuTypeService->forceDelete($menuType);

        return redirect()->back()
            ->with('success', 'Menu type permanently deleted.');
    }

    /**
     * Bulk restore menu types.
     */
    public function bulkRestore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'uuids' => ['required', 'array', 'min:1'],
            'uuids.*' => ['required', 'string', 'uuid'],
        ]);

        $count = $this->menuTypeService->bulkRestore($validated['uuids']);

        return redirect()->route('menu.menu-types.trash')
            ->with('success', "{$count} menu type(s) restored.");
    }

    /**
     * Bulk force delete menu types.
     */
    public function bulkForceDelete(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'uuids' => ['required', 'array', 'min:1'],
            'uuids.*' => ['required', 'string', 'uuid'],
        ]);

        $count = $this->menuTypeService->bulkForceDelete($validated['uuids']);

        return redirect()->route('menu.menu-types.trash')
            ->with('success', "{$count} menu type(s) permanently deleted.");
    }

    /**
     * Empty trash.
     */
    public function emptyTrash(): RedirectResponse
    {
        $count = $this->menuTypeService->emptyTrash();

        return redirect()->route('menu.menu-types.trash')
            ->with('success', "{$count} menu type(s) permanently deleted.");
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
        $this->menuTypeService->clearStatsCache();

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
        $this->menuTypeService->clearStatsCache();

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
        $this->menuTypeService->clearStatsCache();

        return redirect()
            ->route('menu.menu-types.index')
            ->with('success', 'Menu type moved to trash.');
    }

    /**
     * Show bulk delete confirmation modal.
     */
    public function confirmBulkDelete(Request $request): Modal
    {
        $uuids = $request->input('uuids', []);

        $menuTypeItemsData = MenuType::whereIn('uuid', $uuids)
            ->withCount('menus')
            ->get(['id', 'uuid', 'name', 'status']);

        return Inertia::modal('menu::dashboard/TypeMenu/BulkDelete', [
            'menuTypeItemsData' => $menuTypeItemsData->map(fn ($m) => [
                'id' => $m->id,
                'uuid' => $m->uuid,
                'name' => $m->name,
                'status' => $m->status,
                'menus_count' => $m->menus_count,
            ])->toArray(),
        ])->baseRoute('menu.menu-types.index');
    }

    /**
     * Bulk delete menu types.
     */
    public function bulkDelete(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'uuids' => ['required', 'array', 'min:1'],
            'uuids.*' => ['required', 'string', 'uuid'],
        ]);

        $count = $this->menuTypeService->bulkDelete($validated['uuids']);

        return redirect()->route('menu.menu-types.index')
            ->with('success', "{$count} menu type(s) moved to trash.");
    }
}
