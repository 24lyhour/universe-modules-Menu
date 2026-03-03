<?php

namespace Modules\Menu\Http\Controllers\Dashboard\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Modules\Menu\Actions\Dashboard\V1\UpdateMenuScheduleAction;
use Modules\Menu\Http\Requests\Dashboard\V1\UpdateMenuScheduleRequest;
use Modules\Menu\Http\Resources\Dashboard\V1\MenuResource;
use Modules\Menu\Models\Menu;
use Momentum\Modal\Modal;

class MenuScheduleController extends Controller
{
    /**
     * Show schedule modal.
     */
    public function show(Menu $menu): Modal
    {
        return Inertia::modal('menu::dashboard/Menu/Schedule', [
            'menu' => (new MenuResource($menu))->resolve(),
        ])->baseRoute('menu.menus.index');
    }

    /**
     * Update menu schedule.
     */
    public function update(
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
