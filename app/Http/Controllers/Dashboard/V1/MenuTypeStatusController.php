<?php

namespace Modules\Menu\Http\Controllers\Dashboard\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\Menu\Actions\Dashboard\V1\ToggleMenuTypeStatusAction;
use Modules\Menu\Models\MenuType;

class MenuTypeStatusController extends Controller
{
    public function __construct(
        protected ToggleMenuTypeStatusAction $toggleMenuTypeStatusAction,
    ) {}

    /**
     * Toggle menu type status.
     */
    public function __invoke(Request $request, MenuType $menuType): RedirectResponse
    {
        $this->toggleMenuTypeStatusAction->execute($menuType, $request->boolean('status'));

        return redirect()->back();
    }
}
