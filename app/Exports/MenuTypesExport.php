<?php

namespace Modules\Menu\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Modules\Menu\Models\MenuType;

class MenuTypesExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = MenuType::query()
            ->with('outlet')
            ->withCount('menus');

        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if (isset($this->filters['status']) && $this->filters['status'] !== '') {
            $status = filter_var($this->filters['status'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($status !== null) {
                $query->where('status', $status);
            }
        }

        return $query->orderBy('name');
    }

    public function headings(): array
    {
        return [
            'ID',
            'UUID',
            'Name',
            'Description',
            'Outlet',
            'Menus Count',
            'Sort Order',
            'Status',
            'Created At',
        ];
    }

    public function map($menuType): array
    {
        return [
            $menuType->id,
            $menuType->uuid,
            $menuType->name,
            strip_tags($menuType->description ?? ''),
            $menuType->outlet?->name ?? '-',
            $menuType->menus_count ?? 0,
            $menuType->sort_order,
            $menuType->status ? 'Active' : 'Inactive',
            $menuType->created_at?->format('Y-m-d H:i:s'),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4F46E5'],
                ],
            ],
        ];
    }
}
