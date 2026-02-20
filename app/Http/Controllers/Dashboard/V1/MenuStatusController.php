<?php

namespace Modules\Menu\Http\Controllers\Dashboard\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\Menu\Actions\Dashboard\V1\ToggleMenuStatusAction;
use Modules\Menu\Models\Menu;

class MenuStatusController extends Controller
{
    public function __construct(
        protected ToggleMenuStatusAction $toggleMenuStatusAction,
    ) {}

    /**
     * Toggle menu status.
     */
    public function __invoke(Request $request, Menu $menu): RedirectResponse
    {
        $this->toggleMenuStatusAction->execute($menu, $request->boolean('status'));

        return redirect()->back();
    }
}
