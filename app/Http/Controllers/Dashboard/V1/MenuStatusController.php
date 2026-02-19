<?php

namespace Modules\Menu\Http\Controllers\Dashboard\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\Menu\Models\Menu;

class MenuStatusController extends Controller
{
    /**
     * Toggle menu status.
     */
    public function __invoke(Request $request, Menu $menu): RedirectResponse
    {
        $menu->update([
            'status' => $request->boolean('status'),
        ]);

        return redirect()->back();
    }
}
