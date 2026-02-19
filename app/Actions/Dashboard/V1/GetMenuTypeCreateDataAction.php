<?php

namespace Modules\Menu\Actions\Dashboard\V1;

use Modules\Outlet\Models\Outlet;

class GetMenuTypeCreateDataAction
{
    /**
     * Get data for menu type create form.
     */
    public function execute(): array
    {
        $outlets = Outlet::where('status', 'active')
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return [
            'outlets' => $outlets,
        ];
    }
}
