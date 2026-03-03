<?php

namespace Modules\Menu\Http\Controllers\Dashboard\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Modules\Menu\Actions\Dashboard\V1\UpdateMenuScheduleAction;
use Modules\Menu\Http\Requests\Dashboard\V1\UpdateMenuScheduleRequest;
use Modules\Menu\Models\Menu;

class MenuScheduleController extends Controller
{
    /**
     * Update menu schedule.
     */
    public function __invoke(
        UpdateMenuScheduleRequest $request,
        Menu $menu,
        UpdateMenuScheduleAction $action
    ): RedirectResponse {
        $action->execute($menu, $request->validated());

        return redirect()
            ->route('menu.menus.index')
            ->with('success', 'Schedule updated successfully.');
    }
}
